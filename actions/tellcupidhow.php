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

        $this->elementStart('div', array('id' => 'content_inner', 'class' => 'entry-content'));
        $this->element('h2', null, _('How Does This Work?'));
        $content = <<<EOS
<p>
Tell Cupid is the application for finding and flirting with people in your city.
</p>
<p>
Have a crush? <br />Saw someone you like and want to find them? <br />Looking for love?
</p>
<p>
Tell Cupid in 140 characters and he will post your message to dating site <a href="http://centralflirt.com" target="_blank">Central Flirt</a> where thousands of singles can see it.
</p>
EOS;
        $this->raw($content);
        
        $this->element('h2', null, _('Examples'));
        $content = <<<EOS
<p>
<ul>
<li>
<em>@public</em> our eyes met above a copy of the metro, northern line London. You had long brown hair, I was in jeans and brown shirt...
</li>
<li>
<em>@public</em> looking for a special man, sensitive, 22-27, tall in and around Sydney, Australia...
</li>
<li>
<em>@public</em> nice non scene guy looking for same, going to fabric tonight see you there!
</li>
</ul>
</p>
EOS;
        $this->raw($content);
        
        $this->element('h2', null, _('What Does @public Mean?'));
        $content = <<<EOS
<p>
By putting @public in your message ensures your message will be posted to the public timeline on the <a href="http://centralflirt.com" target="_blank">Central Flirt</a> home page, which means 
a lot more people will see your message.
</p>
EOS;
        $this->raw($content);
        
        $this->element('h2', null, _('What Next?'));
        $content = <<<EOS
<p>
Watch the public timeline on the <a href="http://centralflirt.com" target="_blank">Central Flirt</a> home page for someone to respond to your message, maybe your match is watching!
You can view the Central Flirt home page by clicking on the <a href="http://apps.facebook.com/tellcupid/index.php">home</a> tab above.
</p>
<p>
Or you can sign up on <a href="http://centralflirt.com/main/register" target="_blank">Central Flirt</a> and start flirting with other singles from your own account where you can keep your messages private 
and respond to other users.
</p>
EOS;
        $this->raw($content);
        $this->elementEnd('div');

    }
    
    function title() 
    {
        return _("How It Works");
    }
    
    // Make this into a widget later
    function showLocalNav()
    {
        $this->showLocalCupidNav();
    } 

}
