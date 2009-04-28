<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Menu for search actions
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
 * @category  Menu
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2008 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/widget.php';

/**
 * Menu for public group of actions
 *
 * @category Output
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 *
 * @see      Widget
 */

class DocGroupNav extends Widget
{
    var $action = null;

    /**
     * Construction
     *
     * @param Action $action current action, used for output
     */

    function __construct($action=null)
    {
        parent::__construct($action);
        $this->action = $action;
    }

    /**
     * Show the menu
     *
     * @return void
     */

    function show()
    {
        $title_name = $this->action->trimmed('title');
        $this->action->elementStart('ul', array('class' => 'nav'));
        $args = array();
        if ($this->q) {
            $args['q'] = $this->q;
        }
        
        $this->out->menuItem(common_local_url('doc', array('title' => 'help')),
                        _('Help'), _('Find help'), $title_name == 'help');
        $this->out->menuItem(common_local_url('doc', array('title' => 'about')),
                        _('About'), _('About'), $title_name == 'about');
        $this->out->menuItem(common_local_url('doc', array('title' => 'faq')),
                        _('FAQ'), _('Frequently Asked Questions'), $title_name == 'faq');
        $this->out->menuItem(common_local_url('doc', array('title' => 'privacy')),
                        _('Privacy'), _('Privacy Information'), $title_name == 'privacy');
        $this->out->menuItem(common_local_url('doc', array('title' => 'contact')),
                        _('Contact'), _('Contact Us'), $title_name == 'contact');

        $this->action->elementEnd('ul');

    }
}

