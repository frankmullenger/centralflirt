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
define('TIMESTAMP_THRESHOLD', 300);

class UserauthorizationAction extends Action {

	function handle($args) {
		parent::handle($args);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			# CSRF protection
			$token = $this->trimmed('token');
			if (!$token || $token != common_session_token()) {
				$req = $this->get_stored_request();
				$this->show_form(_('There was a problem with your session token. Try again, please.'), $req);
				return;
			}
			# We've shown the form, now post user's choice
			$this->send_authorization();
		} else {
			if (!common_logged_in()) {
				# Go log in, and then come back
				common_debug('saving URL for returnto', __FILE__);
				$argsclone = $_GET;
				unset($argsclone['action']);
				common_set_returnto(common_local_url('userauthorization', $argsclone));
				common_debug('redirecting to login', __FILE__);
				common_redirect(common_local_url('login'));
				return;
			}
			try {
				# this must be a new request
				common_debug('getting new request', __FILE__);
				$req = $this->get_new_request();
				if (!$req) {
					$this->client_error(_('No request found!'));
				}
				common_debug('validating request', __FILE__);
				# XXX: only validate new requests, since nonce is one-time use
				$this->validate_request($req);
				common_debug('showing form', __FILE__);
				$this->store_request($req);
				$this->show_form($req);
			} catch (OAuthException $e) {
				$this->clear_request();
				$this->client_error($e->getMessage());
				return;
			}

		}
	}

	function show_form($req) {

		$nickname = $req->get_parameter('omb_listenee_nickname');
		$profile = $req->get_parameter('omb_listenee_profile');
		$license = $req->get_parameter('omb_listenee_license');
		$fullname = $req->get_parameter('omb_listenee_fullname');
		$homepage = $req->get_parameter('omb_listenee_homepage');
		$bio = $req->get_parameter('omb_listenee_bio');
		$location = $req->get_parameter('omb_listenee_location');
		$avatar = $req->get_parameter('omb_listenee_avatar');

		common_show_header(_('Authorize subscription'));
		common_element('p', NULL, _('Please check these details to make sure '.
									 'that you want to subscribe to this user\'s notices. '.
									 'If you didn\'t just ask to subscribe to someone\'s notices, '.
									 'click "Cancel".'));
		common_element_start('div', 'profile');
		if ($avatar) {
			common_element('img', array('src' => $avatar,
										'class' => 'avatar profile',
										'width' => AVATAR_PROFILE_SIZE,
										'height' => AVATAR_PROFILE_SIZE,
										'alt' => $nickname));
		}
		common_element('a', array('href' => $profile,
								  'class' => 'external profile nickname'),
					   $nickname);
		if ($fullname) {
			common_element_start('div', 'fullname');
			if ($homepage) {
				common_element('a', array('href' => $homepage),
							   $fullname);
			} else {
				common_text($fullname);
			}
			common_element_end('div');
		}
		if ($location) {
			common_element('div', 'location', $location);
		}
		if ($bio) {
			common_element('div', 'bio', $bio);
		}
		common_element_start('div', 'license');
		common_element('a', array('href' => $license,
								  'class' => 'license'),
					   $license);
		common_element_end('div');
		common_element_end('div');
		common_element_start('form', array('method' => 'post',
										   'id' => 'userauthorization',
										   'name' => 'userauthorization',
										   'action' => common_local_url('userauthorization')));
		common_hidden('token', common_session_token());
		common_submit('accept', _('Accept'));
		common_submit('reject', _('Reject'));
		common_element_end('form');
		common_show_footer();
	}

	function send_authorization() {
		$req = $this->get_stored_request();

		if (!$req) {
			common_user_error(_('No authorization request!'));
			return;
		}

		$callback = $req->get_parameter('oauth_callback');

		if ($this->arg('accept')) {
			if (!$this->authorize_token($req)) {
				$this->client_error(_('Error authorizing token'));
			}
			if (!$this->save_remote_profile($req)) {
				$this->client_error(_('Error saving remote profile'));
			}
			if (!$callback) {
				$this->show_accept_message($req->get_parameter('oauth_token'));
			} else {
				$params = array();
				$params['oauth_token'] = $req->get_parameter('oauth_token');
				$params['omb_version'] = OMB_VERSION_01;
				$user = User::staticGet('uri', $req->get_parameter('omb_listener'));
				$profile = $user->getProfile();
				$params['omb_listener_nickname'] = $user->nickname;
				$params['omb_listener_profile'] = common_local_url('showstream',
																   array('nickname' => $user->nickname));
				if ($profile->fullname) {
					$params['omb_listener_fullname'] = $profile->fullname;
				}
				if ($profile->homepage) {
					$params['omb_listener_homepage'] = $profile->homepage;
				}
				if ($profile->bio) {
					$params['omb_listener_bio'] = $profile->bio;
				}
				if ($profile->location) {
					$params['omb_listener_location'] = $profile->location;
				}
				$avatar = $profile->getAvatar(AVATAR_PROFILE_SIZE);
				if ($avatar) {
					$params['omb_listener_avatar'] = $avatar->url;
				}
				$parts = array();
				foreach ($params as $k => $v) {
					$parts[] = $k . '=' . OAuthUtil::urlencodeRFC3986($v);
				}
				$query_string = implode('&', $parts);
				$parsed = parse_url($callback);
				$url = $callback . (($parsed['query']) ? '&' : '?') . $query_string;
				common_redirect($url, 303);
			}
		} else {
			if (!$callback) {
				$this->show_reject_message();
			} else {
				# XXX: not 100% sure how to signal failure... just redirect without token?
				common_redirect($callback, 303);
			}
		}
	}

	function authorize_token(&$req) {
		$consumer_key = $req->get_parameter('oauth_consumer_key');
		$token_field = $req->get_parameter('oauth_token');
		common_debug('consumer key = "'.$consumer_key.'"', __FILE__);
		common_debug('token field = "'.$token_field.'"', __FILE__);
		$rt = new Token();
		$rt->consumer_key = $consumer_key;
		$rt->tok = $token_field;
		$rt->type = 0;
		$rt->state = 0;
		common_debug('request token to look up: "'.print_r($rt,TRUE).'"');
		if ($rt->find(true)) {
			common_debug('found request token to authorize', __FILE__);
			$orig_rt = clone($rt);
			$rt->state = 1; # Authorized but not used
			if ($rt->update($orig_rt)) {
				common_debug('updated request token so it is authorized', __FILE__);
				return true;
			}
		}
		return FALSE;
	}

	# XXX: refactor with similar code in finishremotesubscribe.php

	function save_remote_profile(&$req) {
		# FIXME: we should really do this when the consumer comes
		# back for an access token. If they never do, we've got stuff in a
		# weird state.

		$nickname = $req->get_parameter('omb_listenee_nickname');
		$fullname = $req->get_parameter('omb_listenee_fullname');
		$profile_url = $req->get_parameter('omb_listenee_profile');
		$homepage = $req->get_parameter('omb_listenee_homepage');
		$bio = $req->get_parameter('omb_listenee_bio');
		$location = $req->get_parameter('omb_listenee_location');
		$avatar_url = $req->get_parameter('omb_listenee_avatar');

		$listenee = $req->get_parameter('omb_listenee');
		$remote = Remote_profile::staticGet('uri', $listenee);

		if ($remote) {
			$exists = true;
			$profile = Profile::staticGet($remote->id);
			$orig_remote = clone($remote);
			$orig_profile = clone($profile);
		} else {
			$exists = false;
			$remote = new Remote_profile();
			$remote->uri = $listenee;
			$profile = new Profile();
		}

		$profile->nickname = $nickname;
		$profile->profileurl = $profile_url;

		if ($fullname) {
			$profile->fullname = $fullname;
		}
		if ($homepage) {
			$profile->homepage = $homepage;
		}
		if ($bio) {
			$profile->bio = $bio;
		}
		if ($location) {
			$profile->location = $location;
		}

		if ($exists) {
			$profile->update($orig_profile);
		} else {
			$profile->created = DB_DataObject_Cast::dateTime(); # current time
			$id = $profile->insert();
			if (!$id) {
				return FALSE;
			}
			$remote->id = $id;
		}

		if ($exists) {
			if (!$remote->update($orig_remote)) {
				return FALSE;
			}
		} else {
			$remote->created = DB_DataObject_Cast::dateTime(); # current time
			if (!$remote->insert()) {
				return FALSE;
			}
		}

		if ($avatar_url) {
			if (!$this->add_avatar($profile, $avatar_url)) {
				return FALSE;
			}
		}

		$user = common_current_user();
		$datastore = omb_oauth_datastore();
		$consumer = $this->get_consumer($datastore, $req);
		$token = $this->get_token($datastore, $req, $consumer);

		$sub = new Subscription();
		$sub->subscriber = $user->id;
		$sub->subscribed = $remote->id;
		$sub->token = $token->key; # NOTE: request token, not valid for use!
		$sub->created = DB_DataObject_Cast::dateTime(); # current time

		if (!$sub->insert()) {
			return FALSE;
		}

		return TRUE;
	}

	function add_avatar($profile, $url) {
		$temp_filename = tempnam(sys_get_temp_dir(), 'listenee_avatar');
		copy($url, $temp_filename);
		return $profile->setOriginal($temp_filename);
	}

	function show_accept_message($tok) {
		common_show_header(_('Subscription authorized'));
		common_element('p', NULL,
					   _('The subscription has been authorized, but no '.
						  'callback URL was passed. Check with the site\'s instructions for '.
						  'details on how to authorize the subscription. Your subscription token is:'));
		common_element('blockquote', 'token', $tok);
		common_show_footer();
	}

	function show_reject_message($tok) {
		common_show_header(_('Subscription rejected'));
		common_element('p', NULL,
					   _('The subscription has been rejected, but no '.
						  'callback URL was passed. Check with the site\'s instructions for '.
						  'details on how to fully reject the subscription.'));
		common_show_footer();
	}

	function store_request($req) {
		common_ensure_session();
		$_SESSION['userauthorizationrequest'] = $req;
	}

	function clear_request() {
		common_ensure_session();
		unset($_SESSION['userauthorizationrequest']);
	}

	function get_stored_request() {
		common_ensure_session();
		$req = $_SESSION['userauthorizationrequest'];
		return $req;
	}

	function get_new_request() {
		$req = OAuthRequest::from_request();
		return $req;
	}

	# Throws an OAuthException if anything goes wrong

	function validate_request(&$req) {
		# OAuth stuff -- have to copy from OAuth.php since they're
		# all private methods, and there's no user-authentication method
		common_debug('checking version', __FILE__);
		$this->check_version($req);
		common_debug('getting datastore', __FILE__);
		$datastore = omb_oauth_datastore();
		common_debug('getting consumer', __FILE__);
		$consumer = $this->get_consumer($datastore, $req);
		common_debug('getting token', __FILE__);
		$token = $this->get_token($datastore, $req, $consumer);
		common_debug('checking timestamp', __FILE__);
		$this->check_timestamp($req);
		common_debug('checking nonce', __FILE__);
		$this->check_nonce($datastore, $req, $consumer, $token);
		common_debug('checking signature', __FILE__);
		$this->check_signature($req, $consumer, $token);
		common_debug('validating omb stuff', __FILE__);
		$this->validate_omb($req);
		common_debug('done validating', __FILE__);
		return true;
	}

	function validate_omb(&$req) {
		foreach (array('omb_version', 'omb_listener', 'omb_listenee',
					   'omb_listenee_profile', 'omb_listenee_nickname',
					   'omb_listenee_license') as $param)
		{
			if (!$req->get_parameter($param)) {
				throw new OAuthException("Required parameter '$param' not found");
			}
		}
		# Now, OMB stuff
		$version = $req->get_parameter('omb_version');
		if ($version != OMB_VERSION_01) {
			throw new OAuthException("OpenMicroBlogging version '$version' not supported");
		}
		$listener =	$req->get_parameter('omb_listener');
		$user = User::staticGet('uri', $listener);
		if (!$user) {
			throw new OAuthException("Listener URI '$listener' not found here");
		}
		$cur = common_current_user();
		if ($cur->id != $user->id) {
			throw new OAuthException("Can't add for another user!");
		}
		$listenee = $req->get_parameter('omb_listenee');
		if (!Validate::uri($listenee) &&
			!common_valid_tag($listenee)) {
			throw new OAuthException("Listenee URI '$listenee' not a recognizable URI");
		}
		if (strlen($listenee) > 255) {
			throw new OAuthException("Listenee URI '$listenee' too long");
		}
		$remote = Remote_profile::staticGet('uri', $listenee);
		if ($remote) {
			$sub = new Subscription();
			$sub->subscriber = $user->id;
			$sub->subscribed = $remote->id;
			if ($sub->find(TRUE)) {
				throw new OAuthException("Already subscribed to user!");
			}
		}
		$nickname = $req->get_parameter('omb_listenee_nickname');
		if (!Validate::string($nickname, array('min_length' => 1,
											   'max_length' => 64,
											   'format' => VALIDATE_NUM . VALIDATE_ALPHA_LOWER))) {
			throw new OAuthException('Nickname must have only letters and numbers and no spaces.');
		}
		$profile = $req->get_parameter('omb_listenee_profile');
		if (!common_valid_http_url($profile)) {
			throw new OAuthException("Invalid profile URL '$profile'.");
		}
		$license = $req->get_parameter('omb_listenee_license');
		if (!common_valid_http_url($license)) {
			throw new OAuthException("Invalid license URL '$license'.");
		}
		# optional stuff
		$fullname = $req->get_parameter('omb_listenee_fullname');
		if ($fullname && strlen($fullname) > 255) {
			throw new OAuthException("Full name '$fullname' too long.");
		}
		$homepage = $req->get_parameter('omb_listenee_homepage');
		if ($homepage && (!common_valid_http_url($homepage) || strlen($homepage) > 255)) {
			throw new OAuthException("Invalid homepage '$homepage'");
		}
		$bio = $req->get_parameter('omb_listenee_bio');
		if ($bio && strlen($bio) > 140) {
			throw new OAuthException("Bio too long '$bio'");
		}
		$location = $req->get_parameter('omb_listenee_location');
		if ($location && strlen($location) > 255) {
			throw new OAuthException("Location too long '$location'");
		}
		$avatar = $req->get_parameter('omb_listenee_avatar');
		if ($avatar) {
			if (!common_valid_http_url($avatar) || strlen($avatar) > 255) {
				throw new OAuthException("Invalid avatar URL '$avatar'");
			}
			$size = @getimagesize($avatar);
			if (!$size) {
				throw new OAuthException("Can't read avatar URL '$avatar'");
			}
			if ($size[0] != AVATAR_PROFILE_SIZE || $size[1] != AVATAR_PROFILE_SIZE) {
				throw new OAuthException("Wrong size image at '$avatar'");
			}
			if (!in_array($size[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG,
										  IMAGETYPE_PNG))) {
				throw new OAuthException("Wrong image type for '$avatar'");
			}
		}
		$callback = $req->get_parameter('oauth_callback');
		if ($callback && !common_valid_http_url($callback)) {
			throw new OAuthException("Invalid callback URL '$callback'");
		}
	}

	# Snagged from OAuthServer

	function check_version(&$req) {
		$version = $req->get_parameter("oauth_version");
		if (!$version) {
			$version = 1.0;
		}
		if ($version != 1.0) {
			throw new OAuthException("OAuth version '$version' not supported");
		}
		return $version;
	}

	# Snagged from OAuthServer

	function get_consumer($datastore, $req) {
		$consumer_key = @$req->get_parameter("oauth_consumer_key");
		if (!$consumer_key) {
			throw new OAuthException("Invalid consumer key");
		}

		$consumer = $datastore->lookup_consumer($consumer_key);
		if (!$consumer) {
			throw new OAuthException("Invalid consumer");
		}
		return $consumer;
	}

	# Mostly cadged from OAuthServer

	function get_token($datastore, &$req, $consumer) {/*{{{*/
		$token_field = @$req->get_parameter('oauth_token');
		$token = $datastore->lookup_token($consumer, 'request', $token_field);
		if (!$token) {
			throw new OAuthException("Invalid $token_type token: $token_field");
		}
		return $token;
	}

	function check_timestamp(&$req) {
		$timestamp = @$req->get_parameter('oauth_timestamp');
		$now = time();
		if ($now - $timestamp > TIMESTAMP_THRESHOLD) {
			throw new OAuthException("Expired timestamp, yours $timestamp, ours $now");
		}
	}

	# NOTE: don't call twice on the same request; will fail!
	function check_nonce(&$datastore, &$req, $consumer, $token) {
		$timestamp = @$req->get_parameter('oauth_timestamp');
		$nonce = @$req->get_parameter('oauth_nonce');
		$found = $datastore->lookup_nonce($consumer, $token, $nonce, $timestamp);
		if ($found) {
			throw new OAuthException("Nonce already used");
		}
		return true;
	}

	function check_signature(&$req, $consumer, $token) {
		$signature_method = $this->get_signature_method($req);
		$signature = $req->get_parameter('oauth_signature');
		$valid_sig = $signature_method->check_signature($req,
														$consumer,
														$token,
														$signature);
		if (!$valid_sig) {
			throw new OAuthException("Invalid signature");
		}
	}

	function get_signature_method(&$req) {
		$signature_method = @$req->get_parameter("oauth_signature_method");
		if (!$signature_method) {
			$signature_method = "PLAINTEXT";
		}
		if ($signature_method != 'HMAC-SHA1') {
			throw new OAuthException("Signature method '$signature_method' not supported.");
		}
		return omb_hmac_sha1();
	}
}
