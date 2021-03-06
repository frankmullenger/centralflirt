<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Form for leaving a group
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
 * @category  Form
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Sarven Capadisli <csarven@controlyourself.ca>
 * @copyright 2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/form.php';

/**
 * Form for leaving a group
 *
 * @category Form
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Sarven Capadisli <csarven@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 *
 * @see      UnsubscribeForm
 */

class LeaveForm extends Form
{
    /**
     * group for user to leave
     */

    var $group = null;
    
    //Profile of user leaving group
    var $profile = null;

    /**
     * Constructor
     *
     * @param HTMLOutputter $out   output channel
     * @param group         $group group to leave
     */

    function __construct($out=null, $group=null, $profile = null)
    {
        parent::__construct($out);

        $this->group = $group;
        $this->profile = $profile;
    }

    /**
     * ID of the form
     *
     * @return string ID of the form
     */

    function id()
    {
        if (common_config('profile', 'enable_dating') && !is_null($this->profile)) {
            return 'group-leave-' . $this->group->id.'-'.$this->profile->id;
        }
        return 'group-leave-' . $this->group->id;
    }

    /**
     * class of the form
     *
     * @return string of the form class
     */

    function formClass()
    {
        return 'form_group_leave';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */

    function action()
    {
        //For adding subscribers to an individual group pass the usernick of user that owns the group (cur)
        if (common_config('profile', 'enable_dating') && !is_null($this->profile)) {
            $cur = common_current_user();
            return common_local_url('leavegroup',
                                array('nickname' => $this->group->nickname, 'usernick' => $cur->nickname));
        }
        return common_local_url('leavegroup',
                                array('nickname' => $this->group->nickname));
    }
    
    function formData() {
        //For adding subscribers to an individual group
        if (common_config('profile', 'enable_dating') && !is_null($this->profile)) {
            $this->out->hidden('user_to_remove', $this->profile->id);
        }
    }

    /**
     * Action elements
     *
     * @return void
     */

    function formActions()
    {
        //For removing subscribers to an individual group
        if (common_config('profile', 'enable_dating') && !is_null($this->profile)) {
            $this->out->submit('submit', _('Remove'));
        }
        else {
            $this->out->submit('submit', _('Leave'));
        }
    }
}
