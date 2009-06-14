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

require_once INSTALLDIR.'/lib/facebookaction.php';


class TellCupidHomeAction extends FacebookAction
{

    var $page = null;
    
    function prepare($argarray)
    {        
        parent::prepare($argarray);
        
        $this->page = $this->trimmed('page');
       
        if (!$this->page) {
            $this->page = 1;
        }
        
        return true;
    }

    function handle($args)
    {
        parent::handle($args);        

        //We don't want to update the facebook status with this app yet
        $this->facebook->api_client->data_setUserPreference(FACEBOOK_PROMPTED_UPDATE_PREF, 'true');

        if ($this->flink) {

            $this->user = $this->flink->getUser();

             if ($this->arg('status_submit') == 'Send') {            
                $this->saveNewNotice();
             }

            // User is authenticated and has already been prompted once for
            // Facebook status update permission? Then show the main page
            // of the app
            $this->showPage();
            
        } else {

            // User hasn't authenticated yet, prompt for creds
            $this->login();
        }

    }
    
    function login()
    {
        
        $this->showStylesheets();

        //Log the user in as cupid...
        $nickname = common_canonical_nickname('cupid');
        $password = common_config('facebook', 'password');

        $msg = null;

        if ($nickname) {

            if (common_check_user($nickname, $password)) {

                $user = User::staticGet('nickname', $nickname);

                if (!$user) {
                    $this->showLoginForm(_("Could not connect to cupid at this time, please try again later."));
                }

                $flink = DB_DataObject::factory('foreign_link');
                $flink->user_id = $user->id;
                $flink->foreign_id = $this->fbuid;
                $flink->service = FACEBOOK_SERVICE;
                $flink->created = common_sql_now();
                $flink->set_flags(true, false, false);

                $flink_id = $flink->insert();

                // XXX: Do some error handling here
                if (!$flink_id) {
                    $this->showLoginForm(_("Could not insert the foreign link."));
                }

                $this->setDefaults();
                
                $this->user = $flink->getUser();
                $this->showPage();
                return;

            } else {
                $msg = _('Incorrect username or password.');
            }
        }

        $this->showLoginForm($msg);
        $this->showFooter();

    }
    
    function showLoginForm($msg = null)
    {
        $this->elementStart('div', array('id' => 'content'));
        if ($msg) {
             $this->element('fb:error', array('message' => $msg));
        }
        $this->elementEnd('div');
    }

    function setDefaults()
    {
        // A default prefix string for notices
        $this->facebook->api_client->data_setUserPreference(FACEBOOK_NOTICE_PREFIX, 'dented: ');
        $this->facebook->api_client->data_setUserPreference(FACEBOOK_PROMPTED_UPDATE_PREF, 'false');
    }
    
    function showNoticeForm()
    {
        $post_action = "$this->app_uri/index.php";
        
        $this->elementStart('div', array('style'=>'width:100%; text-align:center; float:left;'));
        $this->elementStart('div', array('style'=>'width:470px; margin:0 auto; text-align:left;'));
        
        $this->element('img', array('class' => 'logo photo',
                                        'src' => theme_path('images/icons/tell_cupid_logo_2_small.jpg'),
                                        'alt' => 'Tell Cupid in association with Central Flirt'));
        
        $notice_form = new FacebookNoticeForm($this, $post_action, null, $post_action, $this->user);
        $notice_form->show();
        $this->elementEnd('div');
        $this->elementEnd('div');
    }

    function title()
    {
        return _("Public Timeline");
    }

    function showContent()
    {
        $notice = Notice::publicStream(($this->page-1)*NOTICES_PER_PAGE, NOTICES_PER_PAGE + 1);
        //$notice = $this->user->noticesWithFriends(($this->page-1) * NOTICES_PER_PAGE, NOTICES_PER_PAGE + 1);
        
        $nl = new NoticeList($notice, $this);

        $cnt = $nl->show();

        $this->pagination($this->page > 1, $cnt > NOTICES_PER_PAGE,
                          $this->page, 'index.php', array('nickname' => $this->user->nickname));
    }

    function showNoticeList($notice)
    {      
        $nl = new NoticeList($notice, $this);
        return $nl->show();
    }
  
    /**
     * Generate pagination links
     *
     * @param boolean $have_before is there something before?
     * @param boolean $have_after  is there something after?
     * @param integer $page        current page
     * @param string  $action      current action
     * @param array   $args        rest of query arguments
     *
     * @return nothing
     */
    function pagination($have_before, $have_after, $page, $action, $args=null)
    {
                
        // Does a little before-after block for next/prev page
     
        // XXX: Fix so this uses common_local_url() if possible.
     
        if ($have_before || $have_after) {
            $this->elementStart('div', array('class' => 'pagination'));
            $this->elementStart('dl', null);
            $this->element('dt', null, _('Pagination'));
            $this->elementStart('dd', null);
            $this->elementStart('ul', array('class' => 'nav'));
        }
        if ($have_before) {
            $pargs   = array('page' => $page-1);
            $newargs = $args ? array_merge($args, $pargs) : $pargs;
            $this->elementStart('li', array('class' => 'nav_prev'));            
            $this->element('a', array('href' => "$action?page=$newargs[page]", 'rel' => 'prev'),
                           _('After'));
            $this->elementEnd('li');
        }
        if ($have_after) {
            $pargs   = array('page' => $page+1);
            $newargs = $args ? array_merge($args, $pargs) : $pargs;
            $this->elementStart('li', array('class' => 'nav_next'));
            $this->element('a', array('href' => "$action?page=$newargs[page]", 'rel' => 'next'),
                           _('Before'));
            $this->elementEnd('li');
        }
        if ($have_before || $have_after) {
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
            $this->elementEnd('div');
        }
    }
    
    // Make this into a widget later
    function showLocalNav()
    {
        $this->showLocalCupidNav();
    }  

}
