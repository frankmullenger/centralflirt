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

class ConfirmaddressAction extends Action {

    function handle($args) {
        parent::handle($args);
        if (!common_logged_in()) {
            common_set_returnto($this->self_url());
            common_redirect(common_local_url('login'));
            return;
        }
        $code = $this->trimmed('code');
        if (!$code) {
            $this->client_error(_('No confirmation code.'));
            return;
        }
        $confirm = Confirm_address::staticGet('code', $code);
        if (!$confirm) {
            $this->client_error(_('Confirmation code not found.'));
            return;
        }
        $cur = common_current_user();
        if ($cur->id != $confirm->user_id) {
            $this->client_error(_('That confirmation code is not for you!'));
            return;
        }
		$type = $confirm->address_type;
		if (!in_array($type, array('email', 'jabber', 'sms'))) {
			$this->server_error(sprintf(_('Unrecognized address type %s'), $type));
			return;
		}
        if ($cur->$type == $confirm->address) {
            $this->client_error(_('That address has already been confirmed.'));
			return;
		}

        $cur->query('BEGIN');

        $orig_user = clone($cur);

		$cur->$type = $confirm->address;

		if ($type == 'sms') {
			$cur->carrier = ($confirm->address_extra)+0;
			$carrier = Sms_carrier::staticGet($cur->carrier);
			$cur->smsemail = $carrier->toEmailAddress($cur->sms);
		}

		$result = $cur->updateKeys($orig_user);

        if (!$result) {
			common_log_db_error($cur, 'UPDATE', __FILE__);
            $this->server_error(_('Couldn\'t update user.'));
            return;
        }

        $result = $confirm->delete();

        if (!$result) {
			common_log_db_error($confirm, 'DELETE', __FILE__);
            $this->server_error(_('Couldn\'t delete email confirmation.'));
            return;
        }

        $cur->query('COMMIT');

        common_show_header(_('Confirm Address'));
        common_element('p', NULL,
                       sprintf(_('The address "%s" has been confirmed for your account.'), $cur->$type));
        common_show_footer();
    }
}
