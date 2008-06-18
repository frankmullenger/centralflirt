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

require_once(INSTALLDIR.'/lib/openid.php');

class FinishopenidloginAction extends Action {

	function handle($args) {
		parent::handle($args);
		if (common_logged_in()) {
			common_user_error(_t('Already logged in.'));
		} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($this->boolean('create')) {
				$this->create_new_user();
			} else if ($this->boolean('connect')) {
				$this->connect_user();
			} else {
				$this->show_form(_t('Something weird happened.'),
								 $this->trimmed('newname'));
			}
		} else {
			$this->try_login();
		}
	}

	function show_form($error=NULL, $username=NULL) {
		common_show_header(_t('OpenID Account Setup'));
		if ($error) {
			common_element('div', array('class' => 'error'), $error);
		} else {
			global $config;
			common_element('div', 'instructions',
						   _t('This is the first time you\'ve logged into ') .
						   $config['site']['name'] .
						   _t(' so we must connect your OpenID to a local account. ' .
							  ' You can either create a new account, or connect with ' .
							  ' your existing account, if you have one.'));
		}
		common_element_start('form', array('method' => 'POST',
										   'id' => 'account_connect',
										   'action' => common_local_url('finishopenidlogin')));
		common_element('h2', NULL,
					   'Create new account');
		common_element('p', NULL,
					   _t('Create a new user with this nickname.'));
		common_input('newname', _t('New nickname'),
					 ($username) ? $username : '',
					 _t('1-64 lowercase letters or numbers, no punctuation or spaces'));
		common_submit('create', _t('Create'));
		common_element('h2', NULL,
					   'Create new account');
		common_element('p', NULL,
					   _t('If you already have an account, login with your username and password '.
						  'to connect it to your OpenID.'));
		common_input('nickname', _t('Existing nickname'));
		common_password('password', _t('Password'));
		common_submit('connect', _t('Connect'));
		common_element_end('form');
		common_show_footer();
	}

	function try_login() {
		
		$consumer = oid_consumer();

		$response = $consumer->complete(common_local_url('finishopenidlogin'));

		if ($response->status == Auth_OpenID_CANCEL) {
			$this->message(_t('OpenID authentication cancelled.'));
			return;
		} else if ($response->status == Auth_OpenID_FAILURE) {
			// Authentication failed; display the error message.
			$this->message(_t('OpenID authentication failed: ') . $response->message);
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			// This means the authentication succeeded; extract the
			// identity URL and Simple Registration data (if it was
			// returned).
			$display = $response->getDisplayIdentifier();
			$canonical = ($response->endpoint->canonicalID) ?
			  $response->endpoint->canonicalID : $response->getDisplayIdentifier();

			$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

			if ($sreg_resp) {
				$sreg = $sreg_resp->contents();
			}

			$user = $this->get_user($canonical);
			
			if ($user) {
				$this->update_user($user, $sreg);
				common_set_user($user->nickname);
				$this->go_home($user->nickname);
			} else {
				$this->save_values($display, $canonical, $sreg);
				$this->show_form(NULL, $this->best_new_nickname($display, $sreg));
			}
		}
	}

	function message($msg) {
		common_show_header(_t('OpenID Login'));
		common_element('p', NULL, $msg);
		common_show_footer();
	}
	
	function get_user($canonical) {
		$user = NULL;
		$oid = User_openid::staticGet('canonical', $canonical);
		if ($oid) {
			$user = User::staticGet('id', $oid->user_id);
		}
		return $user;
	}

	function update_user($user, $sreg) {
		
		$profile = $user->getProfile();

		$orig_profile = clone($profile);
		
		if ($sreg['fullname'] && strlen($sreg['fullname']) <= 255) {
			$profile->fullname = $sreg['fullname'];
		}
		
		if ($sreg['country']) {
			if ($sreg['postcode']) {
				# XXX: use postcode to get city and region
				# XXX: also, store postcode somewhere -- it's valuable!
				$profile->location = $sreg['postcode'] . ', ' . $sreg['country'];
			} else {
				$profile->location = $sreg['country'];
			}
		}

		# XXX save language if it's passed
		# XXX save timezone if it's passed
		
		if (!$profile->update($orig_profile)) {
			common_server_error(_t('Error saving the profile.'));
			return;
		}

		$orig_user = clone($user);
		
		if ($sreg['email'] && Validate::email($sreg['email'], true)) {
			$user->email = $sreg['email'];
		}
		
		if (!$user->update($orig_user)) {
			common_server_error(_t('Error saving the user.'));
			return;
		}
	}
	
	function save_values($display, $canonical, $sreg) {
		common_ensure_session();
		$_SESSION['openid_display'] = $display;
		$_SESSION['openid_canonical'] = $canonical;		
		$_SESSION['openid_sreg'] = $sreg;				
	}

	function get_saved_values($display, $canonical, $sreg) {
		common_ensure_session();
		return array($_SESSION['openid_display'],
					 $_SESSION['openid_canonical'],
					 $_SESSION['openid_sreg']);
	}
	
	function create_new_login() {
		
		$nickname = $this->trimmed('newname');
		
		if (!Validate::string($nickname, array('min_length' => 1,
											   'max_length' => 64,
											   'format' => VALIDATE_NUM . VALIDATE_ALPHA_LOWER))) {
			$this->show_form(_t('Nickname must have only letters and numbers and no spaces.'));
			return;
		}
		
		if (User::staticGet('nickname', $nickname)) {
			$this->show_form(_t('Nickname already in use. Try another one.'));
			return;
		}
		
		list($display, $canonical, $sreg) = $this->get_saved_values();
		
		if (!$display || !$canonical) {
			common_server_error(_t('Stored OpenID not found.'));
			return;
		}
		
		# Possible race condition... let's be paranoid
		
		$other = $this->get_user($canonical);
		
		if ($other) {
			common_server_error(_t('Creating new account for OpenID that already has a user.'));
			return;
		}
		
		$profile = new Profile();
		
		$profile->nickname = $nickname;
		
		if ($sreg['fullname'] && strlen($sreg['fullname']) <= 255) {
			$profile->fullname = $sreg['fullname'];
		}
		
		if ($sreg['country']) {
			if ($sreg['postcode']) {
				# XXX: use postcode to get city and region
				# XXX: also, store postcode somewhere -- it's valuable!
				$profile->location = $sreg['postcode'] . ', ' . $sreg['country'];
			} else {
				$profile->location = $sreg['country'];
			}
		}

		# XXX save language if it's passed
		# XXX save timezone if it's passed
		
		$profile->created = DB_DataObject_Cast::dateTime(); # current time
		
		$id = $profile->insert();
		if (!$id) {
			common_server_error(_t('Error saving the profile.'));
			return;
		}
		
		$user = new User();
		$user->id = $id;
		$user->nickname = $nickname;
		$user->uri = common_mint_tag('user:'.$id);
		
		if ($sreg['email'] && Validate::email($sreg['email'], true)) {
			$user->email = $sreg['email'];
		}
		
		$user->created = DB_DataObject_Cast::dateTime(); # current time
		
		$result = $user->insert();
		
		if (!$result) {
			# Try to clean up...
			$profile->delete();
		}

		$oid = new User_openid();
		$oid->display = $display;
		$oid->canonical = $canonical;
		$oid->user_id = $id;
		$oid->created = DB_DataObject_Cast::dateTime();
		
		$result = $oid->insert();

		if (!$result) {
			# Try to clean up...
			$user->delete();
			$profile->delete();
		}
		
		common_redirect(common_local_url('profilesettings'));
	}
	
	function connect_user() {
		
		$nickname = $this->trimmed('nickname');
		$password = $this->trimmed('password');

		if (!common_check_user($nickname, $password)) {
			$this->show_form(_t('Invalid username or password.'));
			return;
		}

		# They're legit!
		
		$user = User::staticGet('nickname', $nickname);

		list($display, $canonical, $sreg) = $this->get_saved_values();

		if (!$display || !$canonical) {
			common_server_error(_t('Stored OpenID not found.'));
			return;
		}
		
		$oid = new User_openid();
		$oid->display = $display;
		$oid->canonical = $canonical;
		$oid->user_id = $user->id;
		$oid->created = DB_DataObject_Cast::dateTime();
		
		if (!$oid->insert()) {
			common_server_error(_t('Error connecting OpenID.'));
			return;
		}
		
		$this->update_user($user, $sreg);
		common_set_user($user->nickname);
		$this->go_home($user->nickname);
	}
	
	function go_home($nickname) {
		$url = common_get_returnto();
		if ($url) {
			# We don't have to return to it again
			common_set_returnto(NULL);
		} else {
			$url = common_local_url('all',
									array('nickname' =>
										  $nickname));
		}
		common_redirect($url);
	}
	
	function best_new_nickname($display, $sreg) {
		
		# Try the passed-in nickname
		
		if ($sreg['nickname'] && $this->is_new_nickname($sreg['nickname'])) {
			return $sreg['nickname'];
		}

		# Try the full name

		if ($sreg['fullname']) {
			$fullname = $this->nicknamize($sreg['fullname']);
			if ($this->is_new_nickname($fullname)) {
				return $fullname;
			}
		}
		
		# Try the URL
		
		$from_url = $this->openid_to_nickname($display);
		
		if ($from_url && $this->is_new_nickname($from_url)) {
			return $from_url;
		}

		# XXX: others?

		return NULL;
	}

	function is_new_nickname($str) {
		if (!Validate::string($str, array('min_length' => 1,
										  'max_length' => 64,
										  'format' => VALIDATE_NUM . VALIDATE_ALPHA_LOWER))) {
			return false;
		}
		if (User::staticGet('nickname', $str)) {
			return false;
		}
		return true;
	}
	
	function openid_to_nickname($openid) {
        if (Auth_Yadis_identifierScheme($openid) == 'XRI') {
			return $this->xri_to_nickname($openid);
		} else {
			return $this->url_to_nickname($openid);
		}
	}

	# We try to use an OpenID URL as a legal Laconica user name in this order
	# 1. Plain hostname, like http://evanp.myopenid.com/
	# 2. One element in path, like http://profile.typekey.com/EvanProdromou/
	#    or http://getopenid.com/evanprodromou

    function url_to_nickname($openid) {
		static $bad = array('query', 'user', 'password', 'port', 'fragment');

	    $parts = parse_url($openid);

		# If any of these parts exist, this won't work

		foreach ($bad as $badpart) {
			if (array_key_exists($badpart, $parts)) {
				return NULL;
			}
		}

		# We just have host and/or path

		# If it's just a host...
		if (array_key_exists('host', $parts) &&
			(!array_key_exists('path', $parts) || strcmp($parts['path'], '/') == 0))
		{
			$hostparts = explode('.', $parts['host']);

			# Try to catch common idiom of nickname.service.tld

			if ((count($hostparts) > 2) &&
				(strlen($hostparts[count($hostparts) - 2]) > 3) && # try to skip .co.uk, .com.au
				(strcmp($hostparts[0], 'www') != 0))
			{
				return $this->nicknamize($hostparts[0]);
			} else {
				# Do the whole hostname
				return $this->nicknamize($parts['host']);
			}
		} else {
			if (array_key_exists('path', $parts)) {
				# Strip starting, ending slashes
				$path = preg_replace('@/$@', '', $parts['path']);
				$path = preg_replace('@^/@', '', $path);
				if (strpos($path, '/') === false) {
					return $this->nicknamize($path);
				}
			}
		}

		return NULL;
	}

	function xri_to_nickname($xri) {
		$base = $this->xri_base($xri);

		if (!$base) {
			return NULL;
		} else {
			# =evan.prodromou
			# or @gratis*evan.prodromou
			$parts = explode('*', substr($base, 1));
			return $this->nicknamize(array_pop($parts));
		}
	}
	
	function xri_base($xri) {
		if (substr($xri, 0, 6) == 'xri://') {
			return substr($xri, 6);
		} else {
			return $xri;
		}
	}

	# Given a string, try to make it work as a nickname
	
	function nicknamize($str) {
		$str = preg_replace('/\W/', '', $str);
		return strtolower($str);
	}
}
