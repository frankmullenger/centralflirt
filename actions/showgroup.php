<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Group main page
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
 * @category  Personal
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

require_once INSTALLDIR.'/lib/noticelist.php';
require_once INSTALLDIR.'/lib/feedlist.php';

/**
 * Group main page
 *
 * @category Personal
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class ShowgroupAction extends Action
{
    /** group we're viewing. */
    var $group = null;
    /** page we're viewing. */
    var $page = null;

    /**
     * Title of the page
     *
     * @return string page title, with page number
     */

    function title()
    {
        if ($this->page == 1) {
            return sprintf(_("%s group"), $this->group->nickname);
        } else {
            return sprintf(_("%s group, page %d"),
                           $this->group->nickname,
                           $this->page);
        }
    }

    /**
     * Prepare the action
     *
     * Reads and validates arguments and instantiates the attributes.
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */

    function prepare($args)
    {
        parent::prepare($args);

        if (!common_config('inboxes','enabled')) {
            $this->serverError(_('Inboxes must be enabled for groups to work'));
            return false;
        }

        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname

        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->page != 1) {
                $args['page'] = $this->page;
            }
            common_redirect(common_local_url('showgroup', $args), 301);
            return false;
        }

        if (!$nickname) {
            $this->clientError(_('No nickname'), 404);
            return false;
        }

        $this->group = User_group::staticGet('nickname', $nickname);

        if (!$this->group) {
            $this->clientError(_('No such group'), 404);
            return false;
        }

        return true;
    }

    /**
     * Handle the request
     *
     * Shows a profile for the group, some controls, and a list of
     * group notices.
     *
     * @return void
     */

    function handle($args)
    {
        $this->showPage();
    }

    /**
     * Show the page content
     *
     * Shows a group profile and a list of group notices
     */

    function showContent()
    {
        $this->showGroupProfile();
        $this->showGroupNotices();
    }

    /**
     * Show the group notices
     *
     * @return void
     */

    function showGroupNotices()
    {
        $notice = $this->group->getNotices(($this->page-1)*NOTICES_PER_PAGE,
                                           NOTICES_PER_PAGE + 1);

        $nl = new NoticeList($notice, $this);
        $cnt = $nl->show();

        $this->pagination($this->page > 1,
                          $cnt > NOTICES_PER_PAGE,
                          $this->page,
                          'showgroup',
                          array('nickname' => $this->group->nickname));
    }

    /**
     * Show the group profile
     *
     * Information about the group
     *
     * @return void
     */

    function showGroupProfile()
    {
        $this->elementStart('div', array('id' => 'group_profile',
                                         'class' => 'vcard author'));

        $this->element('h2', null, _('Group profile'));

        $this->elementStart('dl', 'group_depiction');
        $this->element('dt', null, _('Photo'));
        $this->elementStart('dd');

        $logo = ($this->group->homepage_logo) ?
          $this->group->homepage_logo : User_group::defaultLogo(AVATAR_PROFILE_SIZE);

        $this->element('img', array('src' => $logo,
                                    'class' => 'photo avatar',
                                    'width' => AVATAR_PROFILE_SIZE,
                                    'height' => AVATAR_PROFILE_SIZE,
                                    'alt' => $this->group->nickname));
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        $this->elementStart('dl', 'group_nickname');
        $this->element('dt', null, _('Nickname'));
        $this->elementStart('dd');
        $hasFN = ($this->group->fullname) ? 'nickname url uid' : 'fn nickname url uid';
        $this->element('a', array('href' => $this->group->homeUrl(),
                                  'rel' => 'me', 'class' => $hasFN),
                            $this->group->nickname);
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        if ($this->group->fullname) {
            $this->elementStart('dl', 'group_fn');
            $this->element('dt', null, _('Full name'));
            $this->elementStart('dd');
            $this->element('span', 'fn', $this->group->fullname);
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }

        if ($this->group->location) {
            $this->elementStart('dl', 'group_location');
            $this->element('dt', null, _('Location'));
            $this->element('dd', 'location', $this->group->location);
            $this->elementEnd('dl');
        }

        if ($this->group->homepage) {
            $this->elementStart('dl', 'group_url');
            $this->element('dt', null, _('URL'));
            $this->elementStart('dd');
            $this->element('a', array('href' => $this->group->homepage,
                                      'rel' => 'me', 'class' => 'url'),
                           $this->group->homepage);
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }

        if ($this->group->description) {
            $this->elementStart('dl', 'group_note');
            $this->element('dt', null, _('Note'));
            $this->element('dd', 'note', $this->group->description);
            $this->elementEnd('dl');
        }

        $this->elementEnd('div');

        $this->elementStart('div', array('id' => 'group_actions'));
        $this->element('h2', null, _('Group actions'));
        $this->elementStart('ul');
        $this->elementStart('li', array('id' => 'group_subscribe'));
        $cur = common_current_user();
        if ($cur) {
            if ($cur->isMember($this->group)) {
                $lf = new LeaveForm($this, $this->group);
                $lf->show();
                if ($cur->isAdmin($this->group)) {
                    $edit = common_local_url('editgroup',
                                             array('nickname' => $this->group->nickname));
                    $this->element('a',
                                   array('href' => $edit,
                                         'id' => 'group_admin'),
                                   _('Admin'));
                }
            } else {
                $jf = new JoinForm($this, $this->group);
                $jf->show();
            }
        }

        $this->elementEnd('li');

        $this->elementEnd('ul');
        $this->elementEnd('div');
    }

    /**
     * Show a list of links to feeds this page produces
     *
     * @return void
     */

    function showExportData()
    {
        $fl = new FeedList($this);
        $fl->show(array(0=>array('href'=>common_local_url('grouprss',
                                                          array('nickname' => $this->group->nickname)),
                                 'type' => 'rss',
                                 'version' => 'RSS 1.0',
                                 'item' => 'notices')));
    }

    /**
     * Show a list of links to feeds this page produces
     *
     * @return void
     */

    function showFeeds()
    {
        $url =
          common_local_url('grouprss',
                           array('nickname' => $this->group->nickname));

        $this->element('link', array('rel' => 'alternate',
                                     'href' => $url,
                                     'type' => 'application/rss+xml',
                                     'title' => sprintf(_('Notice feed for %s group'),
                                                        $this->group->nickname)));
    }
}