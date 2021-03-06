<?php
/*
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.     If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

require_once(INSTALLDIR.'/lib/facebookaction.php');

class TellCupidInviteAction extends FacebookAction
{

    function handle($args)
    {
        parent::handle($args);
        $this->showForm();
    }

    /**
     * Wrapper for showing a page
     *
     * Stores an error and shows the page
     *
     * @param string $error Error, if any
     *
     * @return void
     */

    function showForm($error=null)
    {
        $this->error = $error;
        $this->showPage();
    }

    /**
     * Show the page content
     *
     * Either shows the registration form or, if registration was successful,
     * instructions for using the site.
     *
     * @return void
     */

    function showContent()
    {
        if ($this->arg('ids')) {
            $this->showSuccessContent();
        } else {
            $this->showFormContent();
        }
    }

    function showSuccessContent()
    {

        $this->element('h2', null, sprintf(_('Thanks for inviting your friends to use %s'), 
            common_config('site', 'name')));
        $this->element('p', null, _('Invitations have been sent to the following users:'));

        $friend_ids = $_POST['ids']; // XXX: Hmm... is this the best way to acces the list?

        $this->elementStart('ul', array('id' => 'facebook-friends'));

        foreach ($friend_ids as $friend) {
            $this->elementStart('li');
            $this->element('fb:profile-pic', array('uid' => $friend));
            $this->element('fb:name', array('uid' => $friend,
                                            'capitalize' => 'true'));
            $this->elementEnd('li');
        }

        $this->elementEnd("ul");

    }

    function showFormContent()
    {

        // Get a list of users who are already using the app for exclusion
        $exclude_ids = $this->facebook->api_client->friends_getAppUsers();
        
        if (!$exclude_ids) {
            $exclude_ids = array();
        }

        $content = _('You have been invited to Tell Cupid') .
            htmlentities('<fb:req-choice url="' . $this->app_uri . '" label="Add"/>');

        $this->elementStart('fb:request-form', array('action' => 'invite.php',
                                                      'method' => 'post',
                                                      'invite' => 'true',
                                                      'type' => common_config('site', 'name'),
                                                      'content' => $content));
        $this->hidden('invite', 'true');
        $actiontext = _('Invite your friends to use Tell Cupid');
        $this->element('fb:multi-friend-selector', array('showborder' => 'false',
                                                               'actiontext' => $actiontext,
                                                               'exclude_ids' => implode(',', $exclude_ids),
                                                               'bypass' => 'cancel',
                                                                'cols' => '4'));

        $this->elementEnd('fb:request-form');
    }
    
    function title() 
    {
        return sprintf(_('Send invitations'));
    }
    
    // Make this into a widget later
    function showLocalNav()
    {
        $this->showLocalCupidNav();
    }   

}
