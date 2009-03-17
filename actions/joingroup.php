<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Join a group
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
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

/**
 * Join a group
 *
 * This is the action for joining a group. It works more or less like the subscribe action
 * for users.
 *
 * @category Group
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class JoingroupAction extends Action
{
    var $group = null;
    public $privateGroup = false;

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
            $this->clientError(_('You must be logged in to join a group.'));
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

        $this->group = User_group::staticGet('nickname', $nickname);
        
        //To retrieve the correct private group if necessary:
        $checkCurrentUser = true;
        if (common_config('profile', 'enable_dating')) {
            
            $usernick = $this->arg('usernick');

            if (isset($usernick)) {
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
            
            if (isset($args['user_to_add'])) {
                $userIdToAdd = $this->trimmed('user_to_add');
                $checkCurrentUser = false;
            }
        }

        if (!$this->group) {
            $this->clientError(_('No such group'), 404);
            return false;
        }

        //Don't check the current user if adding another user
        if ($checkCurrentUser) {
            $cur = common_current_user();
            if ($cur->isMember($this->group)) {
                $this->clientError(_('You are already a member of that group'), 403);
                return false;
            }
        }
        else {
            $userToAdd = User::staticGet('id', $userIdToAdd);
            
            if ($userToAdd->isMember($this->group)) {
                $this->clientError(_('You are already a member of that group'), 403);
                return false;
            }
        }

        return true;
    }

    /**
     * Handle the request
     *
     * On POST, add the current user to the group
     *
     * @param array $args unused
     *
     * @return void
     */

    function handle($args)
    {
        parent::handle($args);
        
        //Passing in nickname of user to display users personal (private) groups
        if (isset($args['usernick'])) {
            $this->privateGroup = true;
        }

        $cur = common_current_user();

        $member = new Group_member();

        $member->group_id   = $this->group->id;
        $member->profile_id = $cur->id;
        $member->created    = common_sql_now();
        
        //If adding a user to a private group
        if (common_config('profile', 'enable_dating') && $this->privateGroup) {
            $userToAdd = $this->trimmed('user_to_add');
            $member->profile_id = $userToAdd;
        }

        $result = $member->insert();

        if (!$result) {
            common_log_db_error($member, 'INSERT', __FILE__);
            $this->serverError(sprintf(_('Could not join user %s to group %s'),
                                       $cur->nickname, $this->group->nickname));
        }

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            $this->element('title', null, sprintf(_('%s joined group %s'),
                                                  $cur->nickname,
                                                  $this->group->nickname));
            $this->elementEnd('head');
            $this->elementStart('body');
            
            $lf = new LeaveForm($this, $this->group);
            if (common_config('profile', 'enable_dating') && $this->privateGroup) {
                $addedUser = User::staticGet('id', $member->profile_id);
                $lf = new LeaveForm($this, $this->group, $addedUser->getProfile());
            }
            
            $lf->show();
            $this->elementEnd('body');
            $this->elementEnd('html');
        } else {
            common_redirect(common_local_url('groupmembers', array('nickname' =>
                                                                   $this->group->nickname)));
        }
    }
}