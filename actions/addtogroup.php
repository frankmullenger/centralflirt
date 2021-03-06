<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * List of group members
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
 * @author    Frank Mullenger <frankmullenger@gmail.com>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once(INSTALLDIR.'/lib/profilelist.php');
require_once INSTALLDIR.'/lib/publicgroupnav.php';

/**
 * Add members to a private group.
 *
 * @category Group
 * @package  Laconica
 * @author   Frank Mullenger <frankmullenger@gmail.com>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://www.centralflirt.com/
 */

class AddtogroupAction extends GroupRestrictedAction
{
    /**
     * What I want to do with this class is list all of the subscribers to current user, then provide a join group button to add them to the group basically?
     */
    
    var $page = null;
    public $privateGroup = false;

    function prepare($args)
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url('groupmembers', $args), 301);
            return false;
        }

        if (!$nickname) {
            $this->clientError(_('No nickname'), 404);
            return false;
        }

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
                $this->clientError(_('No user nickname'), 404);
                return false;
            }
        }
        
        $cur = common_current_user();
        if (!$cur->isAdmin($this->group)) {
            $this->clientError(_('You must be an admin to edit the group'), 403);
            return false;
        }

        if (!$this->group) {
            $this->clientError(_('No such group'), 404);
            return false;
        }

        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            return sprintf(_('%s group members'),
                           $this->group->nickname);
        } else {
            return sprintf(_('%s group members, page %d'),
                           $this->group->nickname,
                           $this->page);
        }
    }

    function handle($args)
    {
        //Passing in nickname of user to display users personal (private) groups
        if (isset($args['usernick'])) {
            $this->privateGroup = true;
        }
        parent::handle($args);
        $this->showPage();
    }

    function showPageNotice()
    {
        $this->element('p', 'instructions',
                       _('A list of your followers not currently in this group.'));
    }

    function showLocalNav()
    {
        $nav = new GroupNav($this, $this->group);
        $nav->show();
    }

    function showContent()
    {
        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;
        
        $cur = common_current_user();
        $members = $this->group->getNonMembers($cur, $offset, $limit);

        if ($members) {
            $member_list = new ProfileList($members, null, $this);
            $cnt = $member_list->show();
        }

        $members->free();

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'groupmembers',
                          array('nickname' => $this->group->nickname));
    }
}