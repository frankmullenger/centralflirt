<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Register a new user account
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
 * @category  Login
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

/**
 * An action for registering a new user account
 *
 * @category Login
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class DatingregisterAction extends Action
{
    const FORM_ACCOUNT_INFO = 1;
    const FORM_PERSONAL_DETAILS = 2;
    const FORM_PERSONAL_PROFILE = 3;
    
    /**
     * Has there been an error?
     */
    var $error = null;

    /**
     * Have we registered?
     */
    var $registered = false;
    
    private $formsection = null;

    /**
     * Title of the page
     *
     * @return string title
     */
    function title()
    {
        if ($this->registered) {
            return _('Registration successful');
        } else {
            return _('Register');
        }
    }

    /**
     * Handle input, produce output
     *
     * Switches on request method; either shows the form or handles its input.
     *
     * Checks if registration is closed and shows an error if so.
     *
     * @param array $args $_REQUEST data
     *
     * @return void
     */
    function handle($args)
    {
        parent::handle($args);

        if (common_config('site', 'closed')) {
            $this->clientError(_('Registration not allowed.'));
        } else if (common_logged_in()) {
            $this->clientError(_('Already logged in.'));
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            //Show the next form in the series for the dating registration.
            $this->formsection = $this->trimmed('formsection');
            
            common_debug($this->formsection);
            common_debug(DatingregisterAction::FORM_PERSONAL_PROFILE);
            
            if ($this->formsection != DatingregisterAction::FORM_PERSONAL_PROFILE) {
                $this->saveDataToSession();
                $this->showForm();
            }
            else {
                $this->tryRegister();
            }
        } else {
            $this->showForm();
        }
    }
    
    /**
     * Try to register a user
     *
     * Validates the input and tries to save a new user and profile
     * record. On success, shows an instructions page.
     *
     * @return void
     */
    function tryRegister()
    {
        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            $this->showForm(_('There was a problem with your session token. '.
                              'Try again, please.'));
            return;
        }
        
        //TODO frank: what is the point of this - how will this function interact on the API? if at all?
        //Belt and braces, this was also checked in handle()
        if ($this->formsection != DatingregisterAction::FORM_PERSONAL_PROFILE) {
            $this->showForm(_('You must fill out all forms in the registration process.'));
            return;
        }
        

        // invitation code, if any
        $code = $_SESSION['RegisterData']['User']['code'];

        if ($code) {
            $invite = Invitation::staticGet($code);
        }

        if (common_config('site', 'inviteonly') && !($code && $invite)) {
            $this->clientError(_('Sorry, only invited people can register.'));
            return;
        }
        
        $nickname           = $this->trimmed('nickname');
        $headline           = $this->trimmed('headline');
        $bio                = $this->trimmed('bio');
        $fun                = $this->trimmed('fun');
        $fav_spot           = $this->trimmed('fav_spot');
        $fav_media          = $this->trimmed('fav_media');
        $first_date         = $this->trimmed('first_date');
        
        // Whitespace is OK in a password!
        $password = $this->arg('password');
        $confirm  = $this->arg('confirm');
        
        //Grab the interests into an array
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
        
        // Input scrubbing
        $nickname = common_canonical_nickname($nickname);
        
        //Validation
        if (!Validate::string($nickname, array('min_length' => 1,
                                                      'max_length' => 64,
                                                      'format' => NICKNAME_FMT))) {
            $this->showForm(_('Nickname must have only lowercase letters '.
                              'and numbers and no spaces.'));
        } else if ($this->nicknameExists($nickname)) {
            $this->showForm(_('Nickname already in use. Try another one.'));
        } else if (!User::allowed_nickname($nickname)) {
            $this->showForm(_('Not a valid nickname.'));
        } else if (strlen($password) < 6) {
            $this->showForm(_('Password must be 6 or more characters.'));
            return;
        } else if ($password != $confirm) {
            $this->showForm(_('Passwords don\'t match.'));
        } else {
            $registerData = array();
            $registerData['User'] = array('nickname' => $nickname,
                                            'password' => $password);
            $registerData['DatingProfile'] = array('headline' => $headline,
                                                    'bio' => $bio,
                                                    'fun' => $fun,
                                                    'fav_spot' => $fav_spot,
                                                    'fav_media' => $fav_media,
                                                    'first_date' => $first_date,
                                                    'interests' => $interest_tags);
            common_ensure_session();
            $registerData['User'] = array_merge($_SESSION['registerData']['User'] ,$registerData['User']);
            $registerData['DatingProfile'] = array_merge($_SESSION['registerData']['DatingProfile'] ,$registerData['DatingProfile']);
            
            if ($user = User::datingRegister($registerData)) {
                if (!$user) {
                    $this->showForm(_('Invalid username or password.'));
                    return;
                }
                // success!
                if (!common_set_user($user)) {
                    $this->serverError(_('Error setting user.'));
                    return;
                }
                // this is a real login
                common_real_login(true);
                if ($this->boolean('rememberme')) {
                    common_debug('Adding rememberme cookie for ' . $nickname);
                    common_rememberme($user);
                }
                // Re-init language env in case it changed (not yet, but soon)
                common_init_language();
                $this->showSuccess();
            } else {
                $this->showForm(_('Invalid username or password.'));
            }
        }
    }
    
    /*
     * TODO frank: need to sort out session hijacking, prefill form with data in session? or does token passing take care of it?
     */
    function saveDataToSession() {

        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            $this->showForm(_('There was a problem with your session token. '.
                              'Try again, please.'));
            return;
        }
        
        // invitation code, if any
        $code = $this->trimmed('code');

        if ($code) {
            $invite = Invitation::staticGet($code);
        }

        if (common_config('site', 'inviteonly') && !($code && $invite)) {
            $this->clientError(_('Sorry, only invited people can register.'));
            return;
        }
        
        switch ($this->formsection) {

            case  DatingregisterAction::FORM_ACCOUNT_INFO:

                $email              = $this->trimmed('email');
                $country            = $this->trimmed('country');
                $sex                = $this->trimmed('sex');
                $partner_sex        = $this->trimmed('partner_sex');
                $interested_in      = $this->trimmed('interested_in');
                $birthdate          = $this->trimmed('birthdate_year') .'-'. $this->trimmed('birthdate_month') .'-'. $this->trimmed('birthdate_day');
                
                //Input scrubbing
                $email    = common_canonical_email($email);
                
                // Validation
                if (!Validate::string($email, array('min_length' => 1))) {
                    $this->showForm(_('An email address must be supplied.'));
                } else if ($email && !Validate::email($email, false)) {
                    $this->showForm(_('Not a valid email address.'));
                } else if ($this->emailExists($email)) {
                    $this->showForm(_('Email address already exists.'));
                } else {
                    $registerData = array();
                    $registerData['User'] = array('email' => $email,
                                                    'code' => $code);
                    $registerData['DatingProfile'] = array('country' => $country,
                                                            'sex' => $sex,
                                                            'partner_sex' => $partner_sex,
                                                            'interested_in' => $interested_in,
                                                            'birthdate' => $birthdate);
                    common_ensure_session();
                    $_SESSION['registerData'] = $registerData;
                }
                break;

            case DatingregisterAction::FORM_PERSONAL_DETAILS:
                
                $city = $this->trimmed('city');
                $state = $this->trimmed('state');
                $postcode = $this->trimmed('postcode');
                $profession = $this->trimmed('profession');
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
                
                $languages = '';
                if ($this->arg('language')) {
                    $languages = implode(';', $this->arg('language'));
                }

                $registerData = array();
                $registerData['User'] = array();
                $registerData['DatingProfile'] = array('city' => $city,
                                                        'state' => $state,
                                                        'postcode' => $postcode,
                                                        'profession' => $profession,
                                                        'height' => $height,
                                                        'hair' => $hair,
                                                        'body_type' => $body_type,
                                                        'ethnicity' => $ethnicity,
                                                        'eye_colour' => $eye_colour,
                                                        'marital_status' => $marital_status,
                                                        'have_children' => $have_children,
                                                        'smoke' => $smoke,
                                                        'drink' => $drink,
                                                        'religion' => $religion,
                                                        'education' => $education,
                                                        'politics' => $politics,
                                                        'best_feature' => $best_feature,
                                                        'body_art' => $body_art,
                                                        'languages' => $languages);
                common_ensure_session();
                $registerData['User'] = array_merge($_SESSION['registerData']['User'], $registerData['User']);
                $registerData['DatingProfile'] = array_merge($_SESSION['registerData']['DatingProfile'], $registerData['DatingProfile']);
                $_SESSION['registerData'] = $registerData;
                break;
                
            case DatingregisterAction::FORM_PERSONAL_PROFILE:
                //Belt and braces, handle() should have called tryRegister() instead
                $this->showForm(_('Something has gone wrong with the registration process.'));
                break;
                
            default:
                break;
        }
                
        
        
        /*
         * switch case through the different forms and perform error checking
         * alright to pass the hashed password in the session at this point?
         */
    }

    /**
     * Does the given nickname already exist?
     *
     * Checks a canonical nickname against the database.
     *
     * @param string $nickname nickname to check
     *
     * @return boolean true if the nickname already exists
     */
    function nicknameExists($nickname)
    {
        $user = User::staticGet('nickname', $nickname);
        return ($user !== false);
    }

    /**
     * Does the given email address already exist?
     *
     * Checks a canonical email address against the database.
     *
     * @param string $email email address to check
     *
     * @return boolean true if the address already exists
     */
    function emailExists($email)
    {
        $email = common_canonical_email($email);
        if (!$email || strlen($email) == 0) {
            return false;
        }
        $user = User::staticGet('email', $email);
        return ($user !== false);
    }

    // overrrided to add entry-title class
    function showPageTitle() {
        $this->element('h1', array('class' => 'entry-title'), $this->title());
    }

    // overrided to add hentry, and content-inner class
    function showContentBlock()
     {
         $this->elementStart('div', array('id' => 'content', 'class' => 'hentry'));
         $this->showPageTitle();
         $this->showPageNoticeBlock();
         $this->elementStart('div', array('id' => 'content_inner', 'class' => 'entry-content'));
         // show the actual content (forms, lists, whatever)
         $this->showContent();
         $this->elementEnd('div');
         $this->elementEnd('div');
     }

    /**
     * Instructions or a notice for the page
     *
     * Shows the error, if any, or instructions for registration.
     *
     * @return void
     */
    function showPageNotice()
    {
        if ($this->registered) {
            return;
        } else if ($this->error) {
            $this->element('p', 'error', $this->error);
        } else {
            $instr =
              common_markup_to_html(_('With this form you can create '.
                                      ' a new account. '));

            $this->elementStart('div', 'instructions');
            $this->raw($instr);
            $this->elementEnd('div');
        }
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
        if ($error != null) {
            $this->formsection = --$this->formsection;
        }
        
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
        if ($this->registered) {
            $this->showSuccessContent();
        } else {
            $this->showFormContent();
        }
    }

    /**
     * Show the registration form
     *
     * @return void
     */
    function showFormContent()
    {
        $code = $this->trimmed('code');

        if ($code) {
            $invite = Invitation::staticGet($code);
        }

        if (common_config('site', 'inviteonly') && !($code && $invite)) {
            $this->clientError(_('Sorry, only invited people can register.'));
            return;
        }
        $datingProfile = new Dating_profile();

        $this->elementStart('form', array('method' => 'post',
                                          'id' => 'form_register',
                                          'class' => 'form_settings',
                                          'action' => common_local_url('datingregister')));
        
        //Check the form section that was passed and display the next form for the user
        switch ($this->formsection) {

            case  DatingregisterAction::FORM_ACCOUNT_INFO:
                
                $this->elementStart('fieldset');
                $this->element('legend', null, 'Personal Details');
                $this->hidden('token', common_session_token());
        
                if ($code) {
                    $this->hidden('code', $code);
                }
                
                //Pass the number of the form here
                $this->hidden('formsection', DatingregisterAction::FORM_PERSONAL_DETAILS);
                $this->elementStart('ul', 'form_data');
                
                $this->elementStart('li');
                $this->input('city', _('City'),
                             ($this->arg('city')) ? $this->arg('city') : $datingProfile->city);
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->input('state', _('State'),
                             ($this->arg('state')) ? $this->arg('state') : $datingProfile->state);
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->input('postcode', _('Postcode'),
                             ($this->arg('postcode')) ? $this->arg('postcode') : $datingProfile->postcode);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->input('profession', _('Profession'),
                             ($this->arg('profession')) ? $this->arg('profession') : $datingProfile->profession);
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
                $this->elementStart('fieldset');
                $this->element('legend', null, 'Languages');
                $languageList = $datingProfile->getNiceLanguageStatusList();
                $languageIds = $datingProfile->getLanguages();
                foreach ($languageList as $languageId => $language) {
                    $checked = false;
                    if (in_array($languageId, $languageIds)) {
                        $checked = true;
                    }
                    $this->checkbox("language[$languageId]", _($language), $checked, $instructions=null, $value=$languageId);
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
                
                $this->elementStart('li');
                $this->dropdown('best_feature', _('Best feature'),
                             $datingProfile->getNiceBestFeatureStatusList(), null, true, $datingProfile->best_feature);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('body_art', _('Body Art'),
                             $datingProfile->getNiceBodyArtStatusList(), null, true, $datingProfile->body_art);
                $this->elementEnd('li');
                
                $this->elementEnd('ul');
                $this->submit('submit', _('Register'));
                $this->elementEnd('fieldset');
                break;

            case DatingregisterAction::FORM_PERSONAL_DETAILS:
                $this->elementStart('fieldset');
                $this->element('legend', null, 'Personal Details');
                $this->hidden('token', common_session_token());
        
                if ($code) {
                    $this->hidden('code', $code);
                }
                
                //Pass the number of the form here
                $this->hidden('formsection', DatingregisterAction::FORM_PERSONAL_PROFILE);
                $this->elementStart('ul', 'form_data');
                
                $this->elementStart('li');
                $this->input('nickname', _('Nickname'), $this->trimmed('nickname'),
                             _('1-64 lowercase letters or numbers, '.
                               'no punctuation or spaces. Required.'));
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->password('password', _('Password'),
                                _('6 or more characters. Required.'));
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->password('confirm', _('Confirm'),
                                _('Same as password above. Required.'));
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
                             $this->arg('interests'),
                             _('Tags for yourself, must be comma separated'));
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
                $this->submit('submit', _('Register'));
                $this->elementEnd('fieldset');
                break;
                
            default:
                $this->elementStart('fieldset');
                $this->element('legend', null, 'Account settings');
                $this->hidden('token', common_session_token());
        
                if ($code) {
                    $this->hidden('code', $code);
                }
                
                //Pass the number of the form here
                $this->hidden('formsection', DatingregisterAction::FORM_ACCOUNT_INFO);
        
                $this->elementStart('ul', 'form_data');
                
                $this->elementStart('li');
                if ($invite && $invite->address_type == 'email') {
                    $this->input('email', _('Email'), $invite->address,
                                 _('Used only for updates, announcements, '.
                                   'and password recovery'));
                } else {
                    $this->input('email', _('Email'), $this->trimmed('email'),
                                 _('Used only for updates, announcements, '.
                                   'and password recovery'));
                }
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('country', _('Country'),
                             get_nice_country_list(), null, false, $datingProfile->country);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('sex', _('Sex'),
                             $datingProfile->getNiceSexList(), null, false, Dating_profile::SEX_MALE);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('partner_sex', _('Looking For'),
                             $datingProfile->getNiceSexList(), null, false, Dating_profile::SEX_FEMALE);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('interested_in', _('Interested In'),
                             $datingProfile->getNiceInterestList(), null, false, $datingProfile->interested_in);
                $this->elementEnd('li');
                
                $this->elementStart('li');
                $this->dropdown('birthdate_day', _('Day'),
                             $datingProfile->getNiceMonthDayList(), null, false, $datingProfile->getBirthdate('d'));          
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->dropdown('birthdate_month', _('Month'),
                             $datingProfile->getNiceMonthList(), null, false, $datingProfile->getBirthdate('m'));  
                $this->elementEnd('li');
                $this->elementStart('li');
                $this->dropdown('birthdate_year', _('Year'),
                             $datingProfile->getNiceYearList(), null, false, $datingProfile->getBirthdate('Y'));
                $this->elementEnd('li');
                
                
                $this->elementEnd('ul');
                $this->submit('submit', _('Register'));
                $this->elementEnd('fieldset');
                break;
        }
        $this->elementEnd('form');
    }

    /**
     * Show some information about registering for the site
     *
     * Save the registration flag, run showPage
     *
     * @return void
     */

    function showSuccess()
    {
        $this->registered = true;
        $this->showPage();
    }

    /**
     * Show some information about registering for the site
     *
     * Gives some information and options for new registrees.
     *
     * @return void
     */

    function showSuccessContent()
    {
        $nickname = $this->arg('nickname');

        $profileurl = common_local_url('showstream',
                                       array('nickname' => $nickname));

        $this->elementStart('div', 'success');
        $instr = sprintf(_('Congratulations, %s! And welcome to %%%%site.name%%%%. '.
                           'From here, you may want to...'. "\n\n" .
                           '* Go to [your profile](%s) '.
                           'and post your first message.' .  "\n" .
                           '* Add a [Jabber/GTalk address]'.
                           '(%%%%action.imsettings%%%%) '.
                           'so you can send notices '.
                           'through instant messages.' . "\n" .
                           '* [Search for people](%%%%action.peoplesearch%%%%) '.
                           'that you may know or '.
                           'that share your interests. ' . "\n" .
                           '* Update your [profile settings]'.
                           '(%%%%action.profilesettings%%%%)'.
                           ' to tell others more about you. ' . "\n" .
                           '* Read over the [online docs](%%%%doc.help%%%%)'.
                           ' for features you may have missed. ' . "\n\n" .
                           'Thanks for signing up and we hope '.
                           'you enjoy using this service.'),
                         $nickname, $profileurl);

        $this->raw(common_markup_to_html($instr));

        $have_email = $this->trimmed('email');
        if ($have_email) {
            $emailinstr = _('(You should receive a message by email '.
                            'momentarily, with ' .
                            'instructions on how to confirm '.
                            'your email address.)');
            $this->raw(common_markup_to_html($emailinstr));
        }
        $this->elementEnd('div');
    }

    /**
     * Show the login group nav menu
     *
     * @return void
     */

    function showLocalNav()
    {
        $nav = new LoginGroupNav($this);
        $nav->show();
    }
}
