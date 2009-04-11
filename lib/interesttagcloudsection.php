<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Personal tag cloud section
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
 * @category  Widget
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @copyright 2009 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

/**
 * Interest tag cloud section
 *
 * @category Widget
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 */

class InterestTagCloudSection extends TagCloudSection
{
    var $user = null;

    function __construct($out=null, $user=null)
    {
        parent::__construct($out);
        $this->user = $user;
    }

    function title()
    {
        return sprintf(_('Popular interests'));
    }

    function getTags()
    {
            $qry = <<<EOS
SELECT dating_profile_tag.tag, 
sum(exp(-now()/%s)) as weight 
FROM dating_profile_tag 
GROUP BY dating_profile_tag.tag 
ORDER BY weight DESC 
EOS;
        
        $limit = TAGS_PER_SECTION;
        $offset = 0;

        if (common_config('db','type') == 'pgsql') {
            $qry .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        } else {
            $qry .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $tag = Memcached_DataObject::cachedQuery('Dating_profile_tag',
                                                 sprintf($qry, common_config('tag', 'dropoff')),
                                                 3600);
        return $tag;
    }
    
    function tagUrl($tag)
    {
        return common_local_url('interesttag', array('tag' => $tag));
    }

}
