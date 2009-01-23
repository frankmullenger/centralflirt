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

require_once INSTALLDIR.'/extlib/facebook/facebook.php';
require_once INSTALLDIR.'/lib/facebookaction.php';
require_once INSTALLDIR.'/lib/noticelist.php';

define("FACEBOOK_SERVICE", 2); // Facebook is foreign_service ID 2
define("FACEBOOK_NOTICE_PREFIX", 1);
define("FACEBOOK_PROMPTED_UPDATE_PREF", 2);

// Gets all the notices from users with a Facebook link since a given ID
function getFacebookNotices($since)
{
    $qry = 'SELECT notice.* ' .
        'FROM notice ' .
        'JOIN foreign_link ' .
        'WHERE notice.profile_id = foreign_link.user_id ' .
        'AND foreign_link.service = 2';

    // XXX: What should the limit be?
    return Notice::getStreamDirect($qry, 0, 100, 0, 0, null, $since);
}

function getFacebook()
{
    $apikey = common_config('facebook', 'apikey');
    $secret = common_config('facebook', 'secret');
    return new Facebook($apikey, $secret);
}

function startFBML($indent = true)
{
    global $xw;
    $xw = new XMLWriter();
    $xw->openURI('php://output');
    $xw->setIndent($indent);
}

function getFacebookBaseCSS()
{
    # Add a timestamp to the CSS file so Facebook cache wont ignore our changes
    $ts = filemtime(INSTALLDIR.'/theme/base/css/facebookapp.base.css');
    $cssurl = theme_path('css/facebookapp.base.css', 'base') . "?ts=$ts";
    return $cssurl;
}

function getFacebookThemeCSS() 
{
    # Add a timestamp to the CSS file so Facebook cache wont ignore our changes
    $ts = filemtime(theme_file('css/facebookapp.theme.css'));
    $cssurl = theme_path('css/facebookapp.theme.css') . "?ts=$ts";
    return $cssurl;   
}

function getFacebookJS() {

    # Add a timestamp to the FBJS file so Facebook cache wont ignore our changes
    $ts = filemtime(INSTALLDIR.'/js/facebookapp.js');
    $jsurl = common_path('js/facebookapp.js') . "?ts=$ts";
    return $jsurl;
}

function updateProfileBox($facebook, $flink, $notice) {
    $fbaction = new FacebookAction($output='php://output', $indent=true, $facebook, $flink);
    $fbaction->updateProfileBox($notice);
}

