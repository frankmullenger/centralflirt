<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * List of a user's subscriptions
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
 * @category  Social
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

/**
 * A list of the user's subscriptions
 *
 * @category Social
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

if (!defined('LACONICA')) { exit(1); }

class SubscriptionsAction extends GalleryAction
{
    function title()
    {
        if ($this->page == 1) {
            return sprintf(_('%s subscriptions'), $this->user->nickname);
        } else {
            return sprintf(_('%s subscriptions, page %d'),
                           $this->user->nickname,
                           $this->page);
        }
    }

    function showPageNotice()
    {
        $user =& common_current_user();
        if ($user && ($user->id == $this->profile->id)) {
            $this->element('p', null,
                           _('These are the people whose notices '.
                             'you listen to.'));
        } else {
            $this->element('p', null,
                           sprintf(_('These are the people whose '.
                                     'notices %s listens to.'),
                                   $this->profile->nickname));
        }
    }

    function getAllTags()
    {
        return $this->getTags('subscribed', 'subscriber');
    }

    function showContent()
    {
        parent::showContent();
        
        if (common_config('profile', 'enable_dating')) {
            $this->showDatingContent();
            return;
        }

        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;

        if ($this->tag) {
            $subscriptions = $this->user->getTaggedSubscriptions($this->tag, $offset, $limit);
        } else {
            $subscriptions = $this->user->getSubscriptions($offset, $limit);
        }

        if ($subscriptions) {
            $subscriptions_list = new SubscriptionsList($subscriptions, $this->user, $this);
            $cnt = $subscriptions_list->show();
        }

        $subscriptions->free();

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'subscriptions',
                          array('nickname' => $this->user->nickname));
    }
    
    /**
     * Show subscriptions without any tagging for the dating site
     *
     */
    function showDatingContent()
    {
        $offset = ($this->page-1) * PROFILES_PER_PAGE;
        $limit =  PROFILES_PER_PAGE + 1;

        $cnt = 0;
        $subscriptions = $this->user->getSubscriptions($offset, $limit);
        
        if ($subscriptions) {
            $subscriptions_list = new SubscriptionsList($subscriptions, $this->user, $this);
            $cnt = $subscriptions_list->show();
        }
        $subscriptions->free();

        $this->pagination($this->page > 1, $cnt > PROFILES_PER_PAGE,
                          $this->page, 'subscriptions',
                          array('nickname' => $this->user->nickname));
    }
}

class SubscriptionsList extends ProfileList
{
    public $datingProfile = null;
    public $user = null;
    
    function showOwnerControls($profile)
    {
        $sub = Subscription::pkeyGet(array('subscriber' => $this->owner->id,
                                           'subscribed' => $profile->id));
        if (!$sub) {
            return;
        }
        
        //TODO frank: implement this again once jabber and sms is worked out
        /*
        $this->out->elementStart('form', array('id' => 'subedit-' . $profile->id,
                                          'method' => 'post',
                                          'class' => 'form_subcription_edit',
                                          'action' => common_local_url('subedit')));
        $this->out->hidden('token', common_session_token());
        $this->out->hidden('profile', $profile->id);
        $this->out->checkbox('jabber', _('Jabber'), $sub->jabber);
        $this->out->checkbox('sms', _('SMS'), $sub->sms);
        $this->out->submit('save', _('Save'));
        $this->out->elementEnd('form');
        */
        
        return;
    }
    
