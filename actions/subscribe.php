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

class SubscribeAction extends Action {
	function handle($args) {
		parent::handle($args);

		if (!common_logged_in()) {
			common_user_error(_('Not logged in.'));
			return;
		}

		$user = common_current_user();

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			common_redirect(common_local_url('subscriptions', array('nickname' => $user->nickname)));
			return;
		}

		$other_nickname = $this->arg('subscribeto');

		$other = User::staticGet('nickname', $other_nickname);

		if (!$other) {
			common_user_error(_('No such user.'));
			return;
		}

		if ($user->isSubscribed($other)) {
			common_user_error(_('Already subscribed!.'));
			return;
		}

		$sub = new Subscription();
		$sub->subscriber = $user->id;
		$sub->subscribed = $other->id;

		$sub->created = DB_DataObject_Cast::dateTime(); # current time

		if (!$sub->insert()) {
			common_server_error(_('Couldn\'t create subscription.'));
			return;
		}

		$this->notify($other, $user);

		common_redirect(common_local_url('subscriptions', array('nickname' =>
																$user->nickname)));
	}

	function notify($listenee, $listener) {
		# XXX: add other notifications (Jabber, SMS) here
		# XXX: queue this and handle it offline
		# XXX: Whatever happens, do it in Twitter-like API, too
		$this->notify_email($listenee, $listener);
	}

	function notify_email($listenee, $listener) {
		mail_subscribe_notify($listenee, $listener);
	}
}