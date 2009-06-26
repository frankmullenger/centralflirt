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

/**
 * Table Definition for user
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';
require_once 'Validate.php';

class User extends Memcached_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user';                            // table name
    public $id;                              // int(11)  not_null primary_key
    public $nickname;                        // string(64)  unique_key binary
    public $password;                        // string(255)  binary
    public $email;                           // string(255)  unique_key binary
    public $incomingemail;                   // string(255)  unique_key binary
    public $emailnotifysub;                  // int(4)  
    public $emailnotifyfav;                  // int(4)  
    public $emailnotifynudge;                // int(4)  
    public $emailnotifymsg;                  // int(4)  
    public $emailmicroid;                    // int(4)  
    public $language;                        // string(50)  binary
    public $timezone;                        // string(50)  binary
    public $emailpost;                       // int(4)  
    public $jabber;                          // string(255)  unique_key binary
    public $jabbernotify;                    // int(4)  
    public $jabberreplies;                   // int(4)  
    public $jabbermicroid;                   // int(4)  
    public $updatefrompresence;              // int(4)  
    public $sms;                             // string(64)  unique_key binary
    public $carrier;                         // int(11)  
    public $smsnotify;                       // int(4)  
    public $smsreplies;                      // int(4)  
    public $smsemail;                        // string(255)  multiple_key binary
    public $uri;                             // string(255)  unique_key binary
    public $autosubscribe;                   // int(4)  
    public $post_privately;                  // int(4)  
    public $urlshorteningservice;            // string(50)  binary
    public $inboxed;                         // int(4)  
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('User',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public $passwordHashed = false;

    function getProfile()
    {
        return Profile::staticGet('id', $this->id);
    }
    
    function getDatingProfile() 
    {
        //Check that the config setting for dating profiles is enabled before attempting to retrieve dating profiles
        if (common_config('profile', 'enable_dating')) {
            
            //If a dating profile has not been created yet then return an empty one
            $datingProfile = Dating_profile::staticGet('id', $this->id);
            if (!$datingProfile instanceof Dating_profile) {
                $datingProfile = new Dating_profile;
            }
            return $datingProfile;
        }
        else {
            return false;
        }
    }

    /**
     * Where $this is subscribed to $other
     *
     * @param User $other
     * @return boolean
     */
    function isSubscribed($other)
    {
        assert(!is_null($other));
        # XXX: cache results of this query
        $sub = Subscription::pkeyGet(array('subscriber' => $this->id,
                                           'subscribed' => $other->id));
        return (is_null($sub)) ? false : true;
    }
    
    /**
     * Where $other is subscribed to $this
     * TODO frank: what were you thinking, this is kind of pointless.
     *
     * @param User $other
     * @return boolean
     */
    function isSubscriber($other)
    {
        assert(!is_null($other));
        # XXX: cache results of this query
        $sub = Subscription::pkeyGet(array('subscriber' => $other->id,
                                           'subscribed' => $this->id));
        return (is_null($sub)) ? false : true;
    }
    
    /**
     * Where $other is pending subscription to $this - TODO frank: refactor as pendingSubscriptionFrom()
     *
     * @param User $other
     * @return boolean
     */
    function isPendingSubscription($other) 
    {
        assert(!is_null($other));
        # XXX: cache results of this query
        $sub = Pending_subscription::pkeyGet(array('subscriber' => $other->id,
                                                   'subscribed' => $this->id));
        return (is_null($sub)) ? false : true;
    }
    
    /**
     * Where $this is pending subscription to other
     *
     * @param unknown_type $other
     * @return unknown
     */
    function isPendingSubscriptionTo($other) 
    {
        assert(!is_null($other));
        # XXX: cache results of this query
        $sub = Pending_subscription::pkeyGet(array('subscriber' => $this->id,
                                                   'subscribed' => $other->id));
        return (is_null($sub)) ? false : true;
    }

    # 'update' won't write key columns, so we have to do it ourselves.

    function updateKeys(&$orig)
    {
        $parts = array();
        foreach (array('nickname', 'email', 'jabber', 'incomingemail', 'sms', 'carrier', 'smsemail', 'language', 'timezone') as $k) {
            if (strcmp($this->$k, $orig->$k) != 0) {
                $parts[] = $k . ' = ' . $this->_quote($this->$k);
            }
        }
        if (count($parts) == 0) {
            # No changes
            return true;
        }
        $toupdate = implode(', ', $parts);

        $table = $this->tableName();
        if(common_config('db','quote_identifiers')) {
            $table = '"' . $table . '"';
        }
        $qry = 'UPDATE ' . $table . ' SET ' . $toupdate .
          ' WHERE id = ' . $this->id;
        $orig->decache();
        $result = $this->query($qry);
        if ($result) {
            $this->encache();
        }
        return $result;
    }

    function allowed_nickname($nickname)
    {
        # XXX: should already be validated for size, content, etc.
        static $blacklist = array('rss', 'xrds', 'doc', 'main',
                                  'settings', 'notice', 'user',
                                  'search', 'avatar', 'tag', 'tags',
                                  'api', 'message', 'group', 'groups', 'public');
        $merged = array_merge($blacklist, common_config('nickname', 'blacklist'));
        return !in_array($nickname, $merged);
    }

    function getCurrentNotice($dt=null)
    {
        $profile = $this->getProfile();
        if (!$profile) {
            return null;
        }
        return $profile->getCurrentNotice($dt);
    }

    function getCarrier()
    {
        return Sms_carrier::staticGet('id', $this->carrier);
    }

    function subscribeTo($other)
    {
        /*
         * For dating sites use a pending subscription as subscription action
         * If other is subscribed to user then create a subscription without pending
         */
        if (common_config('profile', 'enable_dating')) {
            
            //If other is not subscribed to this
            if (!$this->isSubscriber($other)) {
                $sub = new Pending_subscription();
            }
            else {
                $sub = new Subscription();
            }
        }
        else {
            $sub = new Subscription();
        }
        
        $sub->subscriber = $this->id;
        $sub->subscribed = $other->id;

        $sub->created = common_sql_now(); # current time

        if (!$sub->insert()) {
            return false;
        }

        return true;
    }
    
    /**
     * Allow $other to subscribe to $this User. Check that a pending subscription exists
     * remove the pending subscription(s) and add a proper subscription.
     *
     * @param User $other
     * @return boolean
     */
    function allowSubscription($other) 
    {
        
        //TODO frank: need to wrap these in a transaction?
        
        /*
         * check that a pending subscription exists from $other for this User
         * remove the pending subscription
         * add a proper subscription
         * return true/false
         */
        $pendingSubscription = new Pending_subscription();
        $pendingSubscription->whereAdd('subscribed = '.$this->id);
        $pendingSubscription->whereAdd('subscriber = '.$other->id);
        $result = $pendingSubscription->find();
        
        if ($result > 0) {

            $pendingSubscription->fetch();
            common_debug(common_log_objstring($pendingSubscription));
            
            $deleteResult = $pendingSubscription->delete();
            
            if (!$deleteResult) {
                common_log_db_error($pendingSubscription, 'DELETE', __FILE__);
                return false;
            }
        }
        else {
            common_log(LOG_ERR, 'Pending subscriptions do not exist.', __FILE__);
            return false;
        }
        
        $subscription = new Subscription();
        $subscription->subscriber = $other->id;
        $subscription->subscribed = $this->id;
        $subscription->created = common_sql_now();

        $result = $subscription->insert();

        if (!$result) {
            common_log_db_error($subscription, 'INSERT', __FILE__);
            return false;
        }
        return true;
    }

    function hasBlocked($other)
    {

        $block = Profile_block::get($this->id, $other->id);

        if (is_null($block)) {
            $result = false;
        } else {
            $result = true;
            $block->free();
        }

        return $result;
    }

    static function register($fields) {

        # MAGICALLY put fields into current scope

        extract($fields);

        $profile = new Profile();

        $profile->query('BEGIN');

        $profile->nickname = $nickname;
        $profile->profileurl = common_profile_url($nickname);

        if ($fullname) {
            $profile->fullname = $fullname;
        }
        if ($homepage) {
            $profile->homepage = $homepage;
        }
        if ($bio) {
            $profile->bio = $bio;
        }
        if ($location) {
            $profile->location = $location;
        }

        $profile->created = common_sql_now();

        $id = $profile->insert();

        if (!$id) {
            common_log_db_error($profile, 'INSERT', __FILE__);
            return false;
        }

        $user = new User();

        $user->id = $id;
        $user->nickname = $nickname;

        if ($password) { # may not have a password for OpenID users
            $user->password = common_munge_password($password, $id);
        }

        # Users who respond to invite email have proven their ownership of that address

        if ($code) {
            $invite = Invitation::staticGet($code);
            if ($invite && $invite->address && $invite->address_type == 'email' && $invite->address == $email) {
                $user->email = $invite->address;
            }
        }

        $inboxes = common_config('inboxes', 'enabled');

        if ($inboxes === true || $inboxes == 'transitional') {
            $user->inboxed = 1;
        }

        $user->created = common_sql_now();
        $user->uri = common_user_uri($user);

        $result = $user->insert();

        if (!$result) {
            common_log_db_error($user, 'INSERT', __FILE__);
            return false;
        }

        # Everyone is subscribed to themself

        $subscription = new Subscription();
        $subscription->subscriber = $user->id;
        $subscription->subscribed = $user->id;
        $subscription->created = $user->created;

        $result = $subscription->insert();

        if (!$result) {
            common_log_db_error($subscription, 'INSERT', __FILE__);
            return false;
        }

        if ($email && !$user->email) {

            $confirm = new Confirm_address();
            $confirm->code = common_confirmation_code(128);
            $confirm->user_id = $user->id;
            $confirm->address = $email;
            $confirm->address_type = 'email';

            $result = $confirm->insert();
            if (!$result) {
                common_log_db_error($confirm, 'INSERT', __FILE__);
                return false;
            }
        }

        if ($code && $user->email) {
            $user->emailChanged();
        }

        $profile->query('COMMIT');

        if ($email && !$user->email) {
            mail_confirm_address($user, $confirm->code, $profile->nickname, $email);
        }

        return $user;
    }
    
    static function datingRegister($fields) {

        # MAGICALLY put fields into current scope
        extract($fields['User']);
        extract($fields['DatingProfile']);
        
        /*
         * Save standard profile
         */
        $profile = new Profile();

        $profile->query('BEGIN');

        $profile->nickname = $nickname;
        $profile->profileurl = common_profile_url($nickname);
        if ($city) {
            $profile->city = $city;
        }
        $profile->created = common_sql_now();
        
        if (isset($created)) {
            $profile->created = $created;
        }
        
        $id = $profile->insert();
        if (!$id) {
            common_log_db_error($profile, 'INSERT', __FILE__);
            return false;
        }

        /*
         * Save dating profile
         */
        $datingProfile = new Dating_profile();
        
        $datingProfile->id = $id;
        $datingProfile->created = common_sql_now();
        
        $datingProfile->firstname = $firstname;
        $datingProfile->lastname = $lastname;
        $datingProfile->address_1 = $address_1;
        $datingProfile->city = $city;
        $datingProfile->state = $state;
        $datingProfile->country = $country;
        $datingProfile->postcode = $postcode;
        $datingProfile->bio = $bio;
        $datingProfile->birthdate = $birthdate;
        $datingProfile->sex = $sex;
        $datingProfile->partner_sex = $partner_sex;
        $datingProfile->interested_in = $interested_in;
        $datingProfile->profession = $profession;
        $datingProfile->headline = $headline;
        $datingProfile->height = $height;
        $datingProfile->hair = $hair;
        $datingProfile->body_type = $body_type;
        $datingProfile->ethnicity = $ethnicity;
        $datingProfile->eye_colour = $eye_colour;
        $datingProfile->marital_status = $marital_status;
        $datingProfile->have_children = $have_children;
        $datingProfile->smoke = $smoke;
        $datingProfile->drink = $drink;
        $datingProfile->religion = $religion;
        $datingProfile->education = $education;
        $datingProfile->politics = $politics;
        $datingProfile->best_feature = $best_feature;
        $datingProfile->body_art = $body_art;
        $datingProfile->fun = $fun;
        $datingProfile->fav_spot = $fav_spot;
        $datingProfile->fav_media = $fav_media;
        $datingProfile->first_date = $first_date;
        $datingProfile->languages = $languages;
        
        $result = $datingProfile->insert();
        if (!$result) {
            common_log_db_error($datingProfile, 'INSERT', __FILE__);
            return false;
        }
        
        /*
         * Save interests
         */
        $result = $datingProfile->setInterestTags($interests);
        if (!$result) {
            common_log_db_error($datingProfile, 'INSERT', __FILE__);
            return false;
        }
        
        /*
         * Save user
         */
        $user = new User();

        $user->id = $id;
        $user->nickname = $nickname;
        $user->post_privately = true;

        if ($password) { # may not have a password for OpenID users
            $user->password = common_munge_password($password, $id);
        }

        # Users who respond to invite email have proven their ownership of that address

        if ($code) {
            $invite = Invitation::staticGet($code);
            if ($invite && $invite->address && $invite->address_type == 'email' && $invite->address == $email) {
                $user->email = $invite->address;
            }
        }

        $inboxes = common_config('inboxes', 'enabled');

        if ($inboxes === true || $inboxes == 'transitional') {
            $user->inboxed = 1;
        }

        $user->created = common_sql_now();
        $user->uri = common_user_uri($user);

        $result = $user->insert();

        if (!$result) {
            common_log_db_error($user, 'INSERT', __FILE__);
            return false;
        }

        # Everyone is subscribed to themself

        $subscription = new Subscription();
        $subscription->subscriber = $user->id;
        $subscription->subscribed = $user->id;
        $subscription->created = $user->created;

        $result = $subscription->insert();

        if (!$result) {
            common_log_db_error($subscription, 'INSERT', __FILE__);
            return false;
        }

        if ($email && !$user->email) {

            $confirm = new Confirm_address();
            $confirm->code = common_confirmation_code(128);
            $confirm->user_id = $user->id;
            $confirm->address = $email;
            $confirm->address_type = 'email';

            $result = $confirm->insert();
            if (!$result) {
                common_log_db_error($confirm, 'INSERT', __FILE__);
                return false;
            }
        }

        if ($code && $user->email) {
            $user->emailChanged();
        }

        $profile->query('COMMIT');

        if ($email && !$user->email) {
            mail_confirm_address($user, $confirm->code, $profile->nickname, $email);
        }

        return $user;
    }

    # Things we do when the email changes

    function emailChanged()
    {

        $invites = new Invitation();
        $invites->address = $this->email;
        $invites->address_type = 'email';

        if ($invites->find()) {
            while ($invites->fetch()) {
                $other = User::staticGet($invites->user_id);
                subs_subscribe_to($other, $this);
            }
        }
    }

    function hasFave($notice)
    {
        $cache = common_memcache();

        # XXX: Kind of a hack.
        if ($cache) {
            # This is the stream of favorite notices, in rev chron
            # order. This forces it into cache.
            $faves = $this->favoriteNotices(0, NOTICE_CACHE_WINDOW);
            $cnt = 0;
            while ($faves->fetch()) {
                if ($faves->id < $notice->id) {
                    # If we passed it, it's not a fave
                    return false;
                } else if ($faves->id == $notice->id) {
                    # If it matches a cached notice, then it's a fave
                    return true;
                }
                $cnt++;
            }
            # If we're not past the end of the cache window,
            # then the cache has all available faves, so this one
            # is not a fave.
            if ($cnt < NOTICE_CACHE_WINDOW) {
                return false;
            }
            # Otherwise, cache doesn't have all faves;
            # fall through to the default
        }
        $fave = Fave::pkeyGet(array('user_id' => $this->id,
                                    'notice_id' => $notice->id));
        return ((is_null($fave)) ? false : true);
    }
    function mutuallySubscribed($other)
    {
        return $this->isSubscribed($other) &&
          $other->isSubscribed($this);
    }

    function mutuallySubscribedUsers()
    {

        # 3-way join; probably should get cached
    	$UT = common_config('db','type')=='pgsql'?'"user"':'user';
        $qry = "SELECT $UT.* " .
          "FROM subscription sub1 JOIN $UT ON sub1.subscribed = $UT.id " .
          "JOIN subscription sub2 ON $UT.id = sub2.subscriber " .
          'WHERE sub1.subscriber = %d and sub2.subscribed = %d ' .
          "ORDER BY $UT.nickname";
        $user = new User();
        $user->query(sprintf($qry, $this->id, $this->id));

        return $user;
    }

    function getReplies($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0, $since=null)
    {
        $qry =
          'SELECT notice.* ' .
          'FROM notice JOIN reply ON notice.id = reply.notice_id ' .
          'WHERE reply.profile_id = %d ';
        return Notice::getStream(sprintf($qry, $this->id),
                                 'user:replies:'.$this->id,
                                 $offset, $limit, $since_id, $before_id, null, $since);
    }

    function getNotices($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0, $since=null)
    {
        $profile = $this->getProfile();
        if (!$profile) {
            return null;
        } else {
            return $profile->getNotices($offset, $limit, $since_id, $before_id);
        }
    }

    function favoriteNotices($offset=0, $limit=NOTICES_PER_PAGE)
    {
        $qry =
          'SELECT notice.* ' .
          'FROM notice JOIN fave ON notice.id = fave.notice_id ' .
          'WHERE fave.user_id = %d ';
        return Notice::getStream(sprintf($qry, $this->id),
                                 'user:faves:'.$this->id,
                                 $offset, $limit);
    }

    /**
     * Retrieve notices including friends notices. 
     * User must be logged in to view notices if the dating site is enabled, if the user logged in is not $this user
     * then filter the messages to only the ones that the logged in user can view.
     *
     * @param unknown_type $offset
     * @param unknown_type $limit
     * @param unknown_type $since_id
     * @param unknown_type $before_id
     * @param unknown_type $since
     * @return unknown
     */
    function noticesWithFriends($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $before_id=0, $since=null)
    {
        /*
         * Disabled, this is checked in action files.
         * Probably not best form to throw client Error in this class file, mixes return values up
        if (common_config('profile', 'enable_dating')) {
            $cur = common_current_user();

            //Belt and braces - this is checked in action files.
            if (!$cur) {
                $this->clientError(_('Only logged in users can access notices.'),403);
                return;
            }
        }
        */
        
        $enabled = common_config('inboxes', 'enabled');

        # Complicated code, depending on whether we support inboxes yet
        # XXX: make this go away when inboxes become mandatory

        if ($enabled === false ||
            ($enabled == 'transitional' && $this->inboxed == 0)) {

            $qry =
              'SELECT notice.* ' .
              'FROM notice JOIN subscription ON notice.profile_id = subscription.subscribed ' .
              'WHERE subscription.subscriber = %d ';
            
            if ($cur && $cur->id !== $this->id) {
                $qry .= 'AND notice.profile_id = %d ';
            }
            
            $order = null;
        } else if ($enabled === true ||
               ($enabled == 'transitional' && $this->inboxed == 1)) {

            $qry =
              'SELECT notice.* ' .
              'FROM notice JOIN notice_inbox ON notice.id = notice_inbox.notice_id ' .
              'WHERE notice_inbox.user_id = %d ';
            
           if ($cur && $cur->id !== $this->id) {
                $qry .= 'AND notice.profile_id = %d ';
            }
            
            # NOTE: we override ORDER
            $order = null;
        }

        if ($cur && $cur->id !== $this->id) {
            return Notice::getStream(sprintf($qry, $cur->id, $this->id),
                                 'user:notices_with_friends:' . $this->id,
                                 $offset, $limit, $since_id, $before_id,
                                 $order, $since);
        }
        return Notice::getStream(sprintf($qry, $this->id),
                                 'user:notices_with_friends:' . $this->id,
                                 $offset, $limit, $since_id, $before_id,
                                 $order, $since);
    }

    function blowFavesCache()
    {
        $cache = common_memcache();
        if ($cache) {
            # Faves don't happen chronologically, so we need to blow
            # ;last cache, too
            $cache->delete(common_cache_key('user:faves:'.$this->id));
            $cache->delete(common_cache_key('user:faves:'.$this->id).';last');
        }
    }

    function getSelfTags()
    {
        return Profile_tag::getTags($this->id, $this->id);
    }

    function setSelfTags($newtags)
    {
        return Profile_tag::setTags($this->id, $this->id, $newtags);
    }

    function block($other)
    {

        # Add a new block record

        $block = new Profile_block();

        # Begin a transaction

        $block->query('BEGIN');

        $block->blocker = $this->id;
        $block->blocked = $other->id;

        $result = $block->insert();

        if (!$result) {
            common_log_db_error($block, 'INSERT', __FILE__);
            return false;
        }

        # Cancel their subscription, if it exists

        $sub = Subscription::pkeyGet(array('subscriber' => $other->id,
                                           'subscribed' => $this->id));

        if ($sub) {
            $result = $sub->delete();
            if (!$result) {
                common_log_db_error($sub, 'DELETE', __FILE__);
                return false;
            }
        }
        
        //Cancel any pending subscriptions
        if (common_config('profile', 'enable_dating')) {
            $sub = Pending_subscription::pkeyGet(array('subscriber' => $other->id,
                                                       'subscribed' => $this->id));
    
            if ($sub) {
                $result = $sub->delete();
                if (!$result) {
                    common_log_db_error($sub, 'DELETE', __FILE__);
                    return false;
                }
            }
        }

        $block->query('COMMIT');

        return true;
    }

    function unblock($other)
    {

        # Get the block record

        $block = Profile_block::get($this->id, $other->id);

        if (!$block) {
            return false;
        }

        $result = $block->delete();

        if (!$result) {
            common_log_db_error($block, 'DELETE', __FILE__);
            return false;
        }

        return true;
    }

    function isMember($group)
    {
        $profile = $this->getProfile();
        return $profile->isMember($group);
    }

    function isAdmin($group)
    {
        $profile = $this->getProfile();
        return $profile->isAdmin($group);
    }

    function getGroups($offset=0, $limit=null, $public = false)
    {
        $qry =
          'SELECT user_group.* ' .
          'FROM user_group JOIN group_member '.
          'ON user_group.id = group_member.group_id ' .
          'WHERE group_member.profile_id = %d ' .
          'ORDER BY group_member.created DESC ';

        if ($public) {
            $qry = <<<EOS
SELECT user_group.* 
FROM user_group JOIN group_member 
ON user_group.id = group_member.group_id 
WHERE group_member.profile_id = %d 
AND user_group.is_private = 0 
ORDER BY group_member.created DESC 
EOS;
        }
        
        if ($offset) {
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        $groups = new User_group();

        $cnt = $groups->query(sprintf($qry, $this->id));

        return $groups;
    }

    function getSubscriptions($offset=0, $limit=null)
    {
        $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscribed ' .
          'WHERE subscription.subscriber = %d ' .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $profile = new Profile();

        $profile->query(sprintf($qry, $this->id));

        return $profile;
    }

    function getSubscribers($offset=0, $limit=null)
    {
        $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscriber ' .
          'WHERE subscription.subscribed = %d ' .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

        if ($offset) {
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        $profile = new Profile();

        $cnt = $profile->query(sprintf($qry, $this->id));

        return $profile;
    }
    
    function getPendingSubscribers($offset=0, $limit=null)
    {

        //TODO frank: do I need to check if dating site is enabled here and return an error?
        
        $qry = <<<EOS
SELECT p.* 
FROM profile p 
JOIN pending_subscription ps ON p.id = ps.subscriber 
WHERE ps.subscribed = %d 
AND ps.subscribed != ps.subscriber 
ORDER BY ps.created DESC    
EOS;
        
        if ($offset) {
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        $profile = new Profile();

        $cnt = $profile->query(sprintf($qry, $this->id));

        return $profile;
    }

    function getTaggedSubscribers($tag, $offset=0, $limit=null)
    {
        $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscriber ' .
          'JOIN profile_tag ON (profile_tag.tagged = subscription.subscriber ' .
          'AND profile_tag.tagger = subscription.subscribed) ' .
          'WHERE subscription.subscribed = %d ' .
          'AND profile_tag.tag = "%s" ' .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

        if ($offset) {
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }
        }

        $profile = new Profile();

        $cnt = $profile->query(sprintf($qry, $this->id, $tag));

        return $profile;
    }

    function getTaggedSubscriptions($tag, $offset=0, $limit=null)
    {
        $qry =
          'SELECT profile.* ' .
          'FROM profile JOIN subscription ' .
          'ON profile.id = subscription.subscribed ' .
          'JOIN profile_tag on (profile_tag.tagged = subscription.subscribed ' .
          'AND profile_tag.tagger = subscription.subscriber) ' .
          'WHERE subscription.subscriber = %d ' .
          'AND profile_tag.tag = "%s" ' .
          'AND subscription.subscribed != subscription.subscriber ' .
          'ORDER BY subscription.created DESC ';

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $profile = new Profile();

        $profile->query(sprintf($qry, $this->id, $tag));

        return $profile;
    }

    function hasOpenID()
    {
        $oid = new User_openid();

        $oid->user_id = $this->id;

        $cnt = $oid->find();

        return ($cnt > 0);
    }
}

