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

require_once(INSTALLDIR.'/lib/rssaction.php');

// Formatting of RSS handled by Rss10Action

class TagrssAction extends Rss10Action {

	function init() {
		$tag = $this->trimmed('tag');

        if (!isset($tag) || mb_strlen($tag) == 0) {
			common_user_error(_('No tag.'));
			return false;
        }

		$this->tag = $tag;
        return true;
	}

	function get_notices($limit=0) {
		$tag = $this->tag;

		$notice = Notice_tag::getStream($tag, 0, ($limit == 0) ? NOTICES_PER_PAGE : $limit);

		while ($notice->fetch()) {
			$notices[] = clone($notice);
		}

		return $notices;
	}

	function get_channel() {
		$tag = $this->tag;

		$c = array('url' => common_local_url('tagrss', array('tag' => $tag)),
			   'title' => $tag,
			   'link' => common_local_url('tagrss', array('tag' => $tag)),
			   'description' => sprintf(_('Microblog tagged with %s'), $tag));
		return $c;
	}
}
