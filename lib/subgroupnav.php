<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Local navigation for subscriptions group of pages
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 *
 * @category  Subs
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/widget.php';

/**
 * Local nav menu for subscriptions, subscribers
 *
 * @category Subs
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class SubGroupNav extends Widget
{
    var $action = null;
    var $user = null;

    /**
     * Construction
     *
     * @param Action $action current action, used for output
     */

    function __construct($action=null, $user=null)
    {
        parent::__construct($action);
        $this->action = $action;
        $this->user = $user;
    }

    /**
     * Show the menu
     *
     * @return void
     */

    function show()
    {
        $cur = common_current_user();
        $action = $this->action->trimmed('action');

        $this->out->elementStart('ul', array('class' => 'nav'));

        
        if ($this->user->id == $cur->id && common_logged_in()) {
            
            $this->out->menuItem(common_local_url('subscribers',
                                              array('nickname' =>
                                                    $this->user->nickname)),
                             _('Subscribers'),
                             sprintf(_('People subscribed to %s'),
                                     $this->user->nickname),
                             $action == 'subscribers',
                             'nav_subscribers');
            
            if (common_config('profile', 'enable_dating')) {
                $this->out->menuItem(common_local_url('pendingsubscribers',
                                              array('nickname' =>
                                                    $this->user->nickname)),
                             _('Requests'),
                             sprintf(_('People requesting to subscribe to %s'),
                                     $this->user->nickname),
                             $action == 'pendingsubscribers',
                             'nav_pendingsubscribers');
            }
            
            $this->out->menuItem(common_local_url('subscriptions',
                                              array('nickname' =>
                                                    $this->user->nickname)),
                             _('Subscriptions'),
                             sprintf(_('People %s subscribes to'),
                                     $this->user->nickname),
                             $action == 'subscriptions',
                             'nav_subscriptions');
                             
            //If private group pass admin nickname as usernick in home url
            if (common_config('profile', 'enable_dating')) {
                $this->out->menuItem(common_local_url('groups', array('usernick' => $this->user->nickname)),
                                     _('Groups'),
                                     _('Your private groups'),
                                     $action == 'groups');
                                     
                $this->out->menuItem(common_local_url('datingsearch', array()),
                                     _('Search'),
                                     _('Search for friends'),
                                     $action == 'datingsearch');
            }
            
            $this->out->menuItem(common_local_url('invite'),
                                 _('Invite'),
                                 sprintf(_('Invite friends and colleagues to join you on %s'),
                                         common_config('site', 'name')),
                                 $action == 'invite',
                                 'nav_invite');
        }
        elseif (common_config('profile', 'enable_dating')) {
            $this->out->menuItem(common_local_url('datingsearch', array()),
                                     _('Search'),
                                     _('Search for friends'),
                                     $action == 'datingsearch');
        }
        
        if (!common_config('profile', 'enable_dating')) {
            $this->out->menuItem(common_local_url('usergroups',
                                                  array('nickname' =>
                                                        $this->user->nickname)),
                                 _('Groups'),
                                 sprintf(_('Groups %s is a member of'),
                                         $this->user->nickname),
                                 $action == 'usergroups',
                                 'nav_usergroups');
        }
        
        
        $this->out->elementEnd('ul');
    }

}