    /**
     * Overwriting this function for the dating site.
     */
    function showProfile()
    {
        if (!common_config('profile', 'enable_dating')) {
            parent::showProfile();
            return;
        }

        //to set the dating profile
        $this->user = User::staticGet('id', $this->profile->id);
        $this->datingProfile = $this->user->getDatingProfile();
        
        //If dating profile for a user does not exist, then something has gone wrong
        if ($this->datingProfile === false) {
            $this->serverError(_('A user exists without a dating profile.'));
            return;
        }
        
        $this->out->elementStart('li', array('class' => 'profile',
                                             'id' => 'profile-' . $this->profile->id));

        $user = common_current_user();

        $this->out->elementStart('div', 'entity_profile vcard');

        $avatar = $this->profile->getAvatar(AVATAR_STREAM_SIZE);
        $this->out->elementStart('a', array('href' => $this->profile->profileurl,
                                            'class' => 'url'));
        $this->out->element('img', array('src' => ($avatar) ? $avatar->displayUrl() : Avatar::defaultImage(AVATAR_STREAM_SIZE),
                                         'class' => 'photo avatar',
                                         'width' => AVATAR_STREAM_SIZE,
                                         'height' => AVATAR_STREAM_SIZE,
                                         'alt' =>
                                         ($this->profile->fullname) ? $this->profile->fullname :
                                         $this->profile->nickname));
                                         
        $this->out->elementStart('span', 'entity_nickname');
        $this->out->raw($this->profile->nickname);
        $this->out->elementEnd('span');
        $this->out->elementEnd('a');
        
        $age = $this->datingProfile->getAge();
        $this->out->elementStart('span', 'entity_age');
        $this->out->raw($age);
        $this->out->elementEnd('span');

        if ($this->datingProfile->headline) {
            $this->out->elementStart('h2', 'entity_headline');
            $this->out->raw($this->datingProfile->headline);
            $this->out->elementEnd('h2');
        }
        if ($this->datingProfile->bio) {
            $this->out->elementStart('p', 'entity_bio');
            $this->out->raw($this->datingProfile->getTruncatedBio());
            $this->out->elementEnd('p');
        }

        # If we're on a list with an owner (subscriptions or subscribers)...

        if ($this->owner) {
            # Get tags
            $tags = Dating_profile_tag::getTags($this->owner->id, $this->profile->id);

            if ($tags) {
                $this->out->elementStart('dl', 'entity_tags');
                $this->out->elementStart('dd');
    
                $this->out->elementStart('ul', 'tags xoxo');
                foreach ($tags as $tag) {
                    $this->out->elementStart('li');
                    //$this->out->element('span', 'mark_hash', '#');
                    $this->out->element('a', array('rel' => 'tag',
                                                   'href' => common_local_url('interesttag', array('tag' => $tag))),
                                        '#'.$tag);
                    $this->out->elementEnd('li');
                }
                $this->out->elementEnd('ul');
    
                $this->out->elementEnd('dd');
                $this->out->elementEnd('dl');
            }
        }
    
        $countryList = get_nice_country_list();
        $this->out->elementStart('span', 'entity_location');
        $this->out->raw($this->highlight(ucwords(($this->datingProfile->city)?$this->datingProfile->city.', '.$countryList[$this->datingProfile->country]:$countryList[$this->datingProfile->country])));
        $this->out->elementEnd('span');

        if ($user && $user->id == $this->owner->id) {
            $this->showOwnerControls($this->profile);
        }

        $this->out->elementEnd('div');

        $this->out->elementStart('div', 'entity_actions');

        $this->out->elementStart('ul');

        if ($user && $user->id != $this->profile->id) {
            # XXX: special-case for user looking at own
            # subscriptions page
            $this->out->elementStart('li', 'entity_subscribe');
            if ($user->isSubscribed($this->profile)) {
                $usf = new UnsubscribeForm($this->out, $this->profile);
                $usf->show();
            } else {
                $sf = new SubscribeForm($this->out, $this->profile);
                $sf->show();
            }
            $this->out->elementEnd('li');
            $this->out->elementStart('li', 'entity_block');
            if ($user && $user->id == $this->owner->id) {
                $this->showBlockForm();
            }
            $this->out->elementEnd('li');
        }

        $this->out->elementEnd('ul');

        $this->out->elementEnd('div');

        $this->out->elementEnd('li');
    }
}
