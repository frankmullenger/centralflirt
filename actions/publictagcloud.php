<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Public tag cloud for notices
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
 * @category  Public
 * @package   Laconica
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2008 Mike Cochrane
 * @copyright 2008-2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) { exit(1); }

define('TAGS_PER_PAGE', 100);

/**
 * Public tag cloud for notices
 *
 * @category Personal
 * @package  Laconica
 * @author    Mike Cochrane <mikec@mikenz.geek.nz>
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2008 Mike Cochrane
 * @copyright 2008-2009 Control Yourself, Inc.
 * @link     http://laconi.ca/
 */

class PublictagcloudAction extends Action
{
    function isReadOnly()
    {
        return true;
    }

    function title()
    {
        return _('Public tag cloud');
    }

    function showPageNotice()
    {
                      
       if (common_config('profile', 'enable_dating')) {
           $this->element('p', 'instructions',
                       sprintf(_('These are most popular recent tags included in public notices on %s. '),
                               common_config('site', 'name')));
                               
           $this->element('p', 'help',
                       _('You can add as many tags as you want to your notices by using "#tagname" in your notices.'));
           return;
       }
       
       $this->element('p', 'instructions',
                       sprintf(_('These are most popular recent tags included in public notices on %s. '.
                                 'You can add as many tags as you want to your notices including "#tagname" in your notices.'),
                               common_config('site', 'name')));
    }

    function showLocalNav()
    {
        $nav = new PublicGroupNav($this);
        $nav->show();
    }

    function handle($args)
    {
        //Disable viewing tags for the dating site
        if (common_config('profile', 'enable_dating')) {
            $this->clientError(_('Tags are disabled.'), 403);
            return;
        }
        
        parent::handle($args);
        $this->showPage();
    }

    function showContent()
    {
        
        if (!common_config('profile', 'enable_dating')) {
                
            # This should probably be cached rather than recalculated
            $tags = new Notice_tag();
    
            #Need to clear the selection and then only re-add the field
            #we are grouping by, otherwise it's not a valid 'group by'
            #even though MySQL seems to let it slide...
            $tags->selectAdd();
            $tags->selectAdd('tag');
    
            #Add the aggregated columns...
            $tags->selectAdd('max(notice_id) as last_notice_id');
            if(common_config('db','type')=='pgsql') {
                $calc='sum(exp(-extract(epoch from (now()-created))/%s)) as weight';
            } else {
                $calc='sum(exp(-(now() - created)/%s)) as weight';
            }
            $tags->selectAdd(sprintf($calc, common_config('tag', 'dropoff')));
            $tags->groupBy('tag');
            $tags->orderBy('weight DESC');
    
            $tags->limit(TAGS_PER_PAGE);
            
            $cnt = $tags->find();

        }
        else {   
            /**
             * TODO frank: look into links.ini file and use joinAdd() to avoid porblems with $cnt below
             * $notices = new Notice();
             * $tags->joinAdd($notices);
             * $tags->whereAdd('notice.is_private = 0');
             */
            
            $tags = new Notice_tag();
            $qry = <<<EOS
SELECT notice_tag.tag, 
sum(exp(-(now() - notice_tag.created)/%s)) as weight 
FROM notice_tag JOIN notice 
ON notice_tag.notice_id = notice.id 
WHERE notice.is_private = 0 
GROUP BY notice_tag.tag 
ORDER BY weight DESC 
EOS;

            $limit = TAGS_PER_PAGE;
            $offset = 0;
    
            if (common_config('db','type') == 'pgsql') {
                $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
            } else {
                $qry .= ' LIMIT ' . $offset . ', ' . $limit;
            }

            $qry = sprintf($qry, common_config('tag', 'dropoff'));
            $tags->query($qry);
            
            //TODO frank: this is not good practice, what happens when there are no public tags at all? divide by zero warning...
            $cnt = 1;
        }

        if ($cnt > 0) {
            $this->elementStart('div', array('id' => 'tagcloud',
                                             'class' => 'section'));

            $tw = array();
            $sum = 0;
            while ($tags->fetch()) {
                $tw[$tags->tag] = $tags->weight;
                $sum += $tags->weight;
            }

            ksort($tw);

            $this->elementStart('dl');
            $this->element('dt', null, _('Tag cloud'));
            $this->elementStart('dd');
            $this->elementStart('ul', 'tags xoxo tag-cloud');
            foreach ($tw as $tag => $weight) {
                $this->showTag($tag, $weight, $weight/$sum);
            }
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
            $this->elementEnd('div');
        }
    }

    function showTag($tag, $weight, $relative)
    {
        if ($relative > 0.1) {
            $rel =  'tag-cloud-7';
        } else if ($relative > 0.05) {
            $rel = 'tag-cloud-6';
        } else if ($relative > 0.02) {
            $rel = 'tag-cloud-5';
        } else if ($relative > 0.01) {
            $rel = 'tag-cloud-4';
        } else if ($relative > 0.005) {
            $rel = 'tag-cloud-3';
        } else if ($relative > 0.002) {
            $rel = 'tag-cloud-2';
        } else {
            $rel = 'tag-cloud-1';
        }

        $this->elementStart('li', $rel);
        $this->element('a', array('href' => common_local_url('tag', array('tag' => $tag))),
                       $tag);
        $this->elementEnd('li');
    }
}
