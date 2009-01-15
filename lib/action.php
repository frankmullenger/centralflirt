<?php
/**
 * Laconica, the distributed open-source microblogging tool
 *
 * Base class for all actions (~views)
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
 * @category  Action
 * @package   Laconica
 * @author    Evan Prodromou <evan@controlyourself.ca>
 * @author    Sarven Capadisli <csarven@controlyourself.ca>
 * @copyright 2008 Control Yourself, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://laconi.ca/
 */

if (!defined('LACONICA')) {
    exit(1);
}

require_once INSTALLDIR.'/lib/noticeform.php';
require_once INSTALLDIR.'/lib/htmloutputter.php';

/**
 * Base class for all actions
 *
 * This is the base class for all actions in the package. An action is
 * more or less a "view" in an MVC framework.
 *
 * Actions are responsible for extracting and validating parameters; using
 * model classes to read and write to the database; and doing ouput.
 *
 * @category Output
 * @package  Laconica
 * @author   Evan Prodromou <evan@controlyourself.ca>
 * @author   Sarven Capadisli <csarven@controlyourself.ca>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://laconi.ca/
 *
 * @see      HTMLOutputter
 */

class Action extends HTMLOutputter // lawsuit
{
    var $args;

    /**
     * Constructor
     *
     * Just wraps the HTMLOutputter constructor.
     *
     * @param string  $output URI to output to, default = stdout
     * @param boolean $indent Whether to indent output, default true
     *
     * @see XMLOutputter::__construct
     * @see HTMLOutputter::__construct
     */

    function __construct($output='php://output', $indent=true)
    {
        parent::__construct($output, $indent);
    }

    // For initializing members of the class

    function prepare($argarray)
    {
        $this->args =& common_copy_args($argarray);
        return true;
    }

    function showPage()
    {
        $this->startHTML();
        $this->showHead();
        $this->showBody();
        $this->endHTML();
    }

    function showHead()
    {
        // XXX: attributes (profile?)
        $this->elementStart('head');
        $this->showTitle();
        $this->showStylesheets();
        $this->showScripts();
        $this->showOpenSearch();
        $this->showFeeds();
        $this->showDescription();
        $this->extraHead();
        $this->elementEnd('head');
    }

    function showTitle()
    {
        $this->element('title', null,
                       sprintf(_("%s - %s"),
                               $this->title(),
                               common_config('site', 'name')));
    }

    // SHOULD overload

    function title()
    {
        return _("Untitled page");
    }

    function showStylesheets()
    {
        $this->element('link', array('rel' => 'stylesheet',
                                     'type' => 'text/css',
                                     'href' => theme_path('display.css', 'base') . '?version=' . LACONICA_VERSION,
                                     'media' => 'screen, projection, tv'));
        $this->element('link', array('rel' => 'stylesheet',
                                     'type' => 'text/css',
                                     'href' => theme_path('thickbox.css', 'base') . '?version=' . LACONICA_VERSION,
                                     'media' => 'screen, projection, tv'));
        $this->element('link', array('rel' => 'stylesheet',
                                     'type' => 'text/css',
                                     'href' => theme_path('display.css', null) . '?version=' . LACONICA_VERSION,
                                     'media' => 'screen, projection, tv'));
        foreach (array(6,7) as $ver) {
            if (file_exists(theme_file('ie'.$ver.'.css'))) {
                // Yes, IE people should be put in jail.
                $this->comment('[if lte IE '.$ver.']><link rel="stylesheet" type="text/css" '.
                               'href="'.theme_path('ie'.$ver.'.css').'?version='.LACONICA_VERSION.'" /><![endif]');
            }
        }
    }

    function showScripts()
    {
        $this->element('script', array('type' => 'text/javascript',
                                       'src' => common_path('js/jquery.min.js')),
                       ' ');
        $this->element('script', array('type' => 'text/javascript',
                                       'src' => common_path('js/jquery.form.js')),
                       ' ');
        $this->element('script', array('type' => 'text/javascript',
                                       'src' => common_path('js/xbImportNode.js')),
                       ' ');
        $this->element('script', array('type' => 'text/javascript',
                                       'src' => common_path('js/util.js?version='.LACONICA_VERSION)),
                       ' ');
    }

    function showOpenSearch()
    {
        $this->element('link', array('rel' => 'search', 'type' => 'application/opensearchdescription+xml',
                                     'href' =>  common_local_url('opensearch', array('type' => 'people')),
                                     'title' => common_config('site', 'name').' People Search'));

        $this->element('link', array('rel' => 'search', 'type' => 'application/opensearchdescription+xml',
                                     'href' =>  common_local_url('opensearch', array('type' => 'notice')),
                                     'title' => common_config('site', 'name').' Notice Search'));
    }

    // MAY overload

    function showFeeds()
    {
        // does nothing by default
    }

    // SHOULD overload

    function showDescription()
    {
        // does nothing by default
    }

    // MAY overload

    function extraHead()
    {
        // does nothing by default
    }

    function showBody()
    {
        $this->elementStart('body');
        $this->elementStart('wrap');
        $this->showHeader();
        $this->showCore();
        $this->showFooter();
        $this->elementEnd('wrap');
        $this->elementEnd('body');
    }

    function showHeader()
    {
        $this->elementStart('div', array('id' => 'header'));
        $this->showLogo();
        $this->showPrimaryNav();
        $this->showSiteNotice();
        $this->showNoticeForm();
        $this->elementEnd('div');
    }

    function showLogo()
    {
        $this->elementStart('address', array('id' => 'site_contact',
                                              'class' => 'vcard'));
        $this->elementStart('a', array('class' => 'url home bookmark',
                                        'href' => common_local_url('public')));
        if ((isset($config['site']['logo']) && is_string($config['site']['logo']) && (strlen($config['site']['logo']) > 0))
            || file_exists(theme_file('logo.png')))
        {
            $this->element('img', array('class' => 'logo photo',
                                        'src' => isset($config['site']['logo']) ?
                                        ($config['site']['logo']) : theme_path('logo.png'),
                                        'alt' => $config['site']['name']));
        }
        $this->element('span', array('class' => 'fn org'), $config['site']['name']);
        $this->elementEnd('a');
        $this->elementEnd('address');
    }

    function showPrimaryNav()
    {
        $this->elementStart('dl', array('id' => 'site_nav_global_primary'));
        $this->element('dt', null, _('Primary site navigation'));
        $user = common_current_user();
        $this->elementStart('ul', array('id' => 'nav'));
        if ($user) {
            $this->menuItem(common_local_url('all', array('nickname' => $user->nickname)),
                             _('Home'));
        }
        $this->menuItem(common_local_url('peoplesearch'), _('Search'));
        if ($user) {
            $this->menuItem(common_local_url('profilesettings'),
                             _('Settings'));
            $this->menuItem(common_local_url('invite'),
                             _('Invite'));
            $this->menuItem(common_local_url('logout'),
                             _('Logout'));
        } else {
            $this->menuItem(common_local_url('login'), _('Login'));
            if (!common_config('site', 'closed')) {
                $this->menuItem(common_local_url('register'), _('Register'));
            }
            $this->menuItem(common_local_url('openidlogin'), _('OpenID'));
        }
        $this->menuItem(common_local_url('doc', array('title' => 'help')),
                         _('Help'));
        $this->elementEnd('ul');
        $this->elementEnd('dl');
    }

    // Revist. Should probably do an hAtom pattern here
    function showSiteNotice()
    {
        $this->elementStart('dl', array('id' => 'site_notice',
                                        'class' => 'system_notice'));
        $this->element('dt', null, _('Site notice'));
        $this->elementStart('dd', null);
        // Output a bunch of paragraphs here
        $this->elementEnd('dd');
    }

    // MAY overload if no notice form needed... or direct message box????

    function showNoticeForm()
    {
        $notice_form = new NoticeForm();
        $notice_form->show();
    }

    function showCore()
    {
        $this->elementStart('div', array('class' => 'core'));
        $this->showLocalNav();
        $this->showContentBlock();
        $this->showAside();
        $this->elementEnd('div');
    }

    // SHOULD overload

    function showLocalNav()
    {
        // does nothing by default
    }

    function showContentBlock()
    {
        $this->elementStart('div', array('id' => 'content'));
        $this->showPageTitle();
        $this->showPageNotice();
        $this->showContent();
        $this->elementEnd('div');
    }

    function showPageTitle() {
        $this->element('h1', NULL, $this->title());
    }

    // SHOULD overload (unless there's not a notice)

