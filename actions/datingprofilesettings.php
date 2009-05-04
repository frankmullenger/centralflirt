<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Change dating profile settings
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
 * @category  Settings
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Frank Mullenger <frankmullenger@gmail.com>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/accountsettingsaction.php';

/**
 * Change profile settings
 *
 * @category Settings
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Zach Copley <zach@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

//TODO frank: need to test whether the dating is enabled before processing any of this action

class DatingprofilesettingsAction extends AccountSettingsAction
{
    /**
     * Title of the page
     *
     * @return string Title of the page
     */

    function title()
    {
        return _('Dating Profile settings');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */

    function getInstructions()
    {
        return _('You can update your dating profile here so people know more about you.');
    }

    /**
     * Content area of the page
     *
     * Shows a form for uploading an avatar.
     *
     * @return void
     */

    function showContent()
    {
        $user = common_current_user();
        $profile = $user->getProfile();
        $datingProfile = $user->getDatingProfile();
        

        if ($datingProfile === false) {
            //TODO throw an error here because dating profiles are not enabled
        }

        $this->elementStart('form', array('method' => 'post',
                                           'id' => 'form_settings_dating_profile',
                                           'class' => 'form_settings',
                                           'action' => common_local_url('datingprofilesettings')));
        $this->elementStart('fieldset');
        $this->element('legend', null, _('Dating Profile Information'));
        $this->hidden('token', common_session_token());

        # too much common patterns here... abstractable?
        $this->elementStart('ul', 'form_data');
        
        $this->elementStart('li', 'sub_heading');
        $this->element('h2', null, _('Personal Details'));
        $this->elementEnd('li');

        $this->elementStart('li');
        $this->input('firstname', _('First name'),
                     ($this->arg('firstname')) ? $this->arg('firstname') : $datingProfile->firstname);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->input('lastname', _('Last name'),
                     ($this->arg('lastname')) ? $this->arg('lastname') : $datingProfile->lastname);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->input('address_1', _('Street Address'),
                     ($this->arg('address_1')) ? $this->arg('address_1') : $datingProfile->address_1);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->input('city', _('City'),
                     ($this->arg('city')) ? $this->arg('city') : $datingProfile->city);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->input('state', _('State'),
                     ($this->arg('state')) ? $this->arg('state') : $datingProfile->state);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('country', _('Country'),
                     get_nice_country_list(), null, false, $datingProfile->country);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->input('postcode', _('Postcode'),
                     ($this->arg('postcode')) ? $this->arg('postcode') : $datingProfile->postcode);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('sex', _('Sex'),
                     $datingProfile->getNiceSexList(), null, false, $datingProfile->sex);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('partner_sex', _('Looking For'),
                     $datingProfile->getNiceSexList(), null, false, $datingProfile->partner_sex);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('interested_in', _('Interested In'),
                     $datingProfile->getNiceInterestList(), null, false, $datingProfile->interested_in);
        $this->elementEnd('li');

        $this->elementStart('li');
        $this->dropdown('birthdate_day', _('Birthdate'),
                     $datingProfile->getNiceMonthDayList(), null, false, $datingProfile->getBirthdate('d'));  
         $this->dropdown('birthdate_month', null,
                     $datingProfile->getNiceMonthList(), null, false, $datingProfile->getBirthdate('m')); 
         $this->dropdown('birthdate_year', null,
                     $datingProfile->getNiceYearList(), null, false, $datingProfile->getBirthdate('Y'));       
        $this->elementEnd('li');
        
        
        
        $this->elementStart('li', 'sub_heading');
        $this->element('h2', null, _('Profile'));
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->input('headline', _('Headline'),
                     ($this->arg('headline')) ? $this->arg('headline') : $datingProfile->headline);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->textarea('bio', _('Bio'),
                     ($this->arg('bio')) ? $this->arg('bio') : $datingProfile->bio);
        $this->elementEnd('li');
        
        
        
        $this->elementStart('li');
        $this->input('interests', _('Interests'),
                     ($this->arg('interests')) ? $this->arg('interests') : implode(', ', $datingProfile->getInterestTags()),
                     _('Tags for yourself, must be comma separated'));
        $this->elementEnd('li');
        
        
        
        $this->elementStart('li', 'sub_heading');
        $this->element('h2', null, _('Physical Appearance'));
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('height', _('Height'),
                     $datingProfile->getNiceHeightList(), null, true, $datingProfile->height);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('hair', _('Hair'),
                     $datingProfile->getNiceHairList(), null, true, $datingProfile->hair);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('body_type', _('Body Type'),
                     $datingProfile->getNiceBodytypeList(), null, true, $datingProfile->body_type);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('ethnicity', _('Ethnicity'),
                     $datingProfile->getNiceEthnicityList(), null, true, $datingProfile->ethnicity);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('eye_colour', _('Eye Colour'),
                     $datingProfile->getNiceEyeColourList(), null, true, $datingProfile->eye_colour);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('body_art', _('Body Art'),
                     $datingProfile->getNiceBodyArtStatusList(), null, true, $datingProfile->body_art);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('best_feature', _('Best feature'),
                     $datingProfile->getNiceBestFeatureStatusList(), null, true, $datingProfile->best_feature);
        $this->elementEnd('li');
        
        $this->elementStart('li', 'sub_heading');
        $this->element('h2', null, _('Lifestyle'));
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->input('profession', _('Profession'),
                     ($this->arg('profession')) ? $this->arg('profession') : $datingProfile->profession);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('marital_status', _('Marital Status'),
                     $datingProfile->getNiceMaritalStatusList(), null, true, $datingProfile->marital_status);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('have_children', _('Do you have Children?'),
                     $datingProfile->getNiceHaveChildrenStatusList(), null, true, $datingProfile->have_children);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('smoke', _('Do you smoke?'),
                     $datingProfile->getNiceDoYouStatusList(), null, true, $datingProfile->smoke);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('drink', _('Do you drink'),
                     $datingProfile->getNiceDoYouStatusList(), null, true, $datingProfile->drink);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('religion', _('Religion'),
                     $datingProfile->getNiceReligionStatusList(), null, true, $datingProfile->religion);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->elementStart('fieldset', array('id' => 'languages'));
        $this->element('legend', null, 'Languages');
        $languageList = $datingProfile->getNiceLanguageStatusList();
        $languageIds = $datingProfile->getLanguages();
        foreach ($languageList as $languageId => $language) {
            $checked = false;
            if (in_array($languageId, $languageIds)) {
                $checked = true;
            }
            $this->elementStart('div', 'language');
            $this->checkbox("language[$languageId]", _($language), $checked, $instructions=null, $value=$languageId);
            $this->elementEnd('div');
        }
        $this->elementEnd('fieldset');
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('education', _('Education'),
                     $datingProfile->getNiceEducationStatusList(), null, true, $datingProfile->education);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->dropdown('politics', _('Politics'),
                     $datingProfile->getNicePoliticsStatusList(), null, true, $datingProfile->politics);
        $this->elementEnd('li');
        
        
        $this->elementStart('li', 'sub_heading');
        $this->element('h2', null, _('Personality'));
        $this->elementEnd('li');
        
        
        $this->elementStart('li');
        $this->textarea('fun', _('What do you do for fun?'),
                     ($this->arg('fun')) ? $this->arg('fun') : $datingProfile->fun);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->textarea('fav_spot', _('What is your favourite spot?'),
                     ($this->arg('fav_spot')) ? $this->arg('fav_spot') : $datingProfile->fav_spot);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->textarea('fav_media', _('Favourite books/movies?'),
                     ($this->arg('fav_media')) ? $this->arg('fav_media') : $datingProfile->fav_media);
        $this->elementEnd('li');
        
        $this->elementStart('li');
        $this->textarea('first_date', _('What would you do/like to do on a first date?'),
                     ($this->arg('first_date')) ? $this->arg('first_date') : $datingProfile->first_date);
        $this->elementEnd('li');
        
        $this->elementEnd('ul');
        $this->submit('save', _('Save'));

        $this->elementEnd('fieldset');
        $this->elementEnd('form');
    }

    /**
     * Handle a post
     *
     * Validate input and save changes. Reload the form with a success
     * or error message.
     *
     * @return void
     */

    function handlePost()
    {
        # CSRF protection

        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            $this->showForm(_('There was a problem with your session token. '.
                               'Try again, please.'));
            return;
        }

        $firstname = $this->trimmed('firstname');
        $lastname = $this->trimmed('lastname');
        $address_1 = $this->trimmed('address_1');
        $city = $this->trimmed('city');
        $state = $this->trimmed('state');
        $country = $this->trimmed('country');
        $postcode = $this->trimmed('postcode');
        $bio = $this->trimmed('bio');
        $sex = $this->trimmed('sex');
        $partner_sex = $this->trimmed('partner_sex');
        $interested_in = $this->trimmed('interested_in');
        $birthdate = $this->trimmed('birthdate_year') .'-'. $this->trimmed('birthdate_month') .'-'. $this->trimmed('birthdate_day');
        
        $profession = $this->trimmed('profession');
        $headline = $this->trimmed('headline');
        $height = $this->trimmed('height');
        $hair = $this->trimmed('hair');
        $body_type = $this->trimmed('body_type');
        $ethnicity = $this->trimmed('ethnicity');
        $eye_colour = $this->trimmed('eye_colour');
        $marital_status = $this->trimmed('marital_status');
        $have_children = $this->trimmed('have_children');
        $smoke = $this->trimmed('smoke');
        $drink = $this->trimmed('drink');
        $religion = $this->trimmed('religion');
        $education = $this->trimmed('education');
        $politics = $this->trimmed('politics');
        $best_feature = $this->trimmed('best_feature');
        $body_art = $this->trimmed('body_art');
        $fun = $this->trimmed('fun');
        $fav_spot = $this->trimmed('fav_spot');
        $fav_media = $this->trimmed('fav_media');
        $first_date = $this->trimmed('first_date');
        
        /*
         * Languages and interests need to be treated differently
         */
        echo '<pre>';
        echo 'why is this not working?!';
        print_r($this->arg('language'));
        print_r($this->args);
        echo '</pre>';
        
        $languages = '';
        if ($this->arg('language')) {
            $languages = implode(';', $this->arg('language'));
        }
        
        
        
        $interests = $this->trimmed('interests');

        if ($interests) {
            $interest_tags = array_map('common_canonical_interest_tag', preg_split('/[,]+/', $interests));
        } else {
            $interest_tags = array();
        }
        foreach ($interest_tags as $tag) {
            if (!common_valid_profile_interest($tag)) {
                $this->showForm(sprintf(_('Invalid tag: "%s"'), $tag));
                return;
            }
        }

        //TODO frank: validation needs to be done here!

        $user = common_current_user();
        $datingProfile = $user->getDatingProfile();
        
        if ($datingProfile === false) {
            //TODO frank: throw an error in here
            exit('dating profile does not exist');
        }

        $orig_datingProfile = clone($datingProfile);

        $datingProfile->id = $user->id;
        $datingProfile->firstname = $firstname;
        $datingProfile->lastname = $lastname;
        $datingProfile->address_1 = $address_1;
        $datingProfile->city = $city;
        $datingProfile->state = $state;
        $datingProfile->country = $country;
        $datingProfile->postcode = $postcode;
        $datingProfile->bio = $bio;
        $datingProfile->birthdate = $birthdate;
        $datingProfile->sex = $sex;
        $datingProfile->partner_sex = $partner_sex;
        $datingProfile->interested_in = $interested_in;
        
        $datingProfile->profession = $profession;
        $datingProfile->headline = $headline;
        $datingProfile->height = $height;
        $datingProfile->hair = $hair;
        $datingProfile->body_type = $body_type;
        $datingProfile->ethnicity = $ethnicity;
        $datingProfile->eye_colour = $eye_colour;
        $datingProfile->marital_status = $marital_status;
        $datingProfile->have_children = $have_children;
        $datingProfile->smoke = $smoke;
        $datingProfile->drink = $drink;
        $datingProfile->religion = $religion;
        $datingProfile->education = $education;
        $datingProfile->politics = $politics;
        $datingProfile->best_feature = $best_feature;
        $datingProfile->body_art = $body_art;
        $datingProfile->fun = $fun;
        $datingProfile->fav_spot = $fav_spot;
        $datingProfile->fav_media = $fav_media;
        $datingProfile->first_date = $first_date;
        
        $datingProfile->languages = $languages;

        common_debug('Old profile: ' . common_log_objstring($orig_datingProfile), __FILE__);
        common_debug('New profile: ' . common_log_objstring($datingProfile), __FILE__);
        
        
        $datingProfile->query('BEGIN');

        try {
            if (empty($orig_datingProfile->id)) {
                
                $datingProfile->url = common_profile_url($nickname);
                $datingProfile->created = common_sql_now();
                $result = $datingProfile->insert();
                
                if (!$result) {
                    common_log_db_error($datingProfile, 'UPDATE', __FILE__);
                    $this->serverError(_('Could not save the profile.'));
                    throw new Exception('Could not save profile.');
                }
                
                $tagsresult = $datingProfile->setInterestTags($interest_tags);
                if (!$tagsresult) {
                    $this->serverError(_('Couldn\'t save interests on new profile..'));
                    throw new Exception('Could not save interests.');
                }
            }
            else {
                
                $tagsresult = $datingProfile->setInterestTags($interest_tags);
                if (!$tagsresult) {
                    $this->serverError(_('Couldn\'t save interests.'));
                    throw new Exception('Could not save interests.');
                }
            
                $result = $datingProfile->update($orig_datingProfile);
                
                if (!$result && !$tagsresult) {
                    common_log_db_error($datingProfile, 'UPDATE', __FILE__);
                    $this->serverError(_('Did not update the profile.'));
                    throw new Exception('Could not update profile.');
                }
            }
            
            $datingProfile->query('COMMIT');
            
        }
        catch (Exception $e) {
            $datingProfile->query('ROLLBACK');
            return;
        }


        common_broadcast_profile($datingProfile);

        $this->showForm(_('Settings saved.'), true);
    }

}
