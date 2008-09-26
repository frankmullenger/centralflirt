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

require_once(INSTALLDIR.'/lib/omb.php');

class RemotesubscribeAction extends Action {

	function handle($args) {

		parent::handle($args);

		if (common_logged_in()) {
			common_user_error(_('You can use the local subscription!'));
		    return;
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			# CSRF protection
			$token = $this->trimmed('token');
			if (!$token || $token != common_session_token()) {
				$this->show_form(_('There was a problem with your session token. Try again, please.'));
				return;
			}
			
			$this->remote_subscription();
		} else {
			$this->show_form();
		}
	}

	function get_instructions() {
		return _('To subscribe, you can [login](%%action.login%%),' .
		          ' or [register](%%action.register%%) a new ' .
		          ' account. If you already have an account ' .
		          ' on a [compatible microblogging site](%%doc.openmublog%%), ' .
		          ' enter your profile URL below.');
	}

	function show_top($err=NULL) {
		if ($err) {
			common_element('div', 'error', $err);
		} else {
			$instructions = $this->get_instructions();
			$output = common_markup_to_html($instructions);
			common_element_start('div', 'instructions');
			common_raw($output);
			common_element_end('p');
		}
	}

	function show_form($err=NULL) {
		$nickname = $this->trimmed('nickname');
		$profile = $this->trimmed('profile_url');
		common_show_header(_('Remote subscribe'), NULL, $err,
						   array($this, 'show_top'));
		# id = remotesubscribe conflicts with the
		# button on profile page
		common_element_start('form', array('id' => 'remsub', 'method' => 'post',
										   'action' => common_local_url('remotesubscribe')));
		common_hidden('token', common_session_token());
		common_input('nickname', _('User nickname'), $nickname,
					 _('Nickname of the user you want to follow'));
		common_input('profile_url', _('Profile URL'), $profile,
					 _('URL of your profile on another compatible microblogging service'));
		common_submit('submit', _('Subscribe'));
		common_element_end('form');
		common_show_footer();
	}

	function remote_subscription() {
		$user = $this->get_user();

		if (!$user) {
			$this->show_form(_('No such user.'));
			return;
		}

		$profile = $this->trimmed('profile_url');

		if (!$profile) {
			$this->show_form(_('No such user.'));
			return;
		}

		if (!Validate::uri($profile, array('allowed_schemes' => array('http', 'https')))) {
			$this->show_form(_('Invalid profile URL (bad format)'));
			return;
		}

		$fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
		$yadis = Auth_Yadis_Yadis::discover($profile, $fetcher);

		if (!$yadis || $yadis->failed) {
			$this->show_form(_('Not a valid profile URL (no YADIS document).'));
			return;
		}

		# XXX: a little liberal for sites that accidentally put whitespace before the xml declaration
		
        $xrds =& Auth_Yadis_XRDS::parseXRDS(trim($yadis->response_text));

		if (!$xrds) {
			$this->show_form(_('Not a valid profile URL (no XRDS defined).'));
			return;
		}

		$omb = $this->getOmb($xrds);

		if (!$omb) {
			$this->show_form(_('Not a valid profile URL (incorrect services).'));
			return;
		}

		list($token, $secret) = $this->request_token($omb);

		if (!$token || !$secret) {
			$this->show_form(_('Couldn\'t get a request token.'));
			return;
		}

		$this->request_authorization($user, $omb, $token, $secret);
	}

	function get_user() {
		$user = NULL;
		$nickname = $this->trimmed('nickname');
		if ($nickname) {
			$user = User::staticGet('nickname', $nickname);
		}
		return $user;
	}

	function getOmb($xrds) {

	    static $omb_endpoints = array(OMB_ENDPOINT_UPDATEPROFILE, OMB_ENDPOINT_POSTNOTICE);
		static $oauth_endpoints = array(OAUTH_ENDPOINT_REQUEST, OAUTH_ENDPOINT_AUTHORIZE,
										OAUTH_ENDPOINT_ACCESS);
		$omb = array();

		# XXX: the following code could probably be refactored to eliminate dupes

		$oauth_services = omb_get_services($xrds, OAUTH_DISCOVERY);

		if (!$oauth_services) {
			return NULL;
		}

		$oauth_service = $oauth_services[0];

		$oauth_xrd = $this->getXRD($oauth_service, $xrds);

		if (!$oauth_xrd) {
			return NULL;
		}

		if (!$this->addServices($oauth_xrd, $oauth_endpoints, $omb)) {
			return NULL;
		}

		$omb_services = omb_get_services($xrds, OMB_NAMESPACE);

		if (!$omb_services) {
			return NULL;
		}

		$omb_service = $omb_services[0];

		$omb_xrd = $this->getXRD($omb_service, $xrds);

		if (!$omb_xrd) {
			return NULL;
		}

		if (!$this->addServices($omb_xrd, $omb_endpoints, $omb)) {
			return NULL;
		}

		# XXX: check that we got all the services we needed

		foreach (array_merge($omb_endpoints, $oauth_endpoints) as $type) {
			if (!array_key_exists($type, $omb) || !$omb[$type]) {
				return NULL;
			}
		}

		if (!omb_local_id($omb[OAUTH_ENDPOINT_REQUEST])) {
			return NULL;
		}

		return $omb;
	}

