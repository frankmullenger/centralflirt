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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.	 If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

require_once(INSTALLDIR.'/lib/stream.php');

class FavoritedAction extends StreamAction {

	function handle($args) {
		parent::handle($args);

		$page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

		common_show_header(_('Popular notices'),
						   array($this, 'show_header'), NULL,
						   array($this, 'show_top'));

		$this->show_notices($page);

		common_show_footer();
	}

	function show_top() {
		$instr = $this->get_instructions();
		$output = common_markup_to_html($instr);
		common_element_start('div', 'instructions');
		common_raw($output);
		common_element_end('div');
		$this->public_views_menu();
	}

	function show_header() {
        return;
	}

	function get_instructions() {
		return _('Showing recently popular notices');
	}

	function show_notices($page) {

		// XXX: Make dropoff configurable like tags?

		$qry =
			'SELECT notice_id, sum(exp(-(now() - modified)/864000)) as weight ' .
			'FROM fave GROUP BY notice_id ' .
			'ORDER BY weight DESC';

		$offset = ($page - 1) * NOTICES_PER_PAGE;
		$limit = NOTICES_PER_PAGE + 1;

		if (common_config('db','type') == 'pgsql') {
			$qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
		} else {
			$qry .= ' LIMIT ' . $offset . ', ' . $limit;
		}

		// XXX: Figure out how to cache these queries.

		$fave = new Fave;
		$fave->query($qry);

		$notice_list = array();

		while ($fave->fetch()) {
		  array_push($notice_list, $fave->notice_id);
		}

		$notice = new Notice();

		$notice->query(sprintf('SELECT * FROM notice WHERE id in (%s)',
			implode(',', $notice_list)));

        $cnt = $this->show_notice_list($notice);

		common_pagination($page > 1, $cnt > NOTICES_PER_PAGE,
						  $page, 'favorited');
	}
}
