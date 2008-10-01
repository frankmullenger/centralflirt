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

require_once(INSTALLDIR.'/lib/twitterapi.php');

class TwitapifriendshipsAction extends TwitterapiAction {

	function is_readonly() {

		static $write_methods = array(	'create',
										'destroy');

		$cmdtext = explode('.', $this->arg('method'));

		if (in_array($cmdtext[0], $write_methods)) {
			return false;
		}

		return true;
	}

	function create($args, $apidata) {
		parent::handle($args);

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->client_error(_('This method requires a POST.'), 400, $apidata['content-type']);
			return;
		}

		$id = $apidata['api_arg'];

		$other = $this->get_user($id);

		if (!$other) {
			$this->client_error(_('Could not follow user: User not found.'), 403, $apidata['content-type']);
			return;
		}

		$user = $apidata['user'];

		if ($user->isSubscribed($other)) {
			$errmsg = sprintf(_('Could not follow user: %s is already on your list.'), $other->nickname);
			$this->client_error($errmsg, 403, $apidata['content-type']);
			return;
		}

		$sub = new Subscription();

		$sub->query('BEGIN');

		$sub->subscriber = $user->id;
		$sub->subscribed = $other->id;
		$sub->created = DB_DataObject_Cast::dateTime(); # current time

		$result = $sub->insert();

		if (!$result) {
			$errmsg = sprintf(_('Could not follow user: %s is already on your list.'), $other->nickname);
			$this->client_error($errmsg, 400, $apidata['content-type']);
			return;
		}

		$sub->query('COMMIT');

		mail_subscribe_notify($other, $user);

		$type = $apidata['content-type'];
		$this->init_document($type);
		$this->show_profile($other, $type);
		$this->end_document($type);

	}

	//destroy
	//
	//Discontinues friendship with the user specified in the ID parameter as the authenticating user.  Returns the un-friended user in the requested format when successful.  Returns a string describing the failure condition when unsuccessful.
	//
	//URL: http://twitter.com/friendships/destroy/id.format
	//
	//Formats: xml, json
	//
	//Parameters:
	//
	//* id.  Required.  The ID or screen name of the user with whom to discontinue friendship.  Ex: http://twitter.com/friendships/destroy/12345.json or http://twitter.com/friendships/destroy/bob.xml

	function destroy($args, $apidata) {
		parent::handle($args);

		if (!in_array($_SERVER['REQUEST_METHOD'], array('POST', 'DELETE'))) {
			$this->client_error(_('This method requires a POST or DELETE.'), 400, $apidata['content-type']);
			return;
		}

		$id = $apidata['api_arg'];

		# We can't subscribe to a remote person, but we can unsub

		$other = $this->get_profile($id);
		$user = $apidata['user'];

		$sub = new Subscription();
		$sub->subscriber = $user->id;
		$sub->subscribed = $other->id;

		if ($sub->find(TRUE)) {
			$sub->query('BEGIN');
			$sub->delete();
			$sub->query('COMMIT');
		} else {
			$this->client_error(_('You are not friends with the specified user.'), 403, $apidata['content-type']);
			return;
		}

		$type = $apidata['content-type'];
		$this->init_document($type);
		$this->show_profile($other, $type);
		$this->end_document($type);

	}

	//	Tests if a friendship exists between two users.
	//
	//
	//	  URL: http://twitter.com/friendships/exists.format
	//
	//	Formats: xml, json, none
	//
	//	  Parameters:
	//
	//	    * user_a.  Required.  The ID or screen_name of the first user to test friendship for.
	//	      * user_b.  Required.  The ID or screen_name of the second user to test friendship for.
	//	  * Ex: http://twitter.com/friendships/exists.xml?user_a=alice&user_b=bob

	function exists($args, $apidata) {
		parent::handle($args);

		if (!in_array($apidata['content-type'], array('xml', 'json'))) {
			common_user_error(_('API method not found!'), $code = 404);
			return;
		}

		$user_a_id = $this->trimmed('user_a');
		$user_b_id = $this->trimmed('user_b');

		$user_a = $this->get_user($user_a_id);
		$user_b = $this->get_user($user_b_id);

		if (!$user_a || !$user_b) {
			$this->client_error(_('Two user ids or screen_names must be supplied.'), 400, $apidata['content-type']);
			return;
		}

		if ($user_a->isSubscribed($user_b)) {
			$result = 'true';
		} else {
			$result = 'false';
		}

		switch ($apidata['content-type']) {
		 case 'xml':
			$this->init_document('xml');
			common_element('friends', NULL, $result);
			$this->end_document('xml');
			break;
		 case 'json':
			$this->init_document('json');
			print json_encode($result);
			$this->end_document('json');
			break;
		 default:
			break;
		}

	}

}