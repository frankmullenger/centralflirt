<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Latest groups information
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
 * @category  Personal
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Sarven Capadisli <csarven@controlyourself.ca>
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/grouplist.php';

/**
 * Latest groups
 *
 * Show the latest groups on the site
 *
 * @category Personal
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class GroupsAction extends Action
{
    var $page = null;
    var $profile = null;
    public $privateGroups = false;

    function isReadOnly()
    {
        return true;
    }

    function title()
    {
        if ($this->page == 1) {
            return _("Groups");
        } else {
            return sprintf(_("Groups, page %d"), $this->page);
        }
    }

    function prepare($args)
    {
        parent::prepare($args);
        $this->page = ($this->arg('page')) ? ($this->arg('page')+0) : 1;
        return true;
    }

    function handle($args)
    {
        parent::handle($args);
        
        //Passing in nickname of user to display users personal (private) groups
        if (isset($args['usernick'])) {
            $this->privateGroups = true;
            
            if (common_config('profile', 'enable_dating')) {
                
                //If a user is not logged in then do not show these pages
                $cur = common_current_user();
                $usernick = common_canonical_nickname($this->arg('usernick'));
                $user = User::staticGet('nickname', $usernick);
                
                if (!$cur || $cur->id != $user->id) {
                    
                    $this->clientError(_('Only logged in users can access this page.'),403);
                    return;
                }
            }
        }
        
        $this->showPage();
    }

    function showLocalNav()
    {
        if ($this->privateGroups) {
            $nav = new PersonalGroupNav($this);
        }
        else {
            $nav = new PublicGroupNav($this);
        }
        $nav->show();
    }

    function showPageNotice()
    {
        if ($this->privateGroups) {
            $notice =
              sprintf(_('%%%%site.name%%%% private groups let you sort your followers ' .
                        'into groups so that you can message a select group of your followers ' .
                        'at once. You can send messages to one of your groups using the ' .
                        'syntax "!groupname".'));
            $this->elementStart('div', 'instructions');
            $this->raw(common_markup_to_html($notice));
            $this->elementEnd('div');
            return;
        }
        
        $notice =
          sprintf(_('%%%%site.name%%%% groups let you find and talk with ' .
                    'people of similar interests. After you join a group ' .
                    'you can send messages to all other members using the ' .
                    'syntax "!groupname". Don\'t see a group you like? Try ' .
                    '[searching for one](%%%%action.groupsearch%%%%) or ' .
                    '[start your own!](%%%%action.newgroup%%%%)'));
        $this->elementStart('div', 'instructions');
        $this->raw(common_markup_to_html($notice));
        $this->elementEnd('div');
    }

    function showContent()
    {
        if ($this->privateGroups) {
            $this->showPrivateGroups();
        }
        else {
            $this->showPublicGroups();
        }
    }
    
    private function showPublicGroups() {
        
        $this->elementStart('p', array('id' => 'new_group'));
        $this->element('a', array('href' => common_local_url('newgroup'),
                                  'class' => 'more'),
                       _('Create a new group'));
        $this->elementEnd('p');

        $offset = ($this->page-1) * GROUPS_PER_PAGE;
        $limit =  GROUPS_PER_PAGE + 1;

        $groups = new User_group();
        $groups->whereAdd('is_private = 0');
        $groups->orderBy('created DESC');
        $groups->limit($offset, $limit);

        if ($groups->find()) {
            $gl = new GroupList($groups, null, $this);
            $cnt = $gl->show();
        }

        $this->pagination($this->page > 1, $cnt > GROUPS_PER_PAGE,
                          $this->page, 'groups');
    }
    
    /**
     * To retireve the groups that are private and owned by current logged in user.
     */
    private function showPrivateGroups() {
        
        $user = common_current_user();
        
        //TODO frank: if no user logged in then throw an error or do not show any groups...
        
        $this->elementStart('p', array('id' => 'new_group'));
        $this->element('a', array('href' => common_local_url('newgroup', array('usernick' => $user->nickname)),
                                  'class' => 'more'),
                       _('Create a new group'));
        $this->elementEnd('p');

        $offset = ($this->page-1) * GROUPS_PER_PAGE;
        $limit =  GROUPS_PER_PAGE + 1;

        $groups = new User_group();
        $groups->whereAdd('is_private = 1');
        $groups->whereAdd("admin_nickname = '".$user->nickname."'");
        $groups->orderBy('created DESC');
        $groups->limit($offset, $limit);

        if ($groups->find()) {
            $gl = new GroupList($groups, null, $this);
            $cnt = $gl->show();
        }

        $this->pagination($this->page > 1, $cnt > GROUPS_PER_PAGE,
                          $this->page, 'groups');
    }

    function showSections()
    {
        $gbp = new GroupsByPostsSection($this);
        $gbp->show();
        $gbm = new GroupsByMembersSection($this);
        $gbm->show();
    }
}
