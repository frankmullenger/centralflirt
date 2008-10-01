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

class TwitterapiAction extends Action {

	function handle($args) {
		parent::handle($args);
	}

	function twitter_user_array($profile, $get_notice=false) {

		$twitter_user = array();

		$twitter_user['name'] = $profile->getBestName();
		$twitter_user['followers_count'] = $this->count_subscriptions($profile);
		$twitter_user['screen_name'] = $profile->nickname;
		$twitter_user['description'] = ($profile->bio) ? $profile->bio : NULL;
		$twitter_user['location'] = ($profile->location) ? $profile->location : NULL;
		$twitter_user['id'] = intval($profile->id);

		$avatar = $profile->getAvatar(AVATAR_STREAM_SIZE);

		$twitter_user['profile_image_url'] = ($avatar) ? common_avatar_display_url($avatar) : common_default_avatar(AVATAR_STREAM_SIZE);
		$twitter_user['protected'] = 'false'; # not supported by Laconica yet
		$twitter_user['url'] = ($profile->homepage) ? $profile->homepage : NULL;

		if ($get_notice) {
			$notice = $profile->getCurrentNotice();
			if ($notice) {
				# don't get user!
				$twitter_user['status'] = $this->twitter_status_array($notice, false);
			}
		}

		return $twitter_user;
	}

	function twitter_status_array($notice, $get_user=true) {

		$twitter_status = array();

		$twitter_status['text'] = $notice->content;
		$twitter_status['truncated'] = 'false'; # Not possible on Laconica
		$twitter_status['created_at'] = $this->date_twitter($notice->created);
		$twitter_status['in_reply_to_status_id'] = ($notice->reply_to) ? intval($notice->reply_to) : NULL;
		$twitter_status['source'] = $this->source_link($notice->source);
		$twitter_status['id'] = intval($notice->id);
		$twitter_status['in_reply_to_user_id'] = ($notice->reply_to) ? $this->replier_by_reply(intval($notice->reply_to)) : NULL;
		$twitter_status['favorited'] = NULL; # XXX: Not implemented on Laconica yet.

		if ($get_user) {
			$profile = $notice->getProfile();
			# Don't get notice (recursive!)
			$twitter_user = $this->twitter_user_array($profile, false);
			$twitter_status['user'] = $twitter_user;
		}

		return $twitter_status;
	}

	function twitter_rss_entry_array($notice) {

		$profile = $notice->getProfile();

		$server = common_config('site', 'server');
		$entry = array();

		$entry['content'] = $profile->nickname . ': ' . $notice->content;
		$entry['title'] = $entry['content'];
		$entry['link'] = common_local_url('shownotice', array('notice' => $notice->id));
		$entry['published'] = common_date_iso8601($notice->created);
		$entry['id'] = "tag:$server,2008:$entry[link]";
		$entry['updated'] = $entry['published'];

		# RSS Item specific
		$entry['description'] = $entry['content'];
		$entry['pubDate'] = common_date_rfc2822($notice->created);
		$entry['guid'] = $entry['link'];

		return $entry;
	}

	function twitter_rss_dmsg_array($message) {

		$server = common_config('site', 'server');
		$entry = array();

		$entry['title'] = sprintf('Message from %s to %s',
			$message->getFrom()->nickname, $message->getTo()->nickname);

		$entry['content'] = $message->content;
		$entry['link'] = $message->uri;
		$entry['published'] = common_date_iso8601($message->created);
		$entry['id'] = "tag:$server,2008:$entry[link]";
		$entry['updated'] = $entry['published'];

		# RSS Item specific
		$entry['description'] = $message->content;
		$entry['pubDate'] = common_date_rfc2822($message->created);
		$entry['guid'] = $entry['link'];

		return $entry;
	}

	function twitter_dmsg_array($message) {

		$twitter_dm = array();

		$from_profile = $message->getFrom();
		$to_profile = $message->getTo();

		$twitter_dm['id'] = $message->id;
		$twitter_dm['sender_id'] = $message->from_profile;
		$twitter_dm['text'] = $message->content;
		$twitter_dm['recipient_id'] = $message->to_profile;
		$twitter_dm['created_at'] = $this->date_twitter($message->created);
		$twitter_dm['sender_screen_name'] = $from_profile->nickname;
		$twitter_dm['recipient_screen_name'] = $to_profile->nickname;
		$twitter_dm['sender'] = $this->twitter_user_array($from_profile, false);
		$twitter_dm['recipient'] = $this->twitter_user_array($to_profile, false);

		return $twitter_dm;
	}

	function show_twitter_xml_status($twitter_status) {
		common_element_start('status');
		foreach($twitter_status as $element => $value) {
			if ($element == 'user') {
				$this->show_twitter_xml_user($twitter_status['user']);
			} else {
				common_element($element, NULL, $value);
			}
		}
		common_element_end('status');
	}

	function show_twitter_xml_user($twitter_user, $role='user') {
		common_element_start($role);
		foreach($twitter_user as $element => $value) {
			if ($element == 'status') {
				$this->show_twitter_xml_status($twitter_user['status']);
			} else {
				common_element($element, NULL, $value);
			}
		}
		common_element_end($role);
	}

	function show_twitter_rss_item($entry) {
		common_element_start('item');
		common_element('title', NULL, $entry['title']);
		common_element('description', NULL, $entry['description']);
		common_element('pubDate', NULL, $entry['pubDate']);
		common_element('guid', NULL, $entry['guid']);
		common_element('link', NULL, $entry['link']);
		common_element_end('item');
	}

	function show_twitter_atom_entry($entry) {
	    common_element_start('entry');
		common_element('title', NULL, $entry['title']);
		common_element('content', array('type' => 'html'), $entry['title']);
		common_element('id', NULL, $entry['id']);
		common_element('published', NULL, $entry['published']);
		common_element('updated', NULL, $entry['updated']);
		common_element('link', array('href' => $entry['link'], 'rel' => 'alternate', 'type' => 'text/html'), NULL);
		common_element_end('entry');
	}

	function show_json_objects($objects) {
		print(json_encode($objects));
	}

	function show_single_xml_status($notice) {
		$this->init_document('xml');
		$twitter_status = $this->twitter_status_array($notice);
		$this->show_twitter_xml_status($twitter_status);
		$this->end_document('xml');
	}

	function show_single_json_status($notice) {
		$this->init_document('json');
		$status = $this->twitter_status_array($notice);
		$this->show_json_objects($status);
		$this->end_document('json');
	}

	function show_single_xml_dmsg($message) {
		$this->init_document('xml');
		$dmsg = $this->twitter_dmsg_array($message);
		$this->show_twitter_xml_dmsg($dmsg);
		$this->end_document('xml');
	}

	function show_single_json_dmsg($message) {
		$this->init_document('json');
		$dmsg = $this->twitter_dmsg_array($message);
		$this->show_twitter_json_dm($dmsg);
		$this->end_document('json');
	}

	function show_twitter_xml_dmsg($twitter_dm) {
		common_element_start('direct_message');
		foreach($twitter_dm as $element => $value) {
			if ($element == 'sender' || $element == 'recipient') {
				$this->show_twitter_xml_user($value, $element);
			} else {
				common_element($element, NULL, $value);
			}
		}
		common_element_end('direct_message');
	}

	function show_xml_timeline($notice) {

		$this->init_document('xml');
		common_element_start('statuses', array('type' => 'array'));

		if (is_array($notice)) {
			foreach ($notice as $n) {
				$twitter_status = $this->twitter_status_array($n);
				$this->show_twitter_xml_status($twitter_status);
			}
		} else {
			while ($notice->fetch()) {
				$twitter_status = $this->twitter_status_array($notice);
				$this->show_twitter_xml_status($twitter_status);
			}
		}

		common_element_end('statuses');
		$this->end_document('xml');
	}

	function show_rss_timeline($notice, $title, $link, $subtitle) {

		$this->init_document('rss');

		common_element_start('channel');
		common_element('title', NULL, $title);
		common_element('link', NULL, $link);
		common_element('description', NULL, $subtitle);
		common_element('language', NULL, 'en-us');
		common_element('ttl', NULL, '40');


		if (is_array($notice)) {
			foreach ($notice as $n) {
				$entry = $this->twitter_rss_entry_array($n);
				$this->show_twitter_rss_item($entry);
			}
		} else {
			while ($notice->fetch()) {
				$entry = $this->twitter_rss_entry_array($notice);
				$this->show_twitter_rss_item($entry);
			}
		}

		common_element_end('channel');
		$this->end_twitter_rss();
	}

	function show_atom_timeline($notice, $title, $id, $link, $subtitle=NULL) {

		$this->init_document('atom');

		common_element('title', NULL, $title);
		common_element('id', NULL, $id);
		common_element('link', array('href' => $link, 'rel' => 'alternate', 'type' => 'text/html'), NULL);
		common_element('subtitle', NULL, $subtitle);

		if (is_array($notice)) {
			foreach ($notice as $n) {
				$entry = $this->twitter_rss_entry_array($n);
				$this->show_twitter_atom_entry($entry);
			}
		} else {
			while ($notice->fetch()) {
				$entry = $this->twitter_rss_entry_array($notice);
				$this->show_twitter_atom_entry($entry);
			}
		}

		$this->end_document('atom');

	}

	function show_json_timeline($notice) {

		$this->init_document('json');

		$statuses = array();

		if (is_array($notice)) {
			foreach ($notice as $n) {
				$twitter_status = $this->twitter_status_array($n);
				array_push($statuses, $twitter_status);
			}
		} else {
			while ($notice->fetch()) {
				$twitter_status = $this->twitter_status_array($notice);
				array_push($statuses, $twitter_status);
			}
		}

		$this->show_json_objects($statuses);

		$this->end_document('json');
	}

	// Anyone know what date format this is?
	// Twitter's dates look like this: "Mon Jul 14 23:52:38 +0000 2008" -- Zach
	function date_twitter($dt) {
		$t = strtotime($dt);
		return date("D M d G:i:s O Y", $t);
	}

	function replier_by_reply($reply_id) {
		$notice = Notice::staticGet($reply_id);
		if ($notice) {
			$profile = $notice->getProfile();
			if ($profile) {
				return intval($profile->id);
			} else {
				common_debug('Can\'t find a profile for notice: ' . $notice->id, __FILE__);
			}
		} else {
			common_debug("Can't get notice: $reply_id", __FILE__);
		}
		return NULL;
	}

	// XXX: Candidate for a general utility method somewhere?
	function count_subscriptions($profile) {

		$count = 0;
		$sub = new Subscription();
		$sub->subscribed = $profile->id;

		$count = $sub->find();

		if ($count > 0) {
			return $count - 1;
		} else {
			return 0;
		}
	}

	function init_document($type='xml') {
		switch ($type) {
		 case 'xml':
			header('Content-Type: application/xml; charset=utf-8');
			common_start_xml();
			break;
		 case 'json':
			header('Content-Type: application/json; charset=utf-8');

			// Check for JSONP callback
			$callback = $this->arg('callback');
			if ($callback) {
				print $callback . '(';
			}
			break;
		 case 'rss':
			header("Content-Type: application/rss+xml; charset=utf-8");
			$this->init_twitter_rss();
			break;
		 case 'atom':
			header('Content-Type: application/atom+xml; charset=utf-8');
			$this->init_twitter_atom();
			break;
		 default:
			$this->client_error(_('Not a supported data format.'));
			break;
		}

		return;
	}

	function end_document($type='xml') {
		switch ($type) {
		 case 'xml':
			common_end_xml();
			break;
		 case 'json':

			// Check for JSONP callback
			$callback = $this->arg('callback');
			if ($callback) {
				print ')';
			}
			break;
		 case 'rss':
			$this->end_twitter_rss();
			break;
		 case 'atom':
			$this->end_twitter_rss();
			break;
		 default:
			$this->client_error(_('Not a supported data format.'));
			break;
		}
		return;
	}

	function client_error($msg, $code = 400, $content_type = 'json') {

		static $status = array(400 => 'Bad Request',
							   401 => 'Unauthorized',
							   402 => 'Payment Required',
							   403 => 'Forbidden',
							   404 => 'Not Found',
							   405 => 'Method Not Allowed',
							   406 => 'Not Acceptable',
							   407 => 'Proxy Authentication Required',
							   408 => 'Request Timeout',
							   409 => 'Conflict',
							   410 => 'Gone',
							   411 => 'Length Required',
							   412 => 'Precondition Failed',
							   413 => 'Request Entity Too Large',
							   414 => 'Request-URI Too Long',
							   415 => 'Unsupported Media Type',
							   416 => 'Requested Range Not Satisfiable',
							   417 => 'Expectation Failed');

		$action = $this->trimmed('action');

		common_debug("User error '$code' on '$action': $msg", __FILE__);

		if (!array_key_exists($code, $status)) {
			$code = 400;
		}

		$status_string = $status[$code];
		header('HTTP/1.1 '.$code.' '.$status_string);

		if ($content_type == 'xml') {
			$this->init_document('xml');
			common_element_start('hash');
			common_element('error', NULL, $msg);
			common_element('request', NULL, $_SERVER['REQUEST_URI']);
			common_element_end('hash');
			$this->end_document('xml');
		} else {
			$this->init_document('json');
			$error_array = array('error' => $msg, 'request' => $_SERVER['REQUEST_URI']);
			print(json_encode($error_array));
			$this->end_document('json');
		}

	}

	function init_twitter_rss() {
		common_start_xml();
		common_element_start('rss', array('version' => '2.0'));
	}

	function end_twitter_rss() {
		common_element_end('rss');
		common_end_xml();
	}

	function init_twitter_atom() {
		common_start_xml();
		common_element_start('feed', array('xmlns' => 'http://www.w3.org/2005/Atom', 'xml:lang' => 'en-US'));
	}

	function end_twitter_atom() {
		common_end_xml();
		common_element_end('feed');
	}

	function show_profile($profile, $content_type='xml', $notice=NULL) {
		$profile_array = $this->twitter_user_array($profile, true);
		switch ($content_type) {
		 case 'xml':
			$this->show_twitter_xml_user($profile_array);
			break;
		 case 'json':
			$this->show_json_objects($profile_array);
			break;
		 default:
			$this->client_error(_('Not a supported data format.'));
			return;
		}
		return;
	}

	function get_user($id) {
		if (is_numeric($id)) {
			return User::staticGet($id);
		} else {
			return User::staticGet('nickname', $id);
		}
	}

	function get_profile($id) {
		if (is_numeric($id)) {
			return Profile::staticGet($id);
		} else {
			$user = User::staticGet('nickname', $id);
			if ($user) {
				return $user->getProfile();
			} else {
				return NULL;
			}
		}
	}

	function source_link($source) {
		$source_name = _($source);
		switch ($source) {
		 case 'web':
		 case 'xmpp':
		 case 'mail':
		 case 'omb':
		 case 'api':
			break;
		 default:
			$ns = Notice_source::staticGet($source);
			if ($ns) {
				$source_name = '<a href="' . $ns->url . '">' . $ns->name . '</a>';
			}
			break;
		}
		return $source_name;
	}

}