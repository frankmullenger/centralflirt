<?php
/**
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, Controlez-Vous, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
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
 */

if (!defined('LACONICA')) {
    exit(1);
}

define('MEMBERS_PER_SECTION', 81);

class GroupRestrictedAction extends Action
{
    public $cur = null;
    public $user = null;
    public $group = null;
    protected $auth = 0;
    
    function prepare($args)
    {
        parent::prepare($args);

        if (common_config('profile', 'enable_dating')) {
            
            //Set current user
            $this->cur = common_current_user();
            
            //Set user we are accessing data on
            $nickname = common_canonical_nickname($this->arg('usernick'));
            $this->user = User::staticGet('nickname', $nickname);
            
            $nickname_arg = $this->arg('nickname');
            $nickname = common_canonical_nickname($nickname_arg);
            $this->group = User_group::staticGet('nickname', $nickname);
            
            //Set authorisation
            $this->setAuthorisation();

            //Handle authorisation
            $this->handleAuthorisation();
            
        }
    }
    
    /**
     * This should be overwritten in child classes to provide more appropriate error messages.
     */
    public function handleAuthorisation()
    {
        //Checks for dating site, make sure user is passed who is admin for this group
        if (!$this->user) {
            $this->clientError(_('No such user.'), 404);
            return false;
        }
        if (!$this->user->isAdmin($this->group)) {
            $this->clientError(_('User passed is not admin for this group.'), 403);
            return false;
        }
        
        //Also make sure the current user is the administrator of the group
        if (!$this->cur->isAdmin($this->group)) {
            $this->clientError(_('You must be an admin to edit the group.'), 403);
            return false;
        }
    }
    
    protected function setAuthorisation()
    {
        if ($this->cur->id == $this->user->id) {
            $this->auth = 3;
        }
        else {
            $this->auth = 0;
        }
    }
    
    /**
     * Local menu
     *
     * @return void
     */
    function showLocalNav()
    {
        $nav = new GroupNav($this, $this->group);
        $nav->show();
    }
    
    /**
     * Fill in the sidebar.
     *
     * @return void
     */
    function showSections()
    {
        $this->showMembers();
        $this->showStatistics();
//        $cloud = new GroupTagCloudSection($this, $this->group);
//        $cloud->show();
    }

    /**
     * Show mini-list of members
     *
     * @return void
     */
    function showMembers()
    {
        $member = $this->group->getMembers(0, MEMBERS_PER_SECTION);

        if (!$member) {
            return;
        }

        $this->elementStart('div', array('id' => 'entity_members',
                                         'class' => 'section'));

        $this->element('h2', null, _('Members'));

        $pml = new ProfileMiniList($member, null, $this);
        $cnt = $pml->show();
        if ($cnt == 0) {
             $this->element('p', null, _('(None)'));
        }

        if ($cnt == MEMBERS_PER_SECTION) {
            $this->element('a', array('href' => common_local_url('groupmembers',
                                                                 array('nickname' => $this->group->nickname))),
                           _('All members'));
        }

        $this->elementEnd('div');
    }

    /**
     * Show some statistics
     *
     * @return void
     */
    function showStatistics()
    {
        // XXX: WORM cache this
        $members = $this->group->getMembers();
        $members_count = 0;
        /** $member->count() doesn't work. */
        while ($members->fetch()) {
            $members_count++;
        }

        $this->elementStart('div', array('id' => 'entity_statistics',
                                         'class' => 'section'));

        $this->element('h2', null, _('Statistics'));

        $this->elementStart('dl', 'entity_created');
        $this->element('dt', null, _('Created'));
        $this->element('dd', null, date('j M Y',
                                                 strtotime($this->group->created)));
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_members');
        $this->element('dt', null, _('Members'));
        $this->element('dd', null, (is_int($members_count)) ? $members_count : '0');
        $this->elementEnd('dl');

        $this->elementEnd('div');
    }

}