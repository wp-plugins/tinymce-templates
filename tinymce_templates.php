<?php
/*
Plugin Name: TinyMCE Templates
Plugin URI: http://firegoby.theta.ne.jp/wp/tinymce_templates
Description: Manage & Add Tiny MCE template.
Author: Takayuki Miyauchi (THETA NETWORKS Co,.Ltd)
Version: 0.31
Author URI: http://firegoby.theta.ne.jp/
*/

/*
Copyright (c) 2010 Takayuki Miyauchi (THETA NETWORKS Co,.Ltd).

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

define('TINYMCE_TEMPLATES_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
define('TINYMCE_TEMPLATES_TABLE', $wpdb->prefix.'mce_template');
define('TINYMCE_TEMPLATES_DOMAIN', 'tinymce_templates');

require_once(dirname(__FILE__).'/includes/addrewriterules.class.php');
require_once(dirname(__FILE__).'/includes/mceplugins.class.php');
require_once(dirname(__FILE__).'/includes/TinyMCETemplate.class.php');
require_once(dirname(__FILE__).'/includes/MceTemplatesAdmin.class.php');

$MceTemplates = new MceTemplates();
register_activation_hook (__FILE__, array(&$MceTemplates, 'activation'));
register_deactivation_hook (__FILE__, array(&$MceTemplates, 'deactivation'));

class MceTemplates{

//
// construct
//
    function __construct()
    {
        add_action('admin_menu', array(&$this, 'loadAdmin'));
    }

    public function activation()
    {
        global $wpdb;
        $sql = "CREATE TABLE {$wpdb->prefix}mce_template (
            `ID` varchar(32) NOT NULL,
            `name` varchar(50) NOT NULL,
            `desc` varchar(100) NOT NULL,
            `html` text NOT NULL,
            `share` tinyint(1) unsigned NOT NULL,
            `author` bigint(20) unsigned NOT NULL,
            `modified` timestamp NOT NULL,
            UNIQUE KEY ID (`ID`))
            ENGINE = MYISAM 
            CHARACTER SET utf8 
            COLLATE utf8_unicode_ci;
        ";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function deactivation()
    {
        // nothing to do
    }

//
// add admin menu
//
    public function loadAdmin()
    {
        load_plugin_textdomain(
            TINYMCE_TEMPLATES_DOMAIN, 
            PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/langs', 
            dirname(plugin_basename(__FILE__)).'/langs'
        );

        add_menu_page(
            __('tinyMCE Templates', TINYMCE_TEMPLATES_DOMAIN),
            __('Templates', TINYMCE_TEMPLATES_DOMAIN),
            'edit_pages',
            'edittemplates',
            '',
            TINYMCE_TEMPLATES_PLUGIN_URL.'/img/icon.png'
        );
        add_submenu_page(
            'edittemplates',
            __('Edit Templates', TINYMCE_TEMPLATES_DOMAIN),
            __('Edit', TINYMCE_TEMPLATES_DOMAIN),
            'edit_pages',
            'edittemplates',
            array(&$this, 'adminPage')
        );
        add_submenu_page(
            'edittemplates',
            __('Add New Templates', TINYMCE_TEMPLATES_DOMAIN),
            __('Add New', TINYMCE_TEMPLATES_DOMAIN),
            'edit_pages',
            'addnewtemplates',
            array(&$this, 'adminPage')
        );
    }


//
// display mcetemplates list
//
    public function adminPage()
    {
        new MceTemplatesAdmin();
    }

}
?>