    function showPageNotice()
    {
        $this->elementStart('dl', array('id' => 'page_notice',
                                        'class' => 'system_notice'));
        $this->element('dt', null, _('Page notice'));
        $this->elementStart('dd', null);
        // Output a bunch of paragraphs here
        $this->elementEnd('dd');
    }

    // MUST overload

    function showContent()
    {
        // show the actual content (forms, lists, whatever)
        $this->elementStart('div', array('id' => 'content_inner'));
        $this->elementEnd('div');
    }

    function showAside()
    {
        $this->showExportData();
        $this->showSections();
    }

    // MAY overload if there are feeds

    function showExportData()
    {
        // is there structure to this?
        // list of (visible!) feed links
        // can we reuse list of feeds from showFeeds() ?
    }

    // SHOULD overload

    function showSections() {
        // for each section, show it
    }

    function showFooter()
    {
        $this->elementStart('div', array('id' => 'footer'));
        $this->showSecondaryNav();
        $this->showLicenses();
        $this->elementEnd('div');
    }

    function showSecondaryNav()
    {
        $this->elementStart('ul', array('id' => 'nav_sub'));
        $this->menuItem(common_local_url('doc', array('title' => 'help')),
                         _('Help'));
        $this->menuItem(common_local_url('doc', array('title' => 'about')),
                         _('About'));
        $this->menuItem(common_local_url('doc', array('title' => 'faq')),
                         _('FAQ'));
        $this->menuItem(common_local_url('doc', array('title' => 'privacy')),
                         _('Privacy'));
        $this->menuItem(common_local_url('doc', array('title' => 'source')),
                         _('Source'));
        $this->menuItem(common_local_url('doc', array('title' => 'contact')),
                         _('Contact'));
        $this->elementEnd('ul');
    }

    function showLicenses()
    {
        $this->elementStart('dl', array('id' => 'licenses'));
        $this->showLaconicaLicense();
        $this->showContentLicense();
        $this->elementEnd('dl');
    }

    function showLaconicaLicense()
    {
        $this->element('dt', array('id' => 'site_laconica_license'), _('Laconica software license'));
        $this->elementStart('dd', null);
        if (common_config('site', 'broughtby')) {
            $instr = _('**%%site.name%%** is a microblogging service brought to you by [%%site.broughtby%%](%%site.broughtbyurl%%). ');
        } else {
            $instr = _('**%%site.name%%** is a microblogging service. ');
        }
        $instr .= sprintf(_('It runs the [Laconica](http://laconi.ca/) microblogging software, version %s, available under the [GNU Affero General Public License](http://www.fsf.org/licensing/licenses/agpl-3.0.html).'), LACONICA_VERSION);
        $output = common_markup_to_html($instr);
        common_raw($output);
        $this->elementEnd('dd');
        // do it
    }

    function showContentLicense()
    {
        $this->element('dt', array('id' => 'site_content_license'), _('Laconica software license'));
        $this->elementStart('dd', array('id' => 'site_content_license_cc'));
        $this->elementStart('p');
        common_text(_('Unless otherwise specified, contents of this site are copyright by the contributors and available under the '));
        $this->element('a', array('class' => 'license',
                                  'rel' => 'external license',
                                  'href' => $config['license']['url']),
                       $config['license']['title']);
        common_text(_('. Contributors should be attributed by full name or nickname.'));
        $this->elementEnd('p');
        $this->element('img', array('id' => 'license_cc',
                                    'src' => $config['license']['image'],
                                    'alt' => $config['license']['title']));
        $this->elementEnd('dd');
    }

    // For comparison with If-Last-Modified
    // If not applicable, return null

    function last_modified()
    {
        return null;
    }

    function etag()
    {
        return null;
    }

    function is_readonly()
    {
        return false;
    }

    function arg($key, $def=null)
    {
        if (array_key_exists($key, $this->args)) {
            return $this->args[$key];
        } else {
            return $def;
        }
    }

    function trimmed($key, $def=null)
    {
        $arg = $this->arg($key, $def);
        return (is_string($arg)) ? trim($arg) : $arg;
    }

    // Note: argarray ignored, since it's now passed in in prepare()

    function handle($argarray=null)
    {

        $lm = $this->last_modified();
        $etag = $this->etag();

        if ($etag) {
            header('ETag: ' . $etag);
        }

        if ($lm) {
            header('Last-Modified: ' . date(DATE_RFC1123, $lm));
            $if_modified_since = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            if ($if_modified_since) {
                $ims = strtotime($if_modified_since);
                if ($lm <= $ims) {
                    if (!$etag ||
                        $this->_has_etag($etag, $_SERVER['HTTP_IF_NONE_MATCH'])) {
                        header('HTTP/1.1 304 Not Modified');
                        // Better way to do this?
                        exit(0);
                    }
                }
            }
        }
    }

