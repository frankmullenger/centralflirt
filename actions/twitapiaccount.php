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

class TwitapiaccountAction extends TwitterapiAction {

	function verify_credentials($args, $apidata) {

		if ($apidata['content-type'] == 'xml') {
			header('Content-Type: application/xml; charset=utf-8');		
			print '<authorized>true</authorized>';
		} elseif ($apidata['content-type'] == 'json') {
			header('Content-Type: application/json; charset=utf-8');		
			print '{"authorized":true}';
		} else {
			common_user_error("API method not found!", $code=404);
		}
			
		exit();
	}
	
	function end_session($args, $apidata) {
		parent::handle($args);
		common_server_error("API method under construction.", $code=501);
		exit();
	}
	
	function update_location($args, $apidata) {
		parent::handle($args);
		common_server_error("API method under construction.", $code=501);
		exit();
	}
	
	function update_delivery_device($args, $apidata) {
		parent::handle($args);
		common_server_error("API method under construction.", $code=501);
		exit();
	}
	
	function rate_limit_status($args, $apidata) {
		parent::handle($args);
		common_server_error("API method under construction.", $code=501);
		exit();
	}
}