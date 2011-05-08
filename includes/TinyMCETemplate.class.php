<?php

new TinyMCETemplate();

class TinyMCETemplate{

    private $list_url = null;

    function __construct()
    {
        new AddRewriteRules(
            'mce_templates.js$',
            'mce_templates',
            array(&$this, 'get_templates')
        );
        add_filter('mce_css', array(&$this, 'addStyle'));
        add_action('init', array(&$this, 'loadTinyMCEPlugin'));
    }

//
// add css tinyMCE content
//
    public function addStyle($css)
    {
        $files = preg_split("/,/", $css);
        $files[] = TINYMCE_TEMPLATES_PLUGIN_URL.'/editor.css';
        $files = array_map('trim', $files);
        return join(",", $files);
    }

//
// add button to tinyMCE editor
//
    public function add_button($buttons = array())
    {
        array_unshift($buttons, '|');
        array_unshift($buttons, 'template');
        return $buttons;
    }

//
// load tinyMCE plugin
//
    public function loadTinyMCEPlugin(){
        global $wp_rewrite;
        $plugin = TINYMCE_TEMPLATES_PLUGIN_URL.'/mce_plugins/plugins/template/editor_plugin.js';
        $path = dirname(__FILE__).'/../mce_plugins/plugins';
        $lang = $path.'/template/langs/langs.php';
        $inits = array();
        $url = home_url();
        if ($wp_rewrite->using_permalinks()) {
            $this->list_url = $url.'/mce_templates.js';
        } else {
            $this->list_url = $url.'/?mce_templates=1';
        }
        $inits['template_external_list_url'] = $this->list_url;
        new mcePlugins(
            'template',
            $plugin,
            $lang,
            array(&$this, 'add_button'),
            $inits
        );
    }

//
// return display templates as JSON
//
    public function get_templates(){
        global $wp_rewrite;
        if (get_query_var('mce_templates')) {
            global $wpdb;
            global $user_login;
            global $current_user;
            global $MceTemplates;

            if (!$user_login) {
                exit;
            }

            if( isset($_GET['id']) && strlen($_GET['id']) ){
                $sql = "select html from ".$wpdb->prefix."mce_template
                    where (`ID`=%s) and (`author`={$current_user->ID} or `share`=1)
                        order by `modified` desc";
                $sql = $wpdb->prepare($sql, $_GET['id']);
                $template = $wpdb->get_var($sql);
                if ($template) {
                    echo wpautop(stripslashes($template));
                }
                exit;
            }

            $sql = "select * from ".$wpdb->prefix."mce_template
                where `author`={$current_user->ID} or `share`=1
                    order by `modified` desc";
            $row = $wpdb->get_results($sql);

            header( 'Content-Type: application/x-javascript; charset=UTF-8' );
            echo 'var tinyMCETemplateList = [';
            $arr = array();
            if ($wp_rewrite->using_permalinks()) {
                $list_url = $this->list_url.'?';
            } else {
                $list_url = $this->list_url.'&';
            }
            foreach ($row as $tpl) {
                $ID = esc_html($tpl->ID);
                $name = $tpl->name;
                $desc = esc_html($tpl->desc);
                $arr[] = "[\"{$name}\", \"{$list_url}id={$ID}\", \"{$desc}\"]";
            }
            echo join(',', $arr);
            echo ']';
            exit;
        }
    }
}

?>
