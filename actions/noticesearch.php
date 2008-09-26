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

require_once(INSTALLDIR.'/lib/searchaction.php');
define('NOTICES_PER_PAGE', 20);

# XXX common parent for people and content search?

class NoticesearchAction extends SearchAction {

	function get_instructions() {
		return _('Search for notices on %%site.name%% by their contents. Separate search terms by spaces; they must be 3 characters or more.');
	}

	function get_title() {
		return _('Text search');
	}

	function show_results($q, $page) {

		$notice = new Notice();

		# lcase it for comparison
		$q = strtolower($q);

		if(common_config('db','type')=='mysql') {
			$notice->whereAdd('MATCH(content) against (\''.addslashes($q).'\')');
		} else {
			$notice->whereAdd('to_tsvector(\'english\', content) @@ plainto_tsquery(\''.addslashes($q).'\')');
		}

		# Ask for an extra to see if there's more.

		$notice->limit((($page-1)*NOTICES_PER_PAGE), NOTICES_PER_PAGE + 1);

		$cnt = $notice->find();

		if ($cnt > 0) {
			$terms = preg_split('/[\s,]+/', $q);
			common_element_start('ul', array('id' => 'notices'));
			for ($i = 0; $i < min($cnt, NOTICES_PER_PAGE); $i++) {
				if ($notice->fetch()) {
					$this->show_notice($notice, $terms);
				} else {
					// shouldn't happen!
					break;
				}
			}
			common_element_end('ul');
		} else {
			common_element('p', 'error', _('No results'));
		}

		common_pagination($page > 1, $cnt > NOTICES_PER_PAGE,
						  $page, 'noticesearch', array('q' => $q));
	}

	function show_header($arr) {
		if ($arr) {
			$q = $arr[0];
		}
		if ($q) {
			common_element('link', array('rel' => 'alternate',
										 'href' => common_local_url('noticesearchrss',
																	array('q' => $q)),
										 'type' => 'application/rss+xml',
										 'title' => _('Search Stream Feed')));
		}
	}

	# XXX: refactor and combine with StreamAction::show_notice()

	function show_notice($notice, $terms) {
		$profile = $notice->getProfile();
		if (!$profile) {
			common_log_db_error($notice, 'SELECT', __FILE__);
			$this->server_error(_('Notice without matching profile'));
			return;
		}
		# XXX: RDFa
		common_element_start('li', array('class' => 'notice_single',
										  'id' => 'notice-' . $notice->id));
		$avatar = $profile->getAvatar(AVATAR_STREAM_SIZE);
		common_element_start('a', array('href' => $profile->profileurl));
		common_element('img', array('src' => ($avatar) ? common_avatar_display_url($avatar) : common_default_avatar(AVATAR_STREAM_SIZE),
									'class' => 'avatar stream',
									'width' => AVATAR_STREAM_SIZE,
									'height' => AVATAR_STREAM_SIZE,
									'alt' =>
									($profile->fullname) ? $profile->fullname :
									$profile->nickname));
		common_element_end('a');
		common_element('a', array('href' => $profile->profileurl,
								  'class' => 'nickname'),
					   $profile->nickname);
		# FIXME: URL, image, video, audio
		common_element_start('p', array('class' => 'content'));
		if ($notice->rendered) {
			common_raw($this->highlight($notice->rendered, $terms));
		} else {
			# XXX: may be some uncooked notices in the DB,
			# we cook them right now. This should probably disappear in future
			# versions (>> 0.4.x)
			common_raw($this->highlight(common_render_content($notice->content, $notice), $terms));
		}
		common_element_end('p');
		$noticeurl = common_local_url('shownotice', array('notice' => $notice->id));
		common_element_start('p', 'time');
		common_element('a', array('class' => 'permalink',
								  'href' => $noticeurl,
								  'title' => common_exact_date($notice->created)),
					   common_date_string($notice->created));
		if ($notice->reply_to) {
			$replyurl = common_local_url('shownotice', array('notice' => $notice->reply_to));
			common_text(' (');
			common_element('a', array('class' => 'inreplyto',
									  'href' => $replyurl),
						   _('in reply to...'));
			common_text(')');
		}
		common_element_start('a',
							 array('href' => common_local_url('newnotice',
															  array('replyto' => $profile->nickname)),
								   'onclick' => 'doreply("'.$profile->nickname.'"); return false',
								   'title' => _('reply'),
								   'class' => 'replybutton'));
		common_raw('&rarr;');
		common_element_end('a');
		common_element_end('p');
		common_element_end('li');
	}

	function highlight($text, $terms) {
		/* Highligh serach terms */
		$pattern = '/('.implode('|',array_map('htmlspecialchars', $terms)).')/i';
		$result = preg_replace($pattern, '<strong>\\1</strong>', $text);

		/* Remove highlighting from inside links, loop incase multiple highlights in links */
		$pattern = '/(href="[^"]*)<strong>('.implode('|',array_map('htmlspecialchars', $terms)).')<\/strong>([^"]*")/iU';
		do {
			$result = preg_replace($pattern, '\\1\\2\\3', $result, -1, $count);
		} while ($count);
		return $result;
	}
}
