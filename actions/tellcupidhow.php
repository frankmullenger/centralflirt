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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.     If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

require_once(INSTALLDIR.'/lib/facebookaction.php');

class TellCupidHowAction extends FacebookAction
{

    function handle($args)
    {
        parent::handle($args);
        $this->showPage();
    }

    /**
     * Show the page content
     *
     * Either shows the registration form or, if registration was successful,
     * instructions for using the site.
     *
     * @return void
     */

    function showContent()
    {
        $this->element('h2', null, _('How Does This Work?'));
        $this->elementstart('p');
        $this->text('Tell Cupid is an application');
        $this->elementend('p');
        
        $content = <<<EOS
<p>
Tell Cupid is the application for finding and flirting with people in your city.
</p>
<p>
Tell Cupid about your crush and he will post the message to dating site Central Flirt where thousands of members can view it. The message you send is
automatically added to the Central Flirt home page.
</p>
EOS;
        $this->raw($content);
    }
    
    function title() 
    {
        return;
    }
    
    // Make this into a widget later
    function showLocalNav()
    {
        $this->showLocalCupidNav();
    } 

}
