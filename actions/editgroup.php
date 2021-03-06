<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Edit an existing group
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Group
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Sarven Capadisli <csarven@controlyourself.ca>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

/**
 * Add a new group
 *
 * This is the form for adding a new group
 *
 * @category Group
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class EditgroupAction extends GroupRestrictedAction
{
    var $msg;
    var $group = null;
    public $privateGroup = false;

    function title()
    {
        return sprintf(_('Edit %s group'), $this->group->nickname);
    }

    /**
     * Prepare to run
     */

    function prepare($args)
    {
        parent::prepare($args);

        if (!common_config('inboxes','enabled')) {
            $this->serverError(_('Inboxes must be enabled for groups to work'));
            return false;
        }

        if (!common_logged_in()) {
            $this->clientError(_('You must be logged in to create a group.'));
            return false;
        }

        $nickname_arg = $this->trimmed('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            common_redirect(common_local_url('editgroup', $args), 301);
            return false;
        }

        if (!$nickname) {
            $this->clientError(_('No nickname'), 404);
            return false;
        }

        $groupid = $this->trimmed('groupid');
        if ($groupid) {
            $this->group = User_group::staticGet('id', $groupid);
        } else {
            $this->group = User_group::staticGet('nickname', $nickname);
            
            //To retrieve the correct private group if necessary:
            if (common_config('profile', 'enable_dating')) {
                
                $usernick = $this->arg('usernick');
    
                if (isset($usernick)) {
                    
                    $cur = common_current_user();
                    if ($usernick != $cur->nickname) {
                        $this->clientError(_('Only the owner of this group can access this page.'), 403);
                        return;
                    }
                    
                    $this->group = new User_group();
                    $this->group->whereAdd('is_private = 1');
                    $this->group->whereAdd("admin_nickname = '$usernick'");
                    $this->group->whereAdd("nickname = '$nickname'");
                    $this->group->find();
                    $this->group->fetch();
                }
                else {
                    $this->group = new User_group();
                    $this->group->whereAdd('is_private = 0');
                    $this->group->whereAdd("nickname = '$nickname'");
                    $this->group->find();
                    $this->group->fetch();
                }
            }
        }

        if (!$this->group) {
            $this->clientError(_('No such group'), 404);
            return false;
        }

        return true;
    }

    /**
     * Handle the request
     *
     * On GET, show the form. On POST, try to save the group.
     *
     * @param array $args unused
     *
     * @return void
     */

    function handle($args)
    {
        parent::handle($args);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->trySave();
        } else {
            //Passing in nickname of user to display users personal (private) groups
            if (isset($args['usernick'])) {
                $this->privateGroup = true;
            }
            $this->showForm();
        }
    }

    function showForm($msg=null)
    {
        $this->msg = $msg;
        $this->showPage();
    }

    function showLocalNav()
    {
        $nav = new GroupNav($this, $this->group);
        $nav->show();
    }

    function showContent()
    {
        $form = new GroupEditForm($this, $this->group);
        $form->show();
    }

    function showPageNotice()
    {
        if ($this->msg) {
            $this->element('p', 'error', $this->msg);
        } else {
            $this->element('p', 'instructions',
                           _('Use this form to edit the group.'));
        }
    }

    function trySave()
    {
        $cur = common_current_user();
        if (!$cur->isAdmin($this->group)) {
            $this->clientError(_('You must be an admin to edit the group'), 403);
            return;
        }

        $nickname    = common_canonical_nickname($this->trimmed('nickname'));
        $fullname    = $this->trimmed('fullname');
        $homepage    = $this->trimmed('homepage');
        $description = $this->trimmed('description');
        $location    = $this->trimmed('location');
        
        //Variables for private forms
        if (common_config('profile', 'enable_dating')) {
            $is_private    = $this->boolean('is_private');
            $this->privateGroup = $is_private;
        }

        if (!Validate::string($nickname, array('min_length' => 1,
                                               'max_length' => 64,
                                               'format' => NICKNAME_FMT))) {
            $this->showForm(_('Nickname must have only lowercase letters '.
                              'and numbers and no spaces.'));
            return;
        } else if ($this->nicknameExists($nickname)) {
            $this->showForm(_('Nickname already in use. Try another one.'));
            return;
        } else if (!User_group::allowedNickname($nickname)) {
            $this->showForm(_('Not a valid nickname.'));
            return;
        } else if (!is_null($homepage) && (strlen($homepage) > 0) &&
                   !Validate::uri($homepage,
                                  array('allowed_schemes' =>
                                        array('http', 'https')))) {
            $this->showForm(_('Homepage is not a valid URL.'));
            return;
        } else if (!is_null($fullname) && strlen($fullname) > 255) {
            $this->showForm(_('Full name is too long (max 255 chars).'));
            return;
        } else if (!is_null($description) && strlen($description) > 140) {
            $this->showForm(_('description is too long (max 140 chars).'));
            return;
        } else if (!is_null($location) && strlen($location) > 255) {
            $this->showForm(_('Location is too long (max 255 chars).'));
            return;
        }

        $orig = clone($this->group);

        $this->group->nickname    = $nickname;
        $this->group->fullname    = $fullname;
        $this->group->homepage    = $homepage;
        $this->group->description = $description;
        $this->group->location    = $location;
        
        //TODO: Should this be in here?
        $this->group->created     = common_sql_now();
        
        //Variables for private forms
        if (common_config('profile', 'enable_dating')) {
            if ($is_private) {
                $group->is_private = $is_private;
                $group->admin_nickname = $cur->nickname;
            }
        }

        $result = $this->group->update($orig);

        if (!$result) {
            common_log_db_error($this->group, 'UPDATE', __FILE__);
            $this->serverError(_('Could not update group.'));
        }

        if ($this->group->nickname != $orig->nickname) {
            common_redirect(common_local_url('editgroup',
                                             array('nickname' => $nickname)),
                            307);
        } else {
            $this->showForm(_('Options saved.'));
        }
    }

    function nicknameExists($nickname)
    {

        //Changed this nickname exists check to use fetch and grab either private groups or public groups depending
        if (common_config('profile', 'enable_dating')) {
            
            $cur = common_current_user();
            $usernick = $cur->nickname;
            $group = new User_group();
            if ($this->privateGroup) {
                $group->whereAdd('is_private = 1');
                $group->whereAdd("admin_nickname = '$usernick'");
                $group->whereAdd("nickname = '$nickname'");
                $group->whereAdd("id != ".$this->group->id);
            }
            else {
                $group = new User_group();
                $group->whereAdd('is_private = 0');
                $group->whereAdd("nickname = '$nickname'");
                $group->whereAdd("id != ".$this->group->id);
            }
            $result = $group->find();
            return ($result !== 0);
        }

        $group = User_group::staticGet('nickname', $nickname);
        return (!is_null($group) &&
                $group != false &&
                $group->id != $this->group->id);
    }
}

