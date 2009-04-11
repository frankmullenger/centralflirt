<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * User profile page
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

require_once INSTALLDIR.'/lib/personalgroupnav.php';
require_once INSTALLDIR.'/lib/noticelist.php';
require_once INSTALLDIR.'/lib/profileminilist.php';
require_once INSTALLDIR.'/lib/groupminilist.php';
require_once INSTALLDIR.'/lib/feedlist.php';

/**
 * User profile page
 *
 * When I created this page, "show stream" seemed like the best name for it.
 * Now, it seems like a really bad name.
 *
 * It shows a stream of the user's posts, plus lots of profile info, links
 * to subscriptions and stuff, etc.
 *
 * @category Personal
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class ShowstreamAction extends RestrictedAction
{
    var $page = null;
    var $profile = null;

    function isReadOnly()
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            return $this->user->nickname;
        } else {
            return sprintf(_("%s, page %d"),
                           $this->user->nickname,
                           $this->page);
        }
    }

    function prepare($args)
    {
        parent::prepare($args);

        $nickname_arg = $this->arg('nickname');
        $nickname = common_canonical_nickname($nickname_arg);

        // Permanent redirect on non-canonical nickname
        if ($nickname_arg != $nickname) {
            $args = array('nickname' => $nickname);
            if ($this->arg('page') && $this->arg('page') != 1) {
                $args['page'] = $this->arg['page'];
            }
            common_redirect(common_local_url('showstream', $args), 301);
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

        common_set_returnto($this->selfUrl());
        
        return true;
    }

    function handle($args)
    {
        //TODO frank: why does this not have a call to parent::handle() I wonder
        
        // Looks like we're good; start output
        // For YADIS discovery, we also have a <meta> tag
        header('X-XRDS-Location: '. common_local_url('xrds', array('nickname' =>
                                                                   $this->user->nickname)));
        $this->showPage();
    }
    
    public function handleAuthorisation() 
    {
        switch ($this->auth) {
            case 0:
            case 1:
            case 2:
            case 3:
                break;
        }
        return;
    }

    function showContent()
    {
        if (common_config('profile', 'enable_dating')) {

            /*
             * If user is logged out show profile
             * If user is logged in but not subscriber show dating profile
             * If user is logged in is subscriber or subscribed to show dating profile and notices
             * If user is logged in and owner of the profile
             */
            $this->showDatingProfileBlurb();
            
            switch ($this->auth) {
                case 0:
                    break;
                case 1:
                    $this->showDatingProfile();
                    break;
                case 2:
                case 3:
                    $this->showDatingProfile();
                    $this->showNotices();
                    break;
            }
        }
        else {
            $this->showProfile();
            $this->showNotices();
        }
    }
    
    function showLocalNav()
    {
        $nav = new PersonalGroupNav($this);
        $nav->show();
    }

    function showPageTitle()
    {
        $user =& common_current_user();
        if ($user && ($user->id == $this->profile->id)) {
            $this->element('h1', NULL, _("Your profile"));
        } else {
            $this->element('h1', NULL, sprintf(_('%s\'s profile'), $this->profile->nickname));
        }
    }

    function showPageNoticeBlock()
    {
        return;
    }

    function showExportData()
    {
        if (!common_config('profile', 'enable_dating')) {

            $fl = new FeedList($this);
            $fl->show(array(0=>array('href'=>common_local_url('userrss',
                                                              array('nickname' => $this->user->nickname)),
                                     'type' => 'rss',
                                     'version' => 'RSS 1.0',
                                     'item' => 'notices'),
                            1=>array('href'=>common_local_url('usertimeline',
                                                              array('nickname' => $this->user->nickname)),
                                     'type' => 'atom',
                                     'version' => 'Atom 1.0',
                                     'item' => 'usertimeline'),
                            2=>array('href'=>common_local_url('foaf',
                                                              array('nickname' => $this->user->nickname)),
                                     'type' => 'rdf',
                                     'version' => 'FOAF',
                                     'item' => 'foaf')));    
        }
    }

    function showFeeds()
    {
        if (common_config('profile', 'enable_dating')) {

            if ($this->cur) {
                $this->element('link', array('rel' => 'alternate',
                                'type' => 'application/rss+xml',
                                'href' => common_local_url('userrss',
                                 array('nickname' => $this->user->nickname)),
                                       'title' => sprintf(_('Notice feed for %s (RSS)'),
                                         $this->user->nickname)));
        
                 $this->element('link',
                     array('rel' => 'alternate',
                           'href' => common_local_url('api',
                             array('apiaction' => 'statuses',
                                   'method' => 'user_timeline.atom',
                                   'argument' => $this->user->nickname)),
                                   'type' => 'application/atom+xml',
                                   'title' => sprintf(_('Notice feed for %s (Atom)'),
                                     $this->user->nickname)));
            }
        }
        else {
            $this->element('link', array('rel' => 'alternate',
                            'type' => 'application/rss+xml',
                            'href' => common_local_url('userrss',
                             array('nickname' => $this->user->nickname)),
                                   'title' => sprintf(_('Notice feed for %s (RSS)'),
                                     $this->user->nickname)));
    
             $this->element('link',
                 array('rel' => 'alternate',
                       'href' => common_local_url('api',
                         array('apiaction' => 'statuses',
                               'method' => 'user_timeline.atom',
                               'argument' => $this->user->nickname)),
                               'type' => 'application/atom+xml',
                               'title' => sprintf(_('Notice feed for %s (Atom)'),
                                 $this->user->nickname)));
        }
    }

    function extraHead()
    {
        //TODO frank: need to look into headers being generated and stop users accessing this info
        
        // FOAF
        $this->element('link', array('rel' => 'meta',
                                     'href' => common_local_url('foaf', array('nickname' =>
                                                                              $this->user->nickname)),
                                     'type' => 'application/rdf+xml',
                                     'title' => 'FOAF'));
        // for remote subscriptions etc.
        $this->element('meta', array('http-equiv' => 'X-XRDS-Location',
                                     'content' => common_local_url('xrds', array('nickname' =>
                                                                               $this->user->nickname))));

        if ($this->profile->bio) {
            $this->element('meta', array('name' => 'description',
                                         'content' => $this->profile->bio));
        }

        if ($this->user->emailmicroid && $this->user->email && $this->profile->profileurl) {
            $id = new Microid('mailto:'.$this->user->email,
                              $this->selfUrl());
            $this->element('meta', array('name' => 'microid',
                                         'content' => $id->toString()));
        }
        if ($this->user->jabbermicroid && $this->user->jabber && $this->profile->profileurl) {
            $id = new Microid('xmpp:'.$this->user->jabber,
                              $this->selfUrl());
            $this->element('meta', array('name' => 'microid',
                                         'content' => $id->toString()));
        }

        // See https://wiki.mozilla.org/Microsummaries

        $this->element('link', array('rel' => 'microsummary',
                                     'href' => common_local_url('microsummary',
                                                                array('nickname' => $this->profile->nickname))));
    }

    function showProfile()
    {
        $this->elementStart('div', 'entity_profile vcard author');
        $this->element('h2', null, _('User profile'));

        $avatar = $this->profile->getAvatar(AVATAR_PROFILE_SIZE);
        $this->elementStart('dl', 'entity_depiction');
        $this->element('dt', null, _('Photo'));
        $this->elementStart('dd');
        $this->element('img', array('src' => ($avatar) ? $avatar->displayUrl() : Avatar::defaultImage(AVATAR_PROFILE_SIZE),
                                    'class' => 'photo avatar',
                                    'width' => AVATAR_PROFILE_SIZE,
                                    'height' => AVATAR_PROFILE_SIZE,
                                    'alt' => $this->profile->nickname));
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_nickname');
        $this->element('dt', null, _('Nickname'));
        $this->elementStart('dd');
        $hasFN = ($this->profile->fullname) ? 'nickname url uid' : 'fn nickname url uid';
        $this->element('a', array('href' => $this->profile->profileurl,
                                  'rel' => 'me', 'class' => $hasFN),
                            $this->profile->nickname);
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        if ($this->profile->fullname) {
            $this->elementStart('dl', 'entity_fn');
            $this->element('dt', null, _('Full name'));
            $this->elementStart('dd');
            $this->element('span', 'fn', $this->profile->fullname);
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }

        if ($this->profile->location) {
            $this->elementStart('dl', 'entity_location');
            $this->element('dt', null, _('Location'));
            $this->element('dd', 'location', $this->profile->location);
            $this->elementEnd('dl');
        }

        if ($this->profile->homepage) {
            $this->elementStart('dl', 'entity_url');
            $this->element('dt', null, _('URL'));
            $this->elementStart('dd');
            $this->element('a', array('href' => $this->profile->homepage,
                                      'rel' => 'me', 'class' => 'url'),
                           $this->profile->homepage);
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }

        if ($this->profile->bio) {
            $this->elementStart('dl', 'entity_note');
            $this->element('dt', null, _('Note'));
            $this->element('dd', 'note', $this->profile->bio);
            $this->elementEnd('dl');
        }

        $tags = Profile_tag::getTags($this->profile->id, $this->profile->id);
        if (count($tags) > 0) {
            $this->elementStart('dl', 'entity_tags');
            $this->element('dt', null, _('Tags'));
            $this->elementStart('dd');
            $this->elementStart('ul', 'tags xoxo');
            foreach ($tags as $tag) {
                $this->elementStart('li');
                $this->element('span', 'mark_hash', '#');
                $this->element('a', array('rel' => 'tag',
                                          'href' => common_local_url('peopletag',
                                                                     array('tag' => $tag))),
                               $tag);
                $this->elementEnd('li');
            }
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }
        $this->elementEnd('div');

        $this->elementStart('div', 'entity_actions');
        $this->element('h2', null, _('User actions'));
        $this->elementStart('ul');
        $cur = common_current_user();

        if ($cur && $cur->id == $this->profile->id) {
            $this->elementStart('li', 'entity_edit');
            $this->element('a', array('href' => common_local_url('profilesettings'),
                                      'title' => _('Edit profile settings')),
                                      _('Edit'));
            $this->elementEnd('li');
        }

        if ($cur) {
            if ($cur->id != $this->profile->id) {
                $this->elementStart('li', 'entity_subscribe');
                if ($cur->isSubscribed($this->profile) || $cur->isPendingSubscriptionTo($this->profile)) {
                    $usf = new UnsubscribeForm($this, $this->profile);
                    $usf->show();
                } else {
                    $sf = new SubscribeForm($this, $this->profile);
                    $sf->show();
                }
                $this->elementEnd('li');
            }
        } else {
            $this->elementStart('li', 'entity_subscribe');
            $this->showRemoteSubscribeLink();
            $this->elementEnd('li');
        }

        $user = User::staticGet('id', $this->profile->id);
        if ($cur && $cur->id != $user->id && $cur->mutuallySubscribed($user)) {
           $this->elementStart('li', 'entity_send-a-message');
            $this->element('a', array('href' => common_local_url('newmessage', array('to' => $user->id)),
                                      'title' => _('Send a direct message to this user')),
                           _('Message'));
            $this->elementEnd('li');

            if ($user->email && $user->emailnotifynudge) {
                $this->elementStart('li', 'entity_nudge');
                $nf = new NudgeForm($this, $user);
                $nf->show();
                $this->elementEnd('li');
            }
        }

        if ($cur && $cur->id != $this->profile->id) {
            $blocked = $cur->hasBlocked($this->profile);
            $this->elementStart('li', 'entity_block');
            if ($blocked) {
                $ubf = new UnblockForm($this, $this->profile);
                $ubf->show();
            } else {
                $bf = new BlockForm($this, $this->profile);
                $bf->show();
            }
            $this->elementEnd('li');
        }
        $this->elementEnd('ul');
        $this->elementEnd('div');
    }
    
    function showDatingProfileBlurb()
    {
        $datingProfile = $this->user->getDatingProfile();
        
        //Belt and braces
        if ($datingProfile == false) {
            //TODO frank: throw an error here as it seems dating profiles are not enabled
            $this->clientError(_('Dating profiles are not enabled.'));
        }
        
        $this->elementStart('div', 'entity_profile vcard author');
        $this->element('h2', null, _('User profile'));

        $avatar = $this->profile->getAvatar(AVATAR_PROFILE_SIZE);
        $this->elementStart('dl', 'entity_depiction');
        $this->element('dt', null, _('Photo'));
        $this->elementStart('dd');
        $this->element('img', array('src' => ($avatar) ? $avatar->displayUrl() : Avatar::defaultImage(AVATAR_PROFILE_SIZE),
                                    'class' => 'photo avatar',
                                    'width' => AVATAR_PROFILE_SIZE,
                                    'height' => AVATAR_PROFILE_SIZE,
                                    'alt' => $this->profile->nickname));
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_nickname');
        $this->element('dt', null, _('Nickname'));
        $this->elementStart('dd');
        $hasFN = ($this->profile->fullname) ? 'nickname url uid' : 'fn nickname url uid';
        $this->element('a', array('href' => $this->profile->profileurl,
                                  'rel' => 'me', 'class' => $hasFN),
                            $this->profile->nickname);
        $this->elementEnd('dd');
        $this->elementEnd('dl');

        if ($datingProfile->firstname) {
            $this->elementStart('dl', 'entity_fn');
            $this->element('dt', null, _('Full name'));
            $this->elementStart('dd');
            $this->element('span', 'fn', ($datingProfile->lastname)?$datingProfile->firstname.' '.$datingProfile->lastname:$datingProfile->firstname);
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }

        $countryList = get_nice_country_list();
        $country = $countryList[$datingProfile->country];
        $this->elementStart('dl', 'entity_location');
        $this->element('dt', null, _('Location'));
        $this->element('dd', 'location', ($datingProfile->city)?$datingProfile->city.', '.$country:$country);
        $this->elementEnd('dl');

        if ($datingProfile->bio) {
            $this->elementStart('dl', 'entity_note');
            $this->element('dt', null, _('Note'));
            $this->element('dd', 'note', $datingProfile->bio);
            $this->elementEnd('dl');
        }

        $tags = Dating_profile_tag::getTags($this->profile->id, $this->profile->id);
        if (count($tags) > 0) {
            $this->elementStart('dl', 'entity_tags');
            $this->element('dt', null, _('Tags'));
            $this->elementStart('dd');
            $this->elementStart('ul', 'tags xoxo');
            foreach ($tags as $tag) {
                $this->elementStart('li');
                $this->element('span', 'mark_hash', '#');
                $this->element('a', array('rel' => 'tag',
                                          'href' => common_local_url('peopletag',
                                                                     array('tag' => $tag))),
                               $tag);
                $this->elementEnd('li');
            }
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }
        $this->elementEnd('div');

        $this->elementStart('div', 'entity_actions');
        $this->element('h2', null, _('User actions'));
        $this->elementStart('ul');
        $cur = common_current_user();

        if ($cur && $cur->id == $this->profile->id) {
            $this->elementStart('li', 'entity_edit');
            $this->element('a', array('href' => common_local_url('profilesettings'),
                                      'title' => _('Edit profile settings')),
                                      _('Edit'));
            $this->elementEnd('li');
        }

        if ($cur) {
            if ($cur->id != $this->profile->id) {
                $this->elementStart('li', 'entity_subscribe');
                if ($cur->isSubscribed($this->profile) || $cur->isPendingSubscriptionTo($this->profile)) {
                    $usf = new UnsubscribeForm($this, $this->profile);
                    $usf->show();
                } else {
                    $sf = new SubscribeForm($this, $this->profile);
                    $sf->show();
                }
                $this->elementEnd('li');
            }
        } else {
            $this->elementStart('li', 'entity_subscribe');
            $this->showRemoteSubscribeLink();
            $this->elementEnd('li');
        }

        $user = User::staticGet('id', $this->profile->id);
        if ($cur && $cur->id != $user->id && $cur->mutuallySubscribed($user)) {
           $this->elementStart('li', 'entity_send-a-message');
            $this->element('a', array('href' => common_local_url('newmessage', array('to' => $user->id)),
                                      'title' => _('Send a direct message to this user')),
                           _('Message'));
            $this->elementEnd('li');

            if ($user->email && $user->emailnotifynudge) {
                $this->elementStart('li', 'entity_nudge');
                $nf = new NudgeForm($this, $user);
                $nf->show();
                $this->elementEnd('li');
            }
        }

        if ($cur && $cur->id != $this->profile->id) {
            $blocked = $cur->hasBlocked($this->profile);
            $this->elementStart('li', 'entity_block');
            if ($blocked) {
                $ubf = new UnblockForm($this, $this->profile);
                $ubf->show();
            } else {
                $bf = new BlockForm($this, $this->profile);
                $bf->show();
            }
            $this->elementEnd('li');
        }
        $this->elementEnd('ul');
        $this->elementEnd('div');
    }
    
    function showDatingProfile() 
    {
        
        $datingProfile = $this->user->getDatingProfile();
        
        //Belt and braces
        if ($datingProfile == false) {
            //TODO frank: throw an error here as it seems dating profiles are not enabled
        }
        
        $this->elementStart('div', 'entity_dating_profile vcard author');
        $this->element('h2', null, _('Dating profile'));
        
        //$this->element('p', null, _('Place parts of dating profile in collapsable divs in here.'));
        
        if ($datingProfile->firstname) {
            $this->elementStart('dl', 'name');
            $this->element('dt', null, _('Name'));
            $this->element('dd', 'name', $datingProfile->firstname.' '.$datingProfile->lastname);
            $this->elementEnd('dl');
        }
        
        $this->elementStart('dl', 'address_1');
        $this->element('dt', null, _('Street'));
        $this->element('dd', 'address_1', $datingProfile->address_1);
        $this->elementEnd('dl');
            
        $this->elementStart('dl', 'city');
        $this->element('dt', null, _('City'));
        $this->element('dd', 'city', $datingProfile->city);
        $this->elementEnd('dl');
        
        $this->elementStart('dl', 'state');
        $this->element('dt', null, _('State'));
        $this->element('dd', 'state', $datingProfile->state);
        $this->elementEnd('dl');
        
        $niceCountryList = get_nice_country_list();
        $this->elementStart('dl', 'country');
        $this->element('dt', null, _('Country'));
        $this->element('dd', 'country', $niceCountryList[$datingProfile->country]);
        $this->elementEnd('dl');
        
        $this->elementStart('dl', 'postcode');
        $this->element('dt', null, _('Postcode'));
        $this->element('dd', 'postcode', $datingProfile->postcode);
        $this->elementEnd('dl');
        
        if ($datingProfile->bio) {
            $this->elementStart('dl', 'bio');
            $this->element('dt', null, _('Bio'));
            $this->element('dd', 'bio', $datingProfile->bio);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->birthdate) {
            $birthDateObject = new DateTime($datingProfile->birthdate);
            $this->elementStart('dl', 'birthdate');
            $this->element('dt', null, _('Birthdate'));
            $this->element('dd', 'birthdate', $birthDateObject->format('j M Y'));
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->sex) {
            $sexList = $datingProfile->getNiceSexList();
            $this->elementStart('dl', 'sex');
            $this->element('dt', null, _('Sex'));
            $this->element('dd', 'sex', $sexList[$datingProfile->sex]);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->partner_sex) {
            $sexList = $datingProfile->getNiceSexList();
            $this->elementStart('dl', 'partner_sex');
            $this->element('dt', null, _('Looking for a'));
            $this->element('dd', 'partner_sex', $sexList[$datingProfile->partner_sex]);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->interested_in) {
            $interestedinList = $datingProfile->getNiceInterestList();
            $this->elementStart('dl', 'interested_in');
            $this->element('dt', null, _('Interested in'));
            $this->element('dd', 'interested_in', $interestedinList[$datingProfile->interested_in]);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->profession) {
            $this->elementStart('dl', 'profession');
            $this->element('dt', null, _('Profession'));
            $this->element('dd', 'profession', $datingProfile->profession);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->headline) {
            $this->elementStart('dl', 'headline');
            $this->element('dt', null, _('Headline'));
            $this->element('dd', 'headline', $datingProfile->headline);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->height) {
            $heightList = $datingProfile->getNiceHeightList();
            $this->elementStart('dl', 'height');
            $this->element('dt', null, _('Height'));
            $this->element('dd', 'height', $heightList[$datingProfile->height]);
            $this->elementEnd('dl');
        }
        
        if ($datingProfile->hair) {
            $hairList = $datingProfile->getNiceHairList();
            $this->elementStart('dl', 'hair');
            $this->element('dt', null, _('Hair'));
            $this->element('dd', 'hair', $hairList[$datingProfile->hair]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->body_type) {
            $bodyTypeList = $datingProfile->getNiceBodytypeList();
            $this->elementStart('dl', 'body_type');
            $this->element('dt', null, _('Body Type'));
            $this->element('dd', 'body_type', $bodyTypeList[$datingProfile->body_type]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->ethnicity) {
            $ethnicityList = $datingProfile->getNiceEthnicityList();
            $this->elementStart('dl', 'ethnicity');
            $this->element('dt', null, _('Ethnicity'));
            $this->element('dd', 'ethnicity', $ethnicityList[$datingProfile->ethnicity]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->eye_colour) {
            $eyecolourList = $datingProfile->getNiceEyeColourList();
            $this->elementStart('dl', 'eye_colour');
            $this->element('dt', null, _('Eye Colour'));
            $this->element('dd', 'eye_colour', $eyecolourList[$datingProfile->eye_colour]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->marital_status) {
            $maritalStatusList = $datingProfile->getNiceMaritalStatusList();
            $this->elementStart('dl', 'marital_status');
            $this->element('dt', null, _('Marital Status'));
            $this->element('dd', 'marital_status', $maritalStatusList[$datingProfile->marital_status]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->have_children) {
            $childrenList = $datingProfile->getNiceHaveChildrenStatusList();
            $this->elementStart('dl', 'have_children');
            $this->element('dt', null, _('Has Children'));
            $this->element('dd', 'have_children', $childrenList[$datingProfile->have_children]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->smoke) {
            $smokeList = $datingProfile->getNiceDoYouStatusList();
            $this->elementStart('dl', 'smoke');
            $this->element('dt', null, _('Smoke'));
            $this->element('dd', 'smoke', $smokeList[$datingProfile->smoke]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->drink) {
            $drinkList = $datingProfile->getNiceDoYouStatusList();
            $this->elementStart('dl', 'drink');
            $this->element('dt', null, _('Drink'));
            $this->element('dd', 'drink', $drinkList[$datingProfile->drink]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->religion) {
            $religionList = $datingProfile->getNiceReligionStatusList();
            $this->elementStart('dl', 'religion');
            $this->element('dt', null, _('Religion'));
            $this->element('dd', 'religion', $religionList[$datingProfile->religion]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->education) {
            $educationList = $datingProfile->getNiceEducationStatusList();
            $this->elementStart('dl', 'education');
            $this->element('dt', null, _('Education'));
            $this->element('dd', 'education', $educationList[$datingProfile->education]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->politics) {
            $politicsList = $datingProfile->getNicePoliticsStatusList();
            $this->elementStart('dl', 'politics');
            $this->element('dt', null, _('Politics'));
            $this->element('dd', 'politics', $politicsList[$datingProfile->politics]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->best_feature) {
            $featureList = $datingProfile->getNiceBestFeatureStatusList();
            $this->elementStart('dl', 'best_feature');
            $this->element('dt', null, _('Best Feature'));
            $this->element('dd', 'best_feature', $featureList[$datingProfile->best_feature]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->body_art) {
            $bodyArtList = $datingProfile->getNiceBodyArtStatusList();
            $this->elementStart('dl', 'body_art');
            $this->element('dt', null, _('Body Art'));
            $this->element('dd', 'body_art', $bodyArtList[$datingProfile->body_art]);
            $this->elementEnd('dl');
        }
        if ($datingProfile->fun) {
            $this->elementStart('dl', 'fun');
            $this->element('dt', null, _('What I do for fun'));
            $this->element('dd', 'fun', $datingProfile->fun);
            $this->elementEnd('dl');
        }
        if ($datingProfile->fav_spot) {
            $this->elementStart('dl', 'fav_spot');
            $this->element('dt', null, _('Favourite spot'));
            $this->element('dd', 'fav_spot', $datingProfile->fav_spot);
            $this->elementEnd('dl');
        }
        if ($datingProfile->fav_media) {
            $this->elementStart('dl', 'fav_media');
            $this->element('dt', null, _('Favourite books/movies'));
            $this->element('dd', 'fav_media', $datingProfile->fav_media);
            $this->elementEnd('dl');
        }
        if ($datingProfile->first_date) {
            $this->elementStart('dl', 'first_date');
            $this->element('dt', null, _('My idea of a good first date'));
            $this->element('dd', 'first_date', $datingProfile->first_date);
            $this->elementEnd('dl');
        }
        $languages = $datingProfile->getLanguages();
        $languageList = $datingProfile->getNiceLanguageStatusList();
        if (!empty($languages)) {
            $this->elementStart('dl', 'first_date');
            $this->element('dt', null, _('Languages'));
            foreach ($languages as $language) {
                $this->element('dd', 'language', $languageList[$language]);
            }
            $this->elementEnd('dl');
        }
        
        
        $tags = Dating_profile_tag::getTags($datingProfile->id, $datingProfile->id);
        if (count($tags) > 0) {
            $this->elementStart('dl', 'entity_interests');
            $this->element('dt', null, _('Interests'));
            $this->elementStart('dd');
            $this->elementStart('ul', 'tags xoxo');
            foreach ($tags as $tag) {
                $this->elementStart('li');
                $this->element('span', 'mark_hash', '#');
                $this->element('a', array('rel' => 'tag',
                                          'href' => common_local_url('interesttag',
                                                                     array('tag' => $tag))),
                               $tag);
                $this->elementEnd('li');
            }
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
        }
        

        $this->elementEnd('div');
    }

    function showRemoteSubscribeLink()
    {
        $url = common_local_url('remotesubscribe',
                                array('nickname' => $this->profile->nickname));
        $this->element('a', array('href' => $url,
                                  'class' => 'entity_remote_subscribe'),
                       _('Subscribe'));
    }

    function showNotices()
    {
        $notice = $this->user->getNotices(($this->page-1)*NOTICES_PER_PAGE, NOTICES_PER_PAGE + 1);

        $pnl = new ProfileNoticeList($notice, $this);
        $cnt = $pnl->show();

        $this->pagination($this->page>1, $cnt>NOTICES_PER_PAGE, $this->page,
                          'showstream', array('nickname' => $this->user->nickname));
    }

    function showSections()
    {
        if (common_config('profile', 'enable_dating')) {
            
            /*
             * Only show parts of the profile page to the current user depending on their authorisation.
             */
            switch ($this->auth) {
                case 0:
                case 1:
                    $this->showGroups();
                    $this->showStatistics();
                    $cloud = new PersonalTagCloudSection($this, $this->user, true);
                    $cloud->show();
                    break;
                case 2:
                    $this->showGroups();
                    $this->showStatistics();
                    $cloud = new PersonalTagCloudSection($this, $this->user, true);
                    $cloud->show();
                    break;
                case 3:
                    $this->showSubscriptions();
                    $this->showSubscribers();
                    $this->showGroups();
                    $this->showStatistics();
                    $cloud = new PersonalTagCloudSection($this, $this->user, false);
                    $cloud->show();
                    break;
            }
        }
        else {
            $this->showSubscriptions();
            $this->showSubscribers();
            $this->showGroups();
            $this->showStatistics();
            $cloud = new PersonalTagCloudSection($this, $this->user);
            $cloud->show();
        }
    }

    function showSubscriptions()
    {
        $profile = $this->user->getSubscriptions(0, PROFILES_PER_MINILIST + 1);

        $this->elementStart('div', array('id' => 'entity_subscriptions',
                                         'class' => 'section'));

        $this->element('h2', null, _('Subscriptions'));

        if ($profile) {
            $pml = new ProfileMiniList($profile, $this->user, $this);
            $cnt = $pml->show();
            if ($cnt == 0) {
                $this->element('p', null, _('(None)'));
            }
        }

        if ($cnt > PROFILES_PER_MINILIST) {
            $this->elementStart('p');
            $this->element('a', array('href' => common_local_url('subscriptions',
                                                                 array('nickname' => $this->profile->nickname)),
                                      'class' => 'more'),
                           _('All subscriptions'));
            $this->elementEnd('p');
        }

        $this->elementEnd('div');
    }

    function showSubscribers()
    {
        $profile = $this->user->getSubscribers(0, PROFILES_PER_MINILIST + 1);

        $this->elementStart('div', array('id' => 'entity_subscribers',
                                         'class' => 'section'));

        $this->element('h2', null, _('Subscribers'));

        if ($profile) {
            $pml = new ProfileMiniList($profile, $this->user, $this);
            $cnt = $pml->show();
            if ($cnt == 0) {
                $this->element('p', null, _('(None)'));
            }
        }

        if ($cnt > PROFILES_PER_MINILIST) {
            $this->elementStart('p');
            $this->element('a', array('href' => common_local_url('subscribers',
                                                                 array('nickname' => $this->profile->nickname)),
                                      'class' => 'more'),
                           _('All subscribers'));
            $this->elementEnd('p');
        }

        $this->elementEnd('div');
    }

    function showStatistics()
    {
        // XXX: WORM cache this
        $subs = new Subscription();
        $subs->subscriber = $this->profile->id;
        $subs_count = (int) $subs->count() - 1;

        $subbed = new Subscription();
        $subbed->subscribed = $this->profile->id;
        $subbed_count = (int) $subbed->count() - 1;

        $notices = new Notice();
        $notices->profile_id = $this->profile->id;
        $notice_count = (int) $notices->count();

        $this->elementStart('div', array('id' => 'entity_statistics',
                                         'class' => 'section'));

        $this->element('h2', null, _('Statistics'));

        // Other stats...?
        $this->elementStart('dl', 'entity_member-since');
        $this->element('dt', null, _('Member since'));
        $this->element('dd', null, date('j M Y',
                                                 strtotime($this->profile->created)));
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_subscriptions');
        $this->elementStart('dt');
        if ($this->auth === 3) {
            $this->element('a', array('href' => common_local_url('subscriptions',
                                                             array('nickname' => $this->profile->nickname))),
                           _('Subscriptions'));
        }
        else {
            $this->element('span', null, _('Subscriptions'));
        }        
        $this->elementEnd('dt');
        $this->element('dd', null, (is_int($subs_count)) ? $subs_count : '0');
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_subscribers');
        $this->elementStart('dt');
        if ($this->auth === 3) {
            $this->element('a', array('href' => common_local_url('subscribers',
                                                             array('nickname' => $this->profile->nickname))),
                       _('Subscribers'));
        }
        else {
            $this->element('span', null, _('Subscribers'));
        }
        $this->elementEnd('dt');
        $this->element('dd', 'subscribers', (is_int($subbed_count)) ? $subbed_count : '0');
        $this->elementEnd('dl');

        $this->elementStart('dl', 'entity_notices');
        $this->element('dt', null, _('Notices'));
        $this->element('dd', null, (is_int($notice_count)) ? $notice_count : '0');
        $this->elementEnd('dl');

        $this->elementEnd('div');
    }

    function showGroups()
    {
        $groups = $this->user->getGroups(0, GROUPS_PER_MINILIST + 1, true);

        $this->elementStart('div', array('id' => 'entity_groups',
                                         'class' => 'section'));

        $this->element('h2', null, _('Groups'));

        if ($groups) {
            $gml = new GroupMiniList($groups, $this->user, $this);
            $cnt = $gml->show();
            if ($cnt == 0) {
                $this->element('p', null, _('(None)'));
            }
        }

        if ($cnt > GROUPS_PER_MINILIST) {
            $this->elementStart('p');
            $this->element('a', array('href' => common_local_url('usergroups',
                                                                 array('nickname' => $this->profile->nickname)),
                                      'class' => 'more'),
                           _('All groups'));
            $this->elementEnd('p');
        }

        $this->elementEnd('div');
    }

    function showAnonymousMessage()
    {

		$m = sprintf(_('**%s** has an account on %%%%site.name%%%%, a [micro-blogging](http://en.wikipedia.org/wiki/Micro-blogging) service ' .
                       'based on the Free Software [Laconica](http://laconi.ca/) tool. ' .
                       '[Join now](%%%%action.register%%%%) to follow **%s**\'s notices and many more! ([Read more](%%%%doc.help%%%%))'),
                     $this->user->nickname, $this->user->nickname);
        
        if (common_config('profile', 'enable_dating')) {             
            $m = sprintf(_('You are viewing **%s**\'s account on %%%%site.name%%%%, a dating site which incorporates micro blogging. ' .
                           'Because you are not logged in you can only see part of **%s**\'s account and not dating information. ' .
                           '[Join now](%%%%action.register%%%%) to follow **%s**\'s notices and flirt! ([Read more](%%%%doc.help%%%%))'),
                         $this->user->nickname, $this->user->nickname, $this->user->nickname);
        }
                     
        $this->elementStart('div', array('id' => 'anon_notice'));
        $this->raw(common_markup_to_html($m));
        $this->elementEnd('div');
    }

}

// We don't show the author for a profile, since we already know who it is!

class ProfileNoticeList extends NoticeList
{
    function newListItem($notice)
    {
        return new ProfileNoticeListItem($notice, $this->out);
    }
}

class ProfileNoticeListItem extends NoticeListItem
{
    function showAuthor()
    {
        return;
    }
}
