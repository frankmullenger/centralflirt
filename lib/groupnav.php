<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Tabset for a particular group
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
 * @category  Action
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Sarven Capadisli <csarven@controlyourself.ca>
 * @copyright 2008 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/widget.php';

/**
 * Tabset for a group
 *
 * Shows a group of tabs for a particular user group
 *
 * @category Output
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Sarven Capadisli <csarven@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 *
 * @see      HTMLOutputter
 */

class GroupNav extends Widget
{
    var $action = null;
    var $group = null;

    /**
     * Construction
     *
     * @param Action $action current action, used for output
     */

    function __construct($action=null, $group=null)
    {
        parent::__construct($action);
        $this->action = $action;
        $this->group = $group;
    }

    /**
     * Show the menu
     *
     * @return void
     */

    function show()
    {
        if (isset($this->group->is_private) && $this->group->is_private) {
            $this->showPrivateNav();
        }
        else {
            $this->showPublicNav();
        }
    }
    
    private function showPublicNav() {
        
        $action_name = $this->action->trimmed('action');
        $nickname = $this->group->nickname;

        $this->out->elementStart('ul', array('class' => 'nav'));
        $this->out->menuItem(common_local_url('showgroup', array('nickname' =>
                                                                 $nickname)),
                             _('Group'),
                             sprintf(_('%s group'), $nickname),
                             $action_name == 'showgroup',
                             'nav_group_group');
        $this->out->menuItem(common_local_url('groupmembers', array('nickname' =>
                                                                    $nickname)),
                             _('Members'),
                             sprintf(_('%s group members'), $nickname),
                             $action_name == 'groupmembers',
                             'nav_group_members');

        $cur = common_current_user();

        if ($cur && $cur->isAdmin($this->group)) {
            $this->out->menuItem(common_local_url('editgroup', array('nickname' =>
                                                                     $nickname)),
                                 _('Admin'),
                                 sprintf(_('Edit %s group properties'), $nickname),
                                 $action_name == 'editgroup',
                                 'nav_group_admin');
            $this->out->menuItem(common_local_url('grouplogo', array('nickname' =>
                                                                     $nickname)),
                                 _('Logo'),
                                 sprintf(_('Add or edit %s logo'), $nickname),
                                 $action_name == 'grouplogo',
                                 'nav_group_logo');
        }
        $this->out->elementEnd('ul');
    }
    
    private function showPrivateNav() {
        
        $action_name = $this->action->trimmed('action');
        $nickname = $this->group->nickname;
        
        $cur = common_current_user();

        if ($cur && $cur->isAdmin($this->group)) {

            $this->out->elementStart('ul', array('class' => 'nav'));
            $this->out->menuItem(common_local_url('showgroup', array('nickname' => $nickname, 'usernick' => $cur->nickname)),
                                 _('Group'),
                                 sprintf(_('%s group'), $nickname),
                                 $action_name == 'showgroup',
                                 'nav_group_group');
            $this->out->menuItem(common_local_url('groupmembers', array('nickname' => $nickname, 'usernick' => $cur->nickname)),
                                 _('Members'),
                                 sprintf(_('%s group members'), $nickname),
                                 $action_name == 'groupmembers',
                                 'nav_group_members');
            $this->out->menuItem(common_local_url('addtogroup', array('nickname' => $nickname, 'usernick' => $cur->nickname)),
                                 _('Add Members'),
                                 _('Add group members'),
                                 $action_name == 'addtogroup',
                                 'nav_group_members');
            $this->out->menuItem(common_local_url('editgroup', array('nickname' => $nickname, 'usernick' => $cur->nickname)),
                                 _('Admin'),
                                 sprintf(_('Edit %s group properties'), $nickname),
                                 $action_name == 'editgroup',
                                 'nav_group_admin');
            $this->out->menuItem(common_local_url('grouplogo', array('nickname' => $nickname, 'usernick' => $cur->nickname)),
                                 _('Logo'),
                                 sprintf(_('Add or edit %s logo'), $nickname),
                                 $action_name == 'grouplogo',
                                 'nav_group_logo');
        }
        $this->out->elementEnd('ul');
    }
}
