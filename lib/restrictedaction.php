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

class RestrictedAction extends Action
{
    public $cur = null;
    public $user = null;
    protected $auth = 0;
    
    function prepare($args)
    {
        parent::prepare($args);

        if (common_config('profile', 'enable_dating')) {
            
            //Set current user
            $this->cur = common_current_user();
            
            //Set user we are accessing data on
            $nickname = common_canonical_nickname($this->arg('nickname'));
            $this->user = User::staticGet('nickname', $nickname);
            
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
        switch ($this->auth) {
            case 0:
                $this->clientError(_('Only logged in users can access this page.'),403);
                break;
            case 1:
                $this->clientError(_('Only users which are subscribed to or from this user can access this page.'),403);
                break;
            case 2:
                $this->clientError(_('Only the user who owns this page may access it.'),403);
                break;
            case 3:
                break;
        }
        return;
    }
    
    protected function setAuthorisation()
    {
        if (!$this->cur) {
            $this->auth = 0;
            return;
        }
        if ($this->cur 
                && !$this->cur->isSubscribed($this->user)               //you are not subscribed to this user
                && !$this->cur->isSubscriber($this->user)               //this user is not subscribed to you
                && !$this->cur->isPendingSubscription($this->user)) {   //this user is not pending subscription to you
            $this->auth = 1;
            return;
        }
        if ($this->cur 
                && ($this->cur->isSubscribed($this->user)                   //you are subscribed to this user
                    || $this->cur->isSubscriber($this->user)                //or this user is subscribed to you
                    || $this->cur->isPendingSubscription($this->user))) {   //or this user is pending subscription to you
            $this->auth = 2;
        }
        if ($this->cur->id == $this->user->id) {
            $this->auth = 3;
        }
        return;
    }

}