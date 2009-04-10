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

require_once('XMPPHP/XMPP.php');

/* Subscribe $user to nickname $other_nickname
  Returns true or an error message.
*/

function subs_subscribe_user($user, $other_nickname)
{

    $other = User::staticGet('nickname', $other_nickname);

    if (!$other) {
        return _('No such user.');
    }

    return subs_subscribe_to($user, $other);
}

/* Subscribe user $user to other user $other.
 * Note: $other must be a local user, not a remote profile.
 * Because the other way is quite a bit more complicated.
 */

function subs_subscribe_to($user, $other)
{

    if ($user->isSubscribed($other)) {
        return _('Already subscribed!.');
    }

    if ($other->hasBlocked($user)) {
        return _('User has blocked you.');
    }

    if (!$user->subscribeTo($other)) {
        return _('Could not subscribe.');
        return;
    }

    subs_notify($other, $user);

    $cache = common_memcache();
    if ($cache) {
        $cache->delete(common_cache_key('user:notices_with_friends:' . $user->id));
	}

    if ($other->autosubscribe && !$other->isSubscribed($user) && !$user->hasBlocked($other)) {
        
        if (!$other->subscribeTo($user)) {
            return _('Could not subscribe other to you.');
        }
        $cache = common_memcache();

        if ($cache) {
            $cache->delete(common_cache_key('user:notices_with_friends:' . $other->id));
		}

        subs_notify($user, $other);
    }

    return true;
}

/**
 * Allow the subscription of $other to $user, the subscription
 * from $other should currently be in the pending table.
 *
 * @param User $user
 * @param User $other
 */
function subs_allow_subscription($user, $other) {
    
    if ($other->isSubscribed($user)) {
        return _('Already subscribed!.');
    }

    if ($user->hasBlocked($other)) {
        return _('You have this user blocked.');
    }
    
    if (!$user->isPendingSubscription($other)) {
        return _('No pending subscriptions exist for this user.');
    }

    if (!$user->allowSubscription($other)) {
        return _('Could not subscribe.');
    }

    subs_notify($user, $other);
    
    $cache = common_memcache();
    if ($cache) {
        $cache->delete(common_cache_key('user:notices_with_friends:' . $other->id));
    }

    if ($user->autosubscribe && !$user->isSubscribed($other) && !$other->hasBlocked($user)) {
        
        if (!$user->subscribeTo($other)) {
            return _('Could not subscribe other to you.');
        }
        $cache = common_memcache();

        if ($cache) {
            $cache->delete(common_cache_key('user:notices_with_friends:' . $user->id));
        }

        subs_notify($other, $user);
    }

    return true;
}
 
function subs_notify($listenee, $listener)
{
    # XXX: add other notifications (Jabber, SMS) here
    # XXX: queue this and handle it offline
    # XXX: Whatever happens, do it in Twitter-like API, too
    //TODO frank: notification emails should reflect if subscription is pending or approved
    subs_notify_email($listenee, $listener);
}

function subs_notify_email($listenee, $listener)
{
    mail_subscribe_notify($listenee, $listener);
}

/* Unsubscribe $user from nickname $other_nickname
  Returns true or an error message.
*/

function subs_unsubscribe_user($user, $other_nickname)
{

    $other = User::staticGet('nickname', $other_nickname);

    if (!$other) {
        return _('No such user.');
    }

    return subs_unsubscribe_to($user, $other->getProfile());
}

/* Unsubscribe user $user from profile $other
 * NB: other can be a remote user. */

function subs_unsubscribe_to($user, $other)
{
    
    //For the dating site, check pending subscriptions also - must get the $otherUser object to check pending subscriptions
    $otherUser = User::staticGet('id', $other->id);
    if (common_config('profile', 'enable_dating')) {

        //If user is not subscribed to other, and other does not have a pending subscription from user
        if (!$user->isSubscribed($other) && !$otherUser->isPendingSubscription($user)) {
            return _('Not subscribed!.');
        }
    }
    else {
        if (!$user->isSubscribed($other))
            return _('Not subscribed!.');
    }

    if ($user->isSubscribed($other)) {
        $sub = DB_DataObject::factory('subscription');

        $sub->subscriber = $user->id;
        $sub->subscribed = $other->id;
    
        $sub->find(true);
    
        // note we checked for existence above
        if (!$sub->delete())
            return _('Couldn\'t delete subscription.');
    }
    
    if ($otherUser->isPendingSubscription($user)) {
        $sub = DB_DataObject::factory('pending_subscription');

        $sub->subscriber = $user->id;
        $sub->subscribed = $other->id;
    
        $sub->find(true);
    
        // note we checked for existence above
        if (!$sub->delete())
            return _('Couldn\'t delete pending subscription.');
    }

    $cache = common_memcache();

    if ($cache) {
        $cache->delete(common_cache_key('user:notices_with_friends:' . $user->id));
	}

    return true;
}