	function getXRD($main_service, $main_xrds) {
		$uri = omb_service_uri($main_service);
		if (strpos($uri, "#") !== 0) {
			# FIXME: more rigorous handling of external service definitions
			return NULL;
		}
		$id = substr($uri, 1);
		$nodes = $main_xrds->allXrdNodes;
		$parser = $main_xrds->parser;
		foreach ($nodes as $node) {
			$attrs = $parser->attributes($node);
			if (array_key_exists('xml:id', $attrs) &&
				$attrs['xml:id'] == $id) {
				# XXX: trick the constructor into thinking this is the only node
				$bogus_nodes = array($node);
				return new Auth_Yadis_XRDS($parser, $bogus_nodes);
			}
		}
		return NULL;
	}

	function addServices($xrd, $types, &$omb) {
		foreach ($types as $type) {
			$matches = omb_get_services($xrd, $type);
			if ($matches) {
				$omb[$type] = $matches[0];
			} else {
				# no match for type
				return false;
			}
		}
		return true;
	}

	function request_token($omb) {
		$con = omb_oauth_consumer();

		$url = omb_service_uri($omb[OAUTH_ENDPOINT_REQUEST]);

		# XXX: Is this the right thing to do? Strip off GET params and make them
		# POST params? Seems wrong to me.

		$parsed = parse_url($url);
		$params = array();
		parse_str($parsed['query'], $params);

		$req = OAuthRequest::from_consumer_and_token($con, NULL, "POST", $url, $params);

		$listener = omb_local_id($omb[OAUTH_ENDPOINT_REQUEST]);

		if (!$listener) {
			return NULL;
		}

		$req->set_parameter('omb_listener', $listener);
		$req->set_parameter('omb_version', OMB_VERSION_01);

		# XXX: test to see if endpoint accepts this signature method

		$req->sign_request(omb_hmac_sha1(), $con, NULL);

		# We re-use this tool's fetcher, since it's pretty good

		$fetcher = Auth_Yadis_Yadis::getHTTPFetcher();

		$result = $fetcher->post($req->get_normalized_http_url(),
								 $req->to_postdata());

		if ($result->status != 200) {
			return NULL;
		}

		parse_str($result->body, $return);

		return array($return['oauth_token'], $return['oauth_token_secret']);
	}

	function request_authorization($user, $omb, $token, $secret) {
		global $config; # for license URL

		$con = omb_oauth_consumer();
		$tok = new OAuthToken($token, $secret);

		$url = omb_service_uri($omb[OAUTH_ENDPOINT_AUTHORIZE]);

		# XXX: Is this the right thing to do? Strip off GET params and make them
		# POST params? Seems wrong to me.

		$parsed = parse_url($url);
		$params = array();
		parse_str($parsed['query'], $params);

		$req = OAuthRequest::from_consumer_and_token($con, $tok, 'GET', $url, $params);

		# We send over a ton of information. This lets the other
		# server store info about our user, and it lets the current
		# user decide if they really want to authorize the subscription.

		$req->set_parameter('omb_version', OMB_VERSION_01);
		$req->set_parameter('omb_listener', omb_local_id($omb[OAUTH_ENDPOINT_REQUEST]));
		$req->set_parameter('omb_listenee', $user->uri);
		$req->set_parameter('omb_listenee_profile', common_profile_url($user->nickname));
		$req->set_parameter('omb_listenee_nickname', $user->nickname);
		$req->set_parameter('omb_listenee_license', $config['license']['url']);

		$profile = $user->getProfile();
		if (!$profile) {
			common_log_db_error($user, 'SELECT', __FILE__);
			$this->server_error(_('User without matching profile'));
			return;
		}
		
		if ($profile->fullname) {
			$req->set_parameter('omb_listenee_fullname', $profile->fullname);
		}
		if ($profile->homepage) {
			$req->set_parameter('omb_listenee_homepage', $profile->homepage);
		}
		if ($profile->bio) {
			$req->set_parameter('omb_listenee_bio', $profile->bio);
		}
		if ($profile->location) {
			$req->set_parameter('omb_listenee_location', $profile->location);
		}
		$avatar = $profile->getAvatar(AVATAR_PROFILE_SIZE);
		if ($avatar) {
			$req->set_parameter('omb_listenee_avatar', $avatar->url);
		}

		# XXX: add a nonce to prevent replay attacks

		$req->set_parameter('oauth_callback', common_local_url('finishremotesubscribe'));

		# XXX: test to see if endpoint accepts this signature method

		$req->sign_request(omb_hmac_sha1(), $con, $tok);

		# store all our info here

		$omb['listenee'] = $user->nickname;
		$omb['listener'] = omb_local_id($omb[OAUTH_ENDPOINT_REQUEST]);
		$omb['token'] = $token;
		$omb['secret'] = $secret;
		# call doesn't work after bounce back so we cache; maybe serialization issue...?
		$omb['access_token_url'] = omb_service_uri($omb[OAUTH_ENDPOINT_ACCESS]);
		$omb['post_notice_url'] = omb_service_uri($omb[OMB_ENDPOINT_POSTNOTICE]);
		$omb['update_profile_url'] = omb_service_uri($omb[OMB_ENDPOINT_UPDATEPROFILE]);

		common_ensure_session();
		
		$_SESSION['oauth_authorization_request'] = $omb;

		# Redirect to authorization service

		common_redirect($req->to_url());
		return;
	}

	function make_nonce() {
		return common_good_rand(16);
	}
}