    function _has_etag($etag, $if_none_match)
    {
        return ($if_none_match) && in_array($etag, explode(',', $if_none_match));
    }

    function boolean($key, $def=false)
    {
        $arg = strtolower($this->trimmed($key));

        if (is_null($arg)) {
            return $def;
        } else if (in_array($arg, array('true', 'yes', '1'))) {
            return true;
        } else if (in_array($arg, array('false', 'no', '0'))) {
            return false;
        } else {
            return $def;
        }
    }

    function server_error($msg, $code=500)
    {
        $action = $this->trimmed('action');
        common_debug("Server error '$code' on '$action': $msg", __FILE__);
        common_server_error($msg, $code);
    }

    function client_error($msg, $code=400)
    {
        $action = $this->trimmed('action');
        common_debug("User error '$code' on '$action': $msg", __FILE__);
        common_user_error($msg, $code);
    }

    function self_url()
    {
        $action = $this->trimmed('action');
        $args = $this->args;
        unset($args['action']);
        foreach (array_keys($_COOKIE) as $cookie) {
            unset($args[$cookie]);
        }
        return common_local_url($action, $args);
    }

    function nav_menu($menu)
    {
        $action = $this->trimmed('action');
        $this->elementStart('ul', array('id' => 'nav_views'));
        foreach ($menu as $menuaction => $menudesc) {
            $this->menuItem(common_local_url($menuaction,
                                              isset($menudesc[2]) ? $menudesc[2] : null),
                             $menudesc[0],
                             $menudesc[1],
                             $action == $menuaction);
        }
        $this->elementEnd('ul');
    }

    function common_show_header($pagetitle, $callable=null, $data=null, $headercall=null)
    {
        global $config, $xw;
        global $action; /* XXX: kind of cheating here. */

        common_start_html();

        $this->elementStart('head');

        if ($callable) {
            if ($data) {
                call_user_func($callable, $data);
            } else {
                call_user_func($callable);
            }
        }
        $this->elementEnd('head');
        $this->elementStart('body', $action);
        $this->elementStart('div', array('id' => 'wrap'));
        $this->elementStart('div', array('id' => 'content'));
    }

    // Added @id to li for some control.
    // XXX: We might want to move this to htmloutputter.php

    function menuItem($url, $text, $id=null, $title=null, $is_selected=false)
    {
        $lattrs = array();
        if ($is_selected) {
            $lattrs['class'] = 'current';
        }

        $this->elementStart('li', (is_null($id)) ? null : array('id' => $id),  $lattrs);
        $attrs['href'] = $url;
        if ($title) {
            $attrs['title'] = $title;
        }
        $this->element('a', $attrs, $text);
        $this->elementEnd('li');
    }

    // Does a little before-after block for next/prev page

    function pagination($have_before, $have_after, $page, $action, $args=null)
    {
        if ($have_before || $have_after) {
            $this->elementStart('div', array('class' => 'pagination'));
            $this->elementStart('dl', null);
            $this->element('dt', null, _('Pagination'));
            $this->elementStart('dd', null);
            $this->elementStart('ul', array('class' => 'nav'));
        }

        if ($have_before) {
            $pargs = array('page' => $page-1);
            $newargs = ($args) ? array_merge($args,$pargs) : $pargs;

            $this->elementStart('li', array('class' => 'nav_prev'));
            $this->element('a', array('href' => common_local_url($action, $newargs), 'rel' => 'prev'),
                           _('After'));
            $this->elementEnd('li');
        }

        if ($have_after) {
            $pargs = array('page' => $page+1);
            $newargs = ($args) ? array_merge($args,$pargs) : $pargs;
            $this->elementStart('li', array('class' => 'nav_next'));
            $this->element('a', array('href' => common_local_url($action, $newargs), 'rel' => 'next'),
                           _('Before'));
            $this->elementEnd('li');
        }

        if ($have_before || $have_after) {
            $this->elementEnd('ul');
            $this->elementEnd('dd');
            $this->elementEnd('dl');
            $this->elementEnd('div');
        }
    }
}
