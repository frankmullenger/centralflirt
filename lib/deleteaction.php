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

class DeleteAction extends Action {

    function handle($args) {
        parent::handle($args);
        $user = common_current_user();
        $notice_id = $this->trimmed('notice');
        $notice = Notice::staticGet($notice_id);
        if (!$notice) {
            common_user_error(_('No such notice.'));
            exit;
        }

        $profile = $notice->getProfile();
        $user_profile = $user->getProfile();

        if (!common_logged_in()) {
            common_user_error(_('Not logged in.'));
            exit;
        } else if ($notice->profile_id != $user_profile->id) {
            common_user_error(_('Can\'t delete this notice.'));
            exit;
        }
    }

    function show_top($arr=NULL) {
        $instr = $this->get_instructions();
        $output = common_markup_to_html($instr);
        common_element_start('div', 'instructions');
        common_raw($output);
        common_element_end('div');
    }

    function get_title() {
        return NULL;
    }

    function show_header() {
        return;
    }
}
