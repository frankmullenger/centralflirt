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

require_once(INSTALLDIR.'/lib/stream.php');

define('SUBSCRIPTIONS_PER_ROW', 4);
define('SUBSCRIPTIONS', 80);

class ShowstreamAction extends StreamAction {

	function handle($args) {

		parent::handle($args);

		$nickname = common_canonical_nickname($this->arg('nickname'));
		$user = User::staticGet('nickname', $nickname);

		if (!$user) {
			$this->no_such_user();
			return;
		}

		$profile = $user->getProfile();

		if (!$profile) {
			common_server_error(_t('User record exists without profile.'));
			return;
		}

		# Looks like we're good; start output
		
		# For YADIS discovery, we also have a <meta> tag

		header('X-XRDS-Location: '. common_local_url('xrds', array('nickname' =>
																   $user->nickname)));

		common_show_header($profile->nickname, array($this, 'show_header'), $user);

		$cur = common_current_user();

		if ($cur && $profile->id == $cur->id) {
			common_notice_form();
		}

		$this->show_sidebar($profile);
		
		$this->show_notices($profile);
		
		common_show_footer();
	}

	function show_header($user) {
		common_element('link', array('rel' => 'alternate',
									 'href' => common_local_url('userrss', array('nickname' =>
																			   $user->nickname)),
									 'type' => 'application/rss+xml',
									 'title' => _t('Notice feed for ') . $user->nickname));
		# for remote subscriptions etc.
		common_element('meta', array('http-equiv' => 'X-XRDS-Location',
									 'content' => common_local_url('xrds', array('nickname' =>
																			   $user->nickname))));
	}
	
	function no_such_user() {
		common_user_error('No such user');
	}

	function show_sidebar($profile) {

		common_element_start('div', 'sidebar width33 floatRight greenBg');

		$this->show_profile($profile);

		$this->show_last_notice($profile);

		$cur = common_current_user();

		if ($cur) {
			if ($cur->id != $profile->id) {
				if ($cur->isSubscribed($profile)) {
					$this->show_unsubscribe_form($profile);
				} else {
					$this->show_subscribe_form($profile);
				}
			}
		} else {
			$this->show_remote_subscribe_form($profile);
		}

		$this->show_statistics($profile);

		$this->show_subscriptions($profile);

		common_element_end('div');
	}
	
	function show_profile($profile) {
		common_element_start('div', 'profile');

		$avatar = $profile->getAvatar(AVATAR_PROFILE_SIZE);
		if ($avatar) {
			common_element('img', array('src' => $avatar->url,
										'class' => 'avatar profile',
										'width' => AVATAR_PROFILE_SIZE,
										'height' => AVATAR_PROFILE_SIZE,
										'alt' => $profile->nickname));
		}
		if ($profile->fullname) {
			common_element_start('div', 'fullname');
			if ($profile->homepage) {
				common_element('a', array('href' => $profile->homepage),
							   $profile->fullname);
			} else {
				common_text($profile->fullname);
			}
			common_element_end('div');
		}
		if ($profile->location) {
			common_element('div', 'location', $profile->location);
		}
		if ($profile->bio) {
			common_element('div', 'bio', $profile->bio);
		}
		common_element_end('div');
	}

	function show_subscribe_form($profile) {
		common_element_start('form', array('id' => 'subscribe', 'method' => 'POST',
										   'action' => common_local_url('subscribe')));
		common_element('input', array('id' => 'subscribeto',
									  'name' => 'subscribeto',
									  'type' => 'hidden',
									  'value' => $profile->nickname));
		common_element('input', array('type' => 'submit',
									  'class' => 'button',
									  'value' => _t('Subscribe')));
		common_element_end('form');
	}

	function show_remote_subscribe_form($profile) {
		common_element_start('form', array('id' => 'remotesubscribe', 'method' => 'POST',
										   'action' => common_local_url('remotesubscribe')));
		common_hidden('nickname', $profile->nickname);
		common_input('profile', _t('Profile'));
		common_submit('submit',_t('Subscribe'));
		common_element_end('form');
	}
	
	function show_unsubscribe_form($profile) {
		common_element_start('form', array('id' => 'unsubscribe', 'method' => 'POST',
										   'action' => common_local_url('unsubscribe')));
		common_element('input', array('id' => 'unsubscribeto',
									  'name' => 'unsubscribeto',
									  'type' => 'hidden',
									  'value' => $profile->nickname));
		common_element('input', array('type' => 'submit',
									  'class' => 'button',
									  'value' => _t('Unsubscribe')));
		common_element_end('form');
	}

	function show_subscriptions($profile) {
		global $config;
		
		# XXX: add a limit
		$subs = DB_DataObject::factory('subscription');
		$subs->subscriber = $profile->id;

		# We ask for an extra one to know if we need to do another page

		$subs->limit(0, SUBSCRIPTIONS);

		$subs_count = $subs->find();

		common_element_start('div', 'subscriptions');

		common_element('h2', 'subscriptions', _t('Subscriptions'));

		$idx = 0;

		while ($subs->fetch()) {
			$idx++;
			if ($idx % SUBSCRIPTIONS_PER_ROW == 1) {
				common_element_start('div', 'row');
			}
			
			$other = Profile::staticGet($subs->subscribed);
			
			common_element_start('a', array('title' => ($other->fullname) ?
											$other->fullname :
											$other->nickname,
											'href' => $other->profileurl,
											'class' => 'subscription'));
			$avatar = $other->getAvatar(AVATAR_MINI_SIZE);
			common_element('img', array('src' => (($avatar) ? $avatar->url :  common_default_avatar(AVATAR_MINI_SIZE)),
										'width' => AVATAR_MINI_SIZE,
										'height' => AVATAR_MINI_SIZE,
										'class' => 'avatar mini',
										'alt' =>  ($other->fullname) ?
										$other->fullname :
										$other->nickname));
			common_element_end('a');
			
			if ($idx % SUBSCRIPTIONS_PER_ROW == 0) {
				common_element_end('div');
			}
			
			if ($idx == SUBSCRIPTIONS) {
				break;
			}
		}

		# close any unclosed row
		if ($idx % SUBSCRIPTIONS_PER_ROW != 0) {
			common_element_end('div');
		}

		common_element('a', array('href' => common_local_url('subscriptions',
															 array('nickname' => $profile->nickname)),
								  'class' => 'moresubscriptions'),
					   _t('All subscriptions'));

		common_element_end('div');
	}

	function show_statistics($profile) {

		// XXX: WORM cache this
		$subs = DB_DataObject::factory('subscription');
		$subs->subscriber = $profile->id;
		$subs_count = (int) $subs->count();

		$subbed = DB_DataObject::factory('subscription');
		$subbed->subscribed = $profile->id;
		$subbed_count = (int) $subbed->count();

		$notices = DB_DataObject::factory('notice');
		$notices->profile_id = $profile->id;
		$notice_count = (int) $notices->count();

		common_element_start('div', 'statistics');
		common_element('h2', 'statistics', _t('Statistics'));

		# Other stats...?
		common_element_start('dl', 'statistics');
		common_element('dt', 'subscriptions', _t('Subscriptions'));
		common_element('dd', 'subscriptions', $subs_count);
		common_element('dt', 'subscribers', _t('Subscribers'));
		common_element('dd', 'subscribers', $subbed_count);
		common_element('dt', 'notices', _t('Notices'));
		common_element('dd', 'notices', $notice_count);
		common_element_end('dl');

		common_element_end('div');
	}

	function show_notices($profile) {

		$notice = DB_DataObject::factory('notice');
		$notice->profile_id = $profile->id;

		$notice->orderBy('created DESC');

		$page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;

		$notice->limit((($page-1)*NOTICES_PER_PAGE), NOTICES_PER_PAGE + 1);

		$cnt = $notice->find();

		common_element_start('div', 'notices width66 floatLeft');

		for ($i = 0; $i < min($cnt, NOTICES_PER_PAGE); $i++) {
			if ($notice->fetch()) {
				$this->show_notice($notice);
			} else {
				// shouldn't happen!
				break;
			}
		}

		if ($page > 1) {
			common_element_start('span', 'floatLeft width25');
			common_element('a', array('href' => common_local_url('showstream', 
																 array('nickname' => $profile->nickname,
																	   'page' => $page-1)),
									  'class' => 'newer'),
						   _t('Newer'));
			common_element_end('span');
		}
		
		if ($cnt > NOTICES_PER_PAGE) {
			common_element_start('span', 'floatRight width25');
			common_element('a', array('href' => common_local_url('showstream', 
																 array('nickname' => $profile->nickname,
																	   'page' => $page+1)),
									  'class' => 'older'),
						   _t('Older'));
			common_element_end('span');
		}
		
		# XXX: show a link for the next page
		common_element_end('div');
	}

	function show_last_notice($profile) {

		common_element_start('div', 'lastnotice');
		common_element('h2', 'lastnotice', _t('Currently'));

		$notice = DB_DataObject::factory('notice');
		$notice->profile_id = $profile->id;
		$notice->orderBy('created DESC');
		$notice->limit(0, 1);

		if ($notice->find(true)) {
			# FIXME: URL, image, video, audio
			common_element_start('span', array('class' => 'content'));
			common_raw(common_render_content($notice->content, $notice));
			common_element_end('span');
		}

		common_element_end('div');
	}
	
	function show_notice($notice) {
		$profile = $notice->getProfile();
		# XXX: RDFa
		common_element_start('div', array('class' => 'notice',
										  'id' => 'notice-' . $notice->id));
		$noticeurl = common_local_url('shownotice', array('notice' => $notice->id));
		# FIXME: URL, image, video, audio
		common_element_start('span', array('class' => 'content'));
		common_raw(common_render_content($notice->content, $notice));
		common_element_end('span');
		common_element('a', array('class' => 'notice',
								  'href' => $noticeurl),
					   common_date_string($notice->created));
		common_element_end('div');
	}
}
