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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

require_once INSTALLDIR.'/lib/personalgroupnav.php';
require_once INSTALLDIR.'/lib/noticelist.php';
require_once INSTALLDIR.'/lib/feedlist.php';

class AllAction extends RestrictedAction
{
    var $user = null;
    var $page = null;

    function isReadOnly()
    {
        return true;
    }

    function prepare($args)
    {
        parent::prepare($args);
        $nickname = common_canonical_nickname($this->arg('nickname'));
        $this->user = User::staticGet('nickname', $nickname);
        $this->page = $this->trimmed('page');
        if (!$this->page) {
            $this->page = 1;
        }
        
        common_set_returnto($this->selfUrl());
        
        return true;
    }

    function handle($args)
    {
        parent::handle($args);

        if (!$this->user) {
            $this->clientError(_('No such user.'));
            return;
        }

        $this->showPage();
    }
    
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
            case 3:
                break;
        }
        return;
    }

    function title()
    {
        if ($this->page > 1) {
            return sprintf(_("%s and friends, page %d"), $this->user->nickname, $this->page);
        } else {
            return sprintf(_("%s and friends"), $this->user->nickname);
        }
    }

    function showFeeds()
    {
        if (!common_config('profile', 'enable_dating')) {
            $this->element('link', array('rel' => 'alternate',
                                         'href' => common_local_url('allrss', array('nickname' =>
                                                                                    $this->user->nickname)),
                                         'type' => 'application/rss+xml',
                                         'title' => sprintf(_('Feed for friends of %s'), $this->user->nickname)));
        }
    }

    function showLocalNav()
    {
        $nav = new PersonalGroupNav($this);
        $nav->show();
    }

    function showExportData()
    {
        if (!common_config('profile', 'enable_dating')) {
            $fl = new FeedList($this);
            $fl->show(array(0=>array('href'=>common_local_url('allrss', array('nickname' => $this->user->nickname)),
                                     'type' => 'rss',
                                     'version' => 'RSS 1.0',
                                     'item' => 'allrss')));
        }
    }

    function showContent()
    {
        $notice = $this->user->noticesWithFriends(($this->page-1)*NOTICES_PER_PAGE, NOTICES_PER_PAGE + 1);

        $nl = new NoticeList($notice, $this);
        $cnt = $nl->show();

        $this->pagination($this->page > 1, $cnt > NOTICES_PER_PAGE,
                          $this->page, 'all', array('nickname' => $this->user->nickname));
    }

    function showPageTitle()
    {
        $user =& common_current_user();
        if ($user && ($user->id == $this->user->id)) {
            $this->element('h1', NULL, _("You and friends"));
        } else { 
            $this->element('h1', NULL, sprintf(_('%s and friends'), $this->user->nickname));
        }
    }
      
    function showPageNotice()
    {
        $user =& common_current_user();
        if ($user && ($user->id == $this->user->id)) {
            $this->element('p', NULL, _("Notices from yourself and the friends you are subscribed to."));
        } else { 
            
            //Show different messages depending on status of relationship between users
            if (!$user->isSubscribed($this->user)) {
                
                if ($user->isPendingSubscriptionTo($this->user)) {
                    $this->element('p', NULL, sprintf(_('Your subscription to %s is still pending, once @s approves you as a subscriber you will be able to see their stream.'), 
                                                        ucfirst($this->user->nickname), 
                                                        ucfirst($this->user->nickname)));             
                }
                else {
                    $this->element('p', NULL, sprintf(_('Notices from %s cannot be displayed until you subscribe to %s.'), 
                                                        ucfirst($this->user->nickname),
                                                        ucfirst($this->user->nickname)));             
                }
            }
            else {
                $this->element('p', NULL, sprintf(_('Notices from %s since you became a subscriber of %s.'), ucfirst($this->user->nickname), ucfirst($this->user->nickname)));             
            }
        }
    }
    
    function getNoticeClass()
    {
        return $this->user->nickname;
    }

}
