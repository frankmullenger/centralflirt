<?php
/**
 * Class to search through dating profiles. Based on people search class.
 *
 * PHP version 5
 *
 * @category Action
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Frank Mullenger <frankmullenger@gmail.com>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://laconi.ca/
 *
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

require_once INSTALLDIR.'/lib/searchaction.php';
require_once INSTALLDIR.'/lib/profilelist.php';

/**
 * People search action class.
 *
 * @category Action
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Robin Millette <millette@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://laconi.ca/
 */
class DatingsearchAction extends SearchAction
{
    function getInstructions()
    {
        if (common_config('profile', 'enable_dating')) {
            return;
        }
        return _('Search for people on %%site.name%%.');
    }

    function title()
    {
        return _('Search for a Date');
    }
    
    function showForm($error=null) {
        
        global $config;
        
        //Check if dating profiles are enabled before allowing a search.
        if (!common_config('profile', 'enable_dating')) {
            //TODO throw an error here because dating profiles are not enabled
        }

        //If a user is not logged in continue to allow searching of dating profiles
        $user = common_current_user();
        if (!is_null($user)) {
            $datingProfile = $user->getDatingProfile();
            
            //Belt and braces
            if ($datingProfile === false) {
                //TODO throw an error here because dating profiles are not enabled
            }
        }
        else {
            $datingProfile = new Dating_profile();
            $datingProfile->sex = Dating_profile::SEX_FEMALE;
            $datingProfile->partner_sex = Dating_profile::SEX_MALE;
        }
        
        /**
         * Basic search should be : age range, sex, seeking a, location, with pics
         */

        //Get all the search options here
        $sex = $this->trimmed('sex');
        $partner_sex = $this->trimmed('partner_sex');
        $age_lower = $this->trimmed('age_lower');
        $age_upper = $this->trimmed('age_upper');
        $city = $this->trimmed('city');
        $country = $this->trimmed('country');
        
        //TODO validation needs to be done here!!
        
        $page = $this->trimmed('page', 1);
        $this->elementStart('form', array('method' => 'get',
                                           'id' => 'form_search',
                                           'class' => 'form_settings',
                                           'action' => common_local_url($this->trimmed('action'))));
        
        if (!isset($config['site']['fancy']) || !$config['site']['fancy']) {
            $this->hidden('action', $this->trimmed('action'));
        }
        
        $this->elementStart('fieldset');
        $this->element('legend', null, _('Search dating profiles'));
        $this->elementStart('ul', 'form_data');
        $this->elementStart('li');
        $this->dropdown('sex', _('You\'re a'),
                         $datingProfile->getNiceSexList(), null, false, (empty($sex))?$datingProfile->sex:$sex);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('partner_sex', _('Seeking a'),
                         $datingProfile->getNiceSexList(), null, false, (empty($partner_sex))?$datingProfile->partner_sex:$partner_sex);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('age_lower', _('Between'),
                         $this->getNiceAgeList(), null, false, (empty($age_lower))?25:$age_lower);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('age_upper', _('And'),
                         $this->getNiceAgeList(), null, false, (empty($age_upper))?35:$age_upper);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->input('city', _('City'), $city);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('country', _('Country'),
                     get_nice_country_list(), null, true, $country);
        $this->elementEnd('li');
        
        $this->elementEnd('ul');
        
        $this->submit('search', 'Search');
        $this->elementEnd('fieldset');
        $this->elementEnd('form');
        
        //TODO frank: check for all the search options here
        if ($sex || $partner_sex || $age_lower || $age_upper || $city || $country) {
            
            //TODO frank: make array keys constants in Dating Profile object?
            $q = array('sex' => $sex, 
                       'partner_sex' => $partner_sex, 
                       'age_lower' => $age_lower, 
                       'age_upper' => $age_upper,
                       'city' => $city,
                       'country' => $country);
            $this->showResults($q, $page);
        }
    }
    
    function getNiceAgeList() {
        $ageList = array();
        for ($i=18; $i<=99; $i++) {
            $ageList[$i] = $i;
        }
        return $ageList;
    }

    /**
     * Queries for dating profiles are currently limited to MySQL databases
     * @see Dating_profile::getSearchEngine()
     *
     * @param array $q
     * @param int $page
     */
    function showResults($q, $page)
    {
        
        $datingProfile = new Dating_profile();
        
        //TODO wrap in a try/catch 
        $search_engine = $datingProfile->getSearchEngine('identica_dating');
        
        $search_engine->set_sort_mode('chron');
        # Ask for an extra to see if there's more.
        $search_engine->limit((($page-1)*PROFILES_PER_PAGE), PROFILES_PER_PAGE + 1);
        if (false === $search_engine->query($q)) {
            $cnt = 0;
        }
        else {
            $cnt = $datingProfile->find();
        }
        if ($cnt > 0) {

            $results = new DatingSearchResults($datingProfile, true, $this);
            $results->show();
        } else {
            $this->element('p', 'error', _('No results'));
        }
        
        $datingProfile->free();
        
        $this->pagination($page > 1, $cnt > PROFILES_PER_PAGE,
                          $page, 'datingsearch', $q);

    }
}

class DatingSearchResults extends ProfileList
{
    /** Current dating profile, profile query. */
    var $datingProfile = null;
    var $pattern = null;
    
    function __construct($profile, $terms, $action)
    {
        parent::__construct($profile, $terms, $action);
        $this->datingProfile = $profile;
        
        //The pattern is set as a dummy for showProfile() below
        $this->pattern = '//i';
    }
    
    function show()
    {

        $this->out->elementStart('ul', 'profiles');

        $cnt = 0;

        while ($this->datingProfile->fetch()) {
            $cnt++;
            if($cnt > PROFILES_PER_PAGE) {
                break;
            }
            $this->showProfile();
        }

        $this->out->elementEnd('ul');

        return $cnt;
    }

    /**
     * TODO frank: decide which profile to link to, dating or ordinary and the visibility of such profiles regarding the messages posted
     *
     */
    function showProfile()
    {
        //Set the profile
        $this->profile = $this->datingProfile->getProfile();
        
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
            if ($user->isSubscribed($this->profile) || $user->isPendingSubscriptionTo($this->profile)) {
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
    
    function highlight($text)
    {
        return preg_replace($this->pattern, '<strong>\\1</strong>', htmlspecialchars($text));
    }

    function isReadOnly()
    {
        return true;
    }
}

