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

require_once INSTALLDIR.'/lib/profilelist.php';

// 10x8

define('AVATARS_PER_PAGE', 80);

class GalleryAction extends Action
{
    var $profile = null;
    var $user = null;
    var $page = null;
    var $tag = null;

    function prepare($args)
    {
        parent::prepare($args);

        // FIXME very similar code below

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->arg('page') && $this->arg('page') != 1) {
                $args['page'] = $this->arg['page'];
            }
            common_redirect(common_local_url('subscriptions', $args), 301);
            return false;
        }

        $this->user = User::staticGet('nickname', $nickname);

        if (!$this->user) {
            $this->clientError(_('No such user.'), 404);
            return false;
        }

        $this->profile = $this->user->getProfile();

        if (!$this->profile) {
            $this->serverError(_('User has no profile.'));
            return false;
        }

        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        $this->tag = $this->trimmed('tag');

        return true;
    }

    function isReadOnly()
    {
        return true;
    }

    function handle($args)
    {
        parent::handle($args);

        if (common_config('profile', 'enable_dating')) {
            
            //If a user is not logged in then do not show these pages
            $cur = common_current_user();
            
            if (!$cur || $cur->id != $this->user->id) {
                $this->clientError(_('Only logged in users can access this page.'),
                    403);
                return;
            }
        }

		# Post from the tag dropdown; redirect to a GET

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    common_redirect($this->selfUrl(), 307);
            return;
		}

        $this->showPage();
    }

    function showLocalNav()
    {
        $nav = new SubGroupNav($this, $this->user);
        $nav->show();
    }

    function showContent()
    {
        if (common_config('profile', 'enable_dating')) {
            $this->showInterestsDropdown();
        }
        else {
            $this->showTagsDropdown();
        }
    }

    function showTagsDropdown()
    {
        $tag = $this->trimmed('tag');

        $tags = $this->getAllTags();

        $content = array();

        foreach ($tags as $t) {
            $content[$t] = $t;
        }
        if ($tags) {
            $this->elementStart('dl', array('id'=>'filter_tags'));
            $this->element('dt', null, _('Filter tags'));
            $this->elementStart('dd');
            $this->elementStart('ul');
            $this->elementStart('li', array('id' => 'filter_tags_all',
                                             'class' => 'child_1'));
            $this->element('a',
                           array('href' =>
                                 common_local_url($this->trimmed('action'),
                                                  array('nickname' =>
                                                        $this->user->nickname))),
                           _('All'));
            $this->elementEnd('li');
            $this->elementStart('li', array('id'=>'filter_tags_item'));
            $this->elementStart('form', array('name' => 'bytag',
                                               'id' => 'bytag',
                                               'method' => 'post'));
            $this->dropdown('tag', _('Tag'), $content,
                            _('Choose a tag to narrow list'), false, $tag);
            $this->submit('go', _('Go'));
            $this->elementEnd('form');
            $this->elementEnd('li');
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }
    }
    
    function showInterestsDropdown ()
    {
        //TODO frank: implement this for the subscriptions page interest filtering
        return;
    }

    // Get list of tags we tagged other users with

    function getTags($lst, $usr)
    {
        $profile_tag = new Notice_tag();
        $profile_tag->query('SELECT DISTINCT(tag) ' .
                            'FROM profile_tag, subscription ' .
                            'WHERE tagger = ' . $this->profile->id . ' ' .
                            'AND ' . $usr . ' = ' . $this->profile->id . ' ' .
                            'AND ' . $lst . ' = tagged ' .
                            'AND tagger != tagged');
        $tags = array();
        while ($profile_tag->fetch()) {
            $tags[] = $profile_tag->tag;
        }
        $profile_tag->free();
        return $tags;
    }

    function getAllTags()
    {
        return array();
    }
}