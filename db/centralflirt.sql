
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `centralflirt`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `avatar`
-- 

CREATE TABLE IF NOT EXISTS `avatar` (
  `profile_id` int(11) NOT NULL COMMENT 'foreign key to profile table',
  `original` tinyint(1) default '0' COMMENT 'uploaded by user or generated?',
  `width` int(11) NOT NULL COMMENT 'image width',
  `height` int(11) NOT NULL COMMENT 'image height',
  `mediatype` varchar(32) collate utf8_bin NOT NULL COMMENT 'file type',
  `filename` varchar(255) collate utf8_bin default NULL COMMENT 'local filename, if local',
  `url` varchar(255) collate utf8_bin default NULL COMMENT 'avatar location',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`profile_id`,`width`,`height`),
  UNIQUE KEY `url` (`url`),
  KEY `avatar_profile_id_idx` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `confirm_address`
-- 

CREATE TABLE IF NOT EXISTS `confirm_address` (
  `code` varchar(32) collate utf8_bin NOT NULL COMMENT 'good random code',
  `user_id` int(11) NOT NULL COMMENT 'user who requested confirmation',
  `address` varchar(255) collate utf8_bin NOT NULL COMMENT 'address (email, Jabber, SMS, etc.)',
  `address_extra` varchar(255) collate utf8_bin NOT NULL COMMENT 'carrier ID, for SMS',
  `address_type` varchar(8) collate utf8_bin NOT NULL COMMENT 'address type ("email", "jabber", "sms")',
  `claimed` datetime default NULL COMMENT 'date this was claimed for queueing',
  `sent` datetime default NULL COMMENT 'date this was sent for queueing',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `consumer`
-- 

CREATE TABLE IF NOT EXISTS `consumer` (
  `consumer_key` varchar(255) collate utf8_bin NOT NULL COMMENT 'unique identifier, root URL',
  `seed` char(32) collate utf8_bin NOT NULL COMMENT 'seed for new tokens by this consumer',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`consumer_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `dating_profile`
-- 

CREATE TABLE IF NOT EXISTS `dating_profile` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `address_1` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `city` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `state` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `country` int(11) default NULL,
  `postcode` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `bio` text character set utf8 collate utf8_unicode_ci,
  `birthdate` date default NULL,
  `sex` int(11) default NULL,
  `partner_sex` int(11) default NULL,
  `interested_in` int(11) default NULL,
  `url` varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  `profession` varchar(255) collate utf8_bin NOT NULL,
  `headline` varchar(255) collate utf8_bin NOT NULL,
  `height` int(11) default NULL,
  `hair` int(11) default NULL,
  `body_type` int(11) default NULL,
  `ethnicity` int(11) default NULL,
  `eye_colour` int(11) default NULL,
  `marital_status` int(11) default NULL,
  `have_children` int(11) default NULL,
  `smoke` int(11) default NULL,
  `drink` int(11) default NULL,
  `religion` int(11) default NULL,
  `languages` varchar(100) collate utf8_bin default NULL COMMENT 'List of language ids',
  `education` int(11) default NULL,
  `politics` int(11) default NULL,
  `best_feature` int(11) default NULL,
  `body_art` int(11) default NULL,
  `fun` text collate utf8_bin NOT NULL,
  `fav_spot` text collate utf8_bin NOT NULL,
  `fav_media` text collate utf8_bin NOT NULL,
  `first_date` text collate utf8_bin NOT NULL,
  `fake` tinyint(1) NOT NULL default '0' COMMENT 'Flag fake profiles',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Profile data for users.';

-- --------------------------------------------------------

-- 
-- Table structure for table `dating_profile_seq`
-- 

CREATE TABLE IF NOT EXISTS `dating_profile_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `dating_profile_tag`
-- 

CREATE TABLE IF NOT EXISTS `dating_profile_tag` (
  `tagger` int(11) NOT NULL COMMENT 'user making the tag',
  `tagged` int(11) NOT NULL COMMENT 'profile tagged',
  `tag` varchar(64) collate utf8_bin NOT NULL COMMENT 'tag associated with this dating profile',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date the tag was added',
  PRIMARY KEY  (`tagger`,`tagged`,`tag`),
  KEY `profile_tag_modified_idx` (`modified`),
  KEY `profile_tag_tagger_tag_idx` (`tagger`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `fave`
-- 

CREATE TABLE IF NOT EXISTS `fave` (
  `notice_id` int(11) NOT NULL COMMENT 'notice that is the favorite',
  `user_id` int(11) NOT NULL COMMENT 'user who likes this notice',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`notice_id`,`user_id`),
  KEY `fave_notice_id_idx` (`notice_id`),
  KEY `fave_user_id_idx` (`user_id`),
  KEY `fave_modified_idx` (`modified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `foreign_link`
-- 

CREATE TABLE IF NOT EXISTS `foreign_link` (
  `user_id` int(11) NOT NULL default '0' COMMENT 'link to user on this system, if exists',
  `foreign_id` int(11) NOT NULL default '0' COMMENT 'link ',
  `service` int(11) NOT NULL COMMENT 'foreign key to service',
  `credentials` varchar(255) collate utf8_bin default NULL COMMENT 'authc credentials, typically a password',
  `noticesync` tinyint(4) NOT NULL default '1' COMMENT 'notice synchronization, bit 1 = sync outgoing, bit 2 = sync incoming, bit 3 = filter local replies',
  `friendsync` tinyint(4) NOT NULL default '2' COMMENT 'friend synchronization, bit 1 = sync outgoing, bit 2 = sync incoming',
  `profilesync` tinyint(4) NOT NULL default '1' COMMENT 'profile synchronization, bit 1 = sync outgoing, bit 2 = sync incoming',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`user_id`,`foreign_id`,`service`),
  KEY `foreign_user_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `foreign_service`
-- 

CREATE TABLE IF NOT EXISTS `foreign_service` (
  `id` int(11) NOT NULL COMMENT 'numeric key for service',
  `name` varchar(32) collate utf8_bin NOT NULL COMMENT 'name of the service',
  `description` varchar(255) collate utf8_bin default NULL COMMENT 'description',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `foreign_subscription`
-- 

CREATE TABLE IF NOT EXISTS `foreign_subscription` (
  `service` int(11) NOT NULL COMMENT 'service where relationship happens',
  `subscriber` int(11) NOT NULL COMMENT 'subscriber on foreign service',
  `subscribed` int(11) NOT NULL COMMENT 'subscribed user',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  PRIMARY KEY  (`service`,`subscriber`,`subscribed`),
  KEY `foreign_subscription_subscriber_idx` (`subscriber`),
  KEY `foreign_subscription_subscribed_idx` (`subscribed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `foreign_user`
-- 

CREATE TABLE IF NOT EXISTS `foreign_user` (
  `id` int(11) NOT NULL COMMENT 'unique numeric key on foreign service',
  `service` int(11) NOT NULL COMMENT 'foreign key to service',
  `uri` varchar(255) collate utf8_bin NOT NULL COMMENT 'identifying URI',
  `nickname` varchar(255) collate utf8_bin default NULL COMMENT 'nickname on foreign service',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`,`service`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `group_inbox`
-- 

CREATE TABLE IF NOT EXISTS `group_inbox` (
  `group_id` int(11) NOT NULL COMMENT 'group receiving the message',
  `notice_id` int(11) NOT NULL COMMENT 'notice received',
  `created` datetime NOT NULL COMMENT 'date the notice was created',
  PRIMARY KEY  (`group_id`,`notice_id`),
  KEY `group_inbox_created_idx` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `group_member`
-- 

CREATE TABLE IF NOT EXISTS `group_member` (
  `group_id` int(11) NOT NULL COMMENT 'foreign key to user_group',
  `profile_id` int(11) NOT NULL COMMENT 'foreign key to profile table',
  `is_admin` tinyint(1) default '0' COMMENT 'is this user an admin?',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`group_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `invitation`
-- 

CREATE TABLE IF NOT EXISTS `invitation` (
  `code` varchar(32) collate utf8_bin NOT NULL COMMENT 'random code for an invitation',
  `user_id` int(11) NOT NULL COMMENT 'who sent the invitation',
  `address` varchar(255) collate utf8_bin NOT NULL COMMENT 'invitation sent to',
  `address_type` varchar(8) collate utf8_bin NOT NULL COMMENT 'address type ("email", "jabber", "sms")',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  PRIMARY KEY  (`code`),
  KEY `invitation_address_idx` (`address`,`address_type`),
  KEY `invitation_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `message`
-- 

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique identifier',
  `uri` varchar(255) collate utf8_bin default NULL COMMENT 'universally unique identifier',
  `from_profile` int(11) NOT NULL COMMENT 'who the message is from',
  `to_profile` int(11) NOT NULL COMMENT 'who the message is to',
  `content` varchar(140) collate utf8_bin default NULL COMMENT 'message content',
  `rendered` text collate utf8_bin COMMENT 'HTML version of the content',
  `url` varchar(255) collate utf8_bin default NULL COMMENT 'URL of any attachment (image, video, bookmark, whatever)',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  `source` varchar(32) collate utf8_bin default NULL COMMENT 'source of comment, like "web", "im", or "clientname"',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uri` (`uri`),
  KEY `message_from_idx` (`from_profile`),
  KEY `message_to_idx` (`to_profile`),
  KEY `message_created_idx` (`created`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `nonce`
-- 

CREATE TABLE IF NOT EXISTS `nonce` (
  `consumer_key` varchar(255) collate utf8_bin NOT NULL COMMENT 'unique identifier, root URL',
  `tok` char(32) collate utf8_bin NOT NULL COMMENT 'identifying value',
  `nonce` char(32) collate utf8_bin NOT NULL COMMENT 'nonce',
  `ts` datetime NOT NULL COMMENT 'timestamp sent',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`consumer_key`,`tok`,`nonce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `notice`
-- 

CREATE TABLE IF NOT EXISTS `notice` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique identifier',
  `profile_id` int(11) NOT NULL COMMENT 'who made the update',
  `uri` varchar(255) collate utf8_bin default NULL COMMENT 'universally unique identifier, usually a tag URI',
  `content` varchar(140) collate utf8_bin default NULL COMMENT 'update content',
  `rendered` text collate utf8_bin COMMENT 'HTML version of the content',
  `url` varchar(255) collate utf8_bin default NULL COMMENT 'URL of any attachment (image, video, bookmark, whatever)',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  `reply_to` int(11) default NULL COMMENT 'notice replied to (usually a guess)',
  `is_local` tinyint(4) default '0' COMMENT 'notice was generated by a user',
  `is_private` tinyint(4) default '0' COMMENT 'If notice is private.',
  `source` varchar(32) collate utf8_bin default NULL COMMENT 'source of comment, like "web", "im", or "clientname"',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uri` (`uri`),
  KEY `notice_profile_id_idx` (`profile_id`),
  KEY `notice_created_idx` (`created`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=205 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `notice_inbox`
-- 

CREATE TABLE IF NOT EXISTS `notice_inbox` (
  `user_id` int(11) NOT NULL COMMENT 'user receiving the message',
  `notice_id` int(11) NOT NULL COMMENT 'notice received',
  `created` datetime NOT NULL COMMENT 'date the notice was created',
  `source` tinyint(4) default '1' COMMENT 'reason it is in the inbox; 1=subscription',
  PRIMARY KEY  (`user_id`,`notice_id`),
  KEY `notice_inbox_notice_id_idx` (`notice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `notice_source`
-- 

CREATE TABLE IF NOT EXISTS `notice_source` (
  `code` varchar(32) collate utf8_bin NOT NULL COMMENT 'source code',
  `name` varchar(255) collate utf8_bin NOT NULL COMMENT 'name of the source',
  `url` varchar(255) collate utf8_bin NOT NULL COMMENT 'url to link to',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `notice_tag`
-- 

CREATE TABLE IF NOT EXISTS `notice_tag` (
  `tag` varchar(64) collate utf8_bin NOT NULL COMMENT 'hash tag associated with this notice',
  `notice_id` int(11) NOT NULL COMMENT 'notice tagged',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  PRIMARY KEY  (`tag`,`notice_id`),
  KEY `notice_tag_created_idx` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `oid_associations`
-- 

CREATE TABLE IF NOT EXISTS `oid_associations` (
  `server_url` blob NOT NULL,
  `handle` varchar(255) character set latin1 NOT NULL default '',
  `secret` blob,
  `issued` int(11) default NULL,
  `lifetime` int(11) default NULL,
  `assoc_type` varchar(64) collate utf8_bin default NULL,
  PRIMARY KEY  (`server_url`(255),`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `oid_nonces`
-- 

CREATE TABLE IF NOT EXISTS `oid_nonces` (
  `server_url` varchar(2047) collate utf8_bin default NULL,
  `timestamp` int(11) default NULL,
  `salt` char(40) collate utf8_bin default NULL,
  UNIQUE KEY `server_url` (`server_url`(255),`timestamp`,`salt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `pending_subscription`
-- 

CREATE TABLE IF NOT EXISTS `pending_subscription` (
  `subscriber` int(11) NOT NULL COMMENT 'profile listening',
  `subscribed` int(11) NOT NULL COMMENT 'profile being listened to',
  `jabber` tinyint(4) default '1' COMMENT 'deliver jabber messages',
  `sms` tinyint(4) default '1' COMMENT 'deliver sms messages',
  `token` varchar(255) collate utf8_bin default NULL COMMENT 'authorization token',
  `secret` varchar(255) collate utf8_bin default NULL COMMENT 'token secret',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`subscriber`,`subscribed`),
  KEY `subscription_subscriber_idx` (`subscriber`),
  KEY `subscription_subscribed_idx` (`subscribed`),
  KEY `subscription_token_idx` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `profile`
-- 

CREATE TABLE IF NOT EXISTS `profile` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique identifier',
  `nickname` varchar(64) collate utf8_bin NOT NULL COMMENT 'nickname or username',
  `fullname` varchar(255) collate utf8_bin default NULL COMMENT 'display name',
  `profileurl` varchar(255) collate utf8_bin default NULL COMMENT 'URL, cached so we dont regenerate',
  `homepage` varchar(255) collate utf8_bin default NULL COMMENT 'identifying URL',
  `bio` varchar(140) collate utf8_bin default NULL COMMENT 'descriptive biography',
  `location` varchar(255) collate utf8_bin default NULL COMMENT 'physical location',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  KEY `profile_nickname_idx` (`nickname`),
  FULLTEXT KEY `nickname` (`nickname`,`fullname`,`location`,`bio`,`homepage`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=478 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `profile_block`
-- 

CREATE TABLE IF NOT EXISTS `profile_block` (
  `blocker` int(11) NOT NULL COMMENT 'user making the block',
  `blocked` int(11) NOT NULL COMMENT 'profile that is blocked',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date of blocking',
  PRIMARY KEY  (`blocker`,`blocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `profile_tag`
-- 

CREATE TABLE IF NOT EXISTS `profile_tag` (
  `tagger` int(11) NOT NULL COMMENT 'user making the tag',
  `tagged` int(11) NOT NULL COMMENT 'profile tagged',
  `tag` varchar(64) collate utf8_bin NOT NULL COMMENT 'hash tag associated with this notice',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date the tag was added',
  PRIMARY KEY  (`tagger`,`tagged`,`tag`),
  KEY `profile_tag_modified_idx` (`modified`),
  KEY `profile_tag_tagger_tag_idx` (`tagger`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `queue_item`
-- 

CREATE TABLE IF NOT EXISTS `queue_item` (
  `notice_id` int(11) NOT NULL COMMENT 'notice queued',
  `transport` varchar(8) collate utf8_bin NOT NULL COMMENT 'queue for what? "email", "jabber", "sms", "irc", ...',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `claimed` datetime default NULL COMMENT 'date this item was claimed',
  PRIMARY KEY  (`notice_id`,`transport`),
  KEY `queue_item_created_idx` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `related_group`
-- 

CREATE TABLE IF NOT EXISTS `related_group` (
  `group_id` int(11) NOT NULL COMMENT 'foreign key to user_group',
  `related_group_id` int(11) NOT NULL COMMENT 'foreign key to user_group',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  PRIMARY KEY  (`group_id`,`related_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `remember_me`
-- 

CREATE TABLE IF NOT EXISTS `remember_me` (
  `code` varchar(32) collate utf8_bin NOT NULL COMMENT 'good random code',
  `user_id` int(11) NOT NULL COMMENT 'user who is logged in',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `remote_profile`
-- 

CREATE TABLE IF NOT EXISTS `remote_profile` (
  `id` int(11) NOT NULL COMMENT 'foreign key to profile table',
  `uri` varchar(255) collate utf8_bin default NULL COMMENT 'universally unique identifier, usually a tag URI',
  `postnoticeurl` varchar(255) collate utf8_bin default NULL COMMENT 'URL we use for posting notices',
  `updateprofileurl` varchar(255) collate utf8_bin default NULL COMMENT 'URL we use for updates to this profile',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `reply`
-- 

CREATE TABLE IF NOT EXISTS `reply` (
  `notice_id` int(11) NOT NULL COMMENT 'notice that is the reply',
  `profile_id` int(11) NOT NULL COMMENT 'profile replied to',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  `replied_id` int(11) default NULL COMMENT 'notice replied to (not used, see notice.reply_to)',
  PRIMARY KEY  (`notice_id`,`profile_id`),
  KEY `reply_notice_id_idx` (`notice_id`),
  KEY `reply_profile_id_idx` (`profile_id`),
  KEY `reply_replied_id_idx` (`replied_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `sms_carrier`
-- 

CREATE TABLE IF NOT EXISTS `sms_carrier` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key for SMS carrier',
  `name` varchar(64) collate utf8_bin default NULL COMMENT 'name of the carrier',
  `email_pattern` varchar(255) collate utf8_bin NOT NULL COMMENT 'sprintf pattern for making an email address from a phone number',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `subscription`
-- 

CREATE TABLE IF NOT EXISTS `subscription` (
  `subscriber` int(11) NOT NULL COMMENT 'profile listening',
  `subscribed` int(11) NOT NULL COMMENT 'profile being listened to',
  `jabber` tinyint(4) default '1' COMMENT 'deliver jabber messages',
  `sms` tinyint(4) default '1' COMMENT 'deliver sms messages',
  `token` varchar(255) collate utf8_bin default NULL COMMENT 'authorization token',
  `secret` varchar(255) collate utf8_bin default NULL COMMENT 'token secret',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`subscriber`,`subscribed`),
  KEY `subscription_subscriber_idx` (`subscriber`),
  KEY `subscription_subscribed_idx` (`subscribed`),
  KEY `subscription_token_idx` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `token`
-- 

CREATE TABLE IF NOT EXISTS `token` (
  `consumer_key` varchar(255) collate utf8_bin NOT NULL COMMENT 'unique identifier, root URL',
  `tok` char(32) collate utf8_bin NOT NULL COMMENT 'identifying value',
  `secret` char(32) collate utf8_bin NOT NULL COMMENT 'secret value',
  `type` tinyint(4) NOT NULL default '0' COMMENT 'request or access',
  `state` tinyint(4) default '0' COMMENT 'for requests; 0 = initial, 1 = authorized, 2 = used',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`consumer_key`,`tok`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL COMMENT 'foreign key to profile table',
  `nickname` varchar(64) collate utf8_bin default NULL COMMENT 'nickname or username, duped in profile',
  `password` varchar(255) collate utf8_bin default NULL COMMENT 'salted password, can be null for OpenID users',
  `email` varchar(255) collate utf8_bin default NULL COMMENT 'email address for password recovery etc.',
  `incomingemail` varchar(255) collate utf8_bin default NULL COMMENT 'email address for post-by-email',
  `emailnotifysub` tinyint(4) default '1' COMMENT 'Notify by email of subscriptions',
  `emailnotifyfav` tinyint(4) default '1' COMMENT 'Notify by email of favorites',
  `emailnotifynudge` tinyint(4) default '1' COMMENT 'Notify by email of nudges',
  `emailnotifymsg` tinyint(4) default '1' COMMENT 'Notify by email of direct messages',
  `emailmicroid` tinyint(4) default '1' COMMENT 'whether to publish email microid',
  `language` varchar(50) collate utf8_bin default NULL COMMENT 'preferred language',
  `timezone` varchar(50) collate utf8_bin default NULL COMMENT 'timezone',
  `emailpost` tinyint(4) default '1' COMMENT 'Post by email',
  `jabber` varchar(255) collate utf8_bin default NULL COMMENT 'jabber ID for notices',
  `jabbernotify` tinyint(4) default '0' COMMENT 'whether to send notices to jabber',
  `jabberreplies` tinyint(4) default '0' COMMENT 'whether to send notices to jabber on replies',
  `jabbermicroid` tinyint(4) default '1' COMMENT 'whether to publish xmpp microid',
  `updatefrompresence` tinyint(4) default '0' COMMENT 'whether to record updates from Jabber presence notices',
  `sms` varchar(64) collate utf8_bin default NULL COMMENT 'sms phone number',
  `carrier` int(11) default NULL COMMENT 'foreign key to sms_carrier',
  `smsnotify` tinyint(4) default '0' COMMENT 'whether to send notices to SMS',
  `smsreplies` tinyint(4) default '0' COMMENT 'whether to send notices to SMS on replies',
  `smsemail` varchar(255) collate utf8_bin default NULL COMMENT 'built from sms and carrier',
  `uri` varchar(255) collate utf8_bin default NULL COMMENT 'universally unique identifier, usually a tag URI',
  `autosubscribe` tinyint(4) default '0' COMMENT 'automatically subscribe to users who subscribe to us',
  `post_privately` tinyint(4) default '0' COMMENT 'Flag to keep posts private for subscribers only (unless individual notices are made public).',
  `urlshorteningservice` varchar(50) collate utf8_bin default 'ur1.ca' COMMENT 'service to use for auto-shortening URLs',
  `inboxed` tinyint(4) default '0' COMMENT 'has an inbox been created for this user?',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `incomingemail` (`incomingemail`),
  UNIQUE KEY `jabber` (`jabber`),
  UNIQUE KEY `sms` (`sms`),
  UNIQUE KEY `uri` (`uri`),
  KEY `user_smsemail_idx` (`smsemail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_group`
-- 

CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(11) NOT NULL auto_increment COMMENT 'unique identifier',
  `nickname` varchar(64) collate utf8_bin default NULL COMMENT 'nickname for addressing',
  `fullname` varchar(255) collate utf8_bin default NULL COMMENT 'display name',
  `homepage` varchar(255) collate utf8_bin default NULL COMMENT 'URL, cached so we dont regenerate',
  `description` varchar(140) collate utf8_bin default NULL COMMENT 'descriptive biography',
  `location` varchar(255) collate utf8_bin default NULL COMMENT 'related physical location, if any',
  `original_logo` varchar(255) collate utf8_bin default NULL COMMENT 'original size logo',
  `homepage_logo` varchar(255) collate utf8_bin default NULL COMMENT 'homepage (profile) size logo',
  `stream_logo` varchar(255) collate utf8_bin default NULL COMMENT 'stream-sized logo',
  `mini_logo` varchar(255) collate utf8_bin default NULL COMMENT 'mini logo',
  `is_private` tinyint(1) NOT NULL default '0' COMMENT 'Flag to mark if group is personal.',
  `admin_nickname` varchar(64) collate utf8_bin default NULL COMMENT 'Denormalising this table to remove join from query for private groups.',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nickname` (`nickname`,`admin_nickname`),
  KEY `user_group_nickname_idx` (`nickname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_openid`
-- 

CREATE TABLE IF NOT EXISTS `user_openid` (
  `canonical` varchar(255) collate utf8_bin NOT NULL COMMENT 'Canonical true URL',
  `display` varchar(255) collate utf8_bin NOT NULL COMMENT 'URL for viewing, may be different from canonical',
  `user_id` int(11) NOT NULL COMMENT 'user owning this URL',
  `created` datetime NOT NULL COMMENT 'date this record was created',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'date this record was modified',
  PRIMARY KEY  (`canonical`),
  UNIQUE KEY `display` (`display`),
  KEY `user_openid_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `nonce`
-- 
ALTER TABLE `nonce`
  ADD CONSTRAINT `nonce_ibfk_1` FOREIGN KEY (`consumer_key`, `tok`) REFERENCES `token` (`consumer_key`, `tok`);

