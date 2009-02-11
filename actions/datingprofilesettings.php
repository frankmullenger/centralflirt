<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Change profile settings
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
 * @author    Zach Copley <zach@controlyourself.ca>
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

//TODO need to test whether the dating is enabled before processing any of this action

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
        return _('You can update your dating profile here '.
                  'so people know more about you.');
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
        $this->element('legend', null, _('Dating Profile information'));
        $this->hidden('token', common_session_token());

        # too much common patterns here... abstractable?

        $this->elementStart('ul', 'form_data');
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
        $this->textarea('bio', _('Bio'),
                     ($this->arg('bio')) ? $this->arg('bio') : $datingProfile->bio);
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
        $this->dropdown('birthdate_year', _('Year'),
                     $datingProfile->getNiceYearList(), null, false, $datingProfile->birthdate_year);
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('birthdate_month', _('Month'),
                     $datingProfile->getNiceMonthList(), null, false, $datingProfile->birthdate_month);  
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('birthdate_day', _('Day'),
                     $datingProfile->getNiceMonthDayList(), null, false, $datingProfile->birthdate_day);          
        $this->elementEnd('li');
        $this->elementStart('li');
        $this->dropdown('interested_in', _('Interested In'),
                     $datingProfile->getNiceInterestList(), null, false, $datingProfile->interested_in);
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
        $birthdate_year = $this->trimmed('birthdate_year');
        $birthdate_month = $this->trimmed('birthdate_month');
        $birthdate_day = $this->trimmed('birthdate_day');
        
        //TODO validation needs to go here !!!

        $user = common_current_user();
        $datingProfile = $user->getDatingProfile();
        
        if ($datingProfile === false) {
            //TODO throw an error in here
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
        $datingProfile->birthdate_year = $birthdate_year;
        $datingProfile->birthdate_month = $birthdate_month;
        $datingProfile->birthdate_day = $birthdate_day;
        $datingProfile->sex = $sex;
        $datingProfile->partner_sex = $partner_sex;
        $datingProfile->interested_in = $interested_in;

        common_debug('Old profile: ' . common_log_objstring($orig_datingProfile), __FILE__);
        common_debug('New profile: ' . common_log_objstring($datingProfile), __FILE__);

        if (empty($orig_datingProfile->id)) {
            
            $datingProfile->url = common_profile_url($nickname);
            $datingProfile->created = common_sql_now();
            $result = $datingProfile->insert();
        }
        else {
            $result = $datingProfile->update($orig_datingProfile);
        }

        if (!$result) {
            common_log_db_error($datingProfile, 'UPDATE', __FILE__);
            $this->serverError(_('Couldn\'t save dating profile.'));
            return;
        }

        common_broadcast_profile($datingProfile);

        $this->showForm(_('Settings saved.'), true);
    }

}
