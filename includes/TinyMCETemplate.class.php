<?php

new TinyMCETemplate();

class TinyMCETemplate{

    private $mce_css = null;

    function __construct()
    {
        $plugin = TINYMCE_TEMPLATES_PLUGIN_URL.'/mce_plugins/plugins/template/editor_plugin.js';
        $path = dirname(__FILE__).'/../mce_plugins/plugins';
        $lang = $path.'/template/langs/langs.php';
        $inits = array();
        $url = get_bloginfo('url');
        $inits['template_external_list_url'] = $url.'/mce_templates.js';
        $this->mce_css = TINYMCE_TEMPLATES_PLUGIN_URL.'/editor.css';
        $inits['content_css'] = $this->mce_css;
        new mcePlugins(
            'template',
            $plugin,
            $lang,
            array(&$this, 'add_button'),
            $inits
        );

        add_filter('mce_css', array(&$this, 'addCSS'));
        add_filter('query_vars', array(&$this, 'query_vars'));
        add_filter('rewrite_rules_array', array(&$this, 'rewrite_rules_array'));
        add_action('init', array(&$this, 'init'));
        add_action('wp', array(&$this, 'wp'));
    }

    public function addCSS($css)
    {
        return $css.','.$this->mce_css;
    }

    public function add_button($buttons = array())
    {
        array_unshift($buttons, '|');
        array_unshift($buttons, 'template');
        return $buttons;
    }

    public function init(){
        global $wp_rewrite;
        $rules = $wp_rewrite->wp_rewrite_rules();
        if (!isset($rules['mce_templates.js$'])) {
            $wp_rewrite->flush_rules();
        }
    }

    public function rewrite_rules_array($rules){
        global $wp_rewrite;
        $new_rules['mce_templates.js$'] = $wp_rewrite->index . '?mce_templates=1';
        $rules = array_merge($new_rules, $rules);
        return $rules;
    }

    public function query_vars($vars) {
        $vars[] = 'mce_templates';
        return $vars;
    }
    
    public function wp(){
        if (get_query_var('mce_templates')) {
            global $wpdb;
            global $user_login;
            global $current_user;
            global $MceTemplates;

            if (!$user_login) {
                exit;
            }

            if( isset($_GET['id']) && strlen($_GET['id']) ){
                $sql = "select html from {$wpdb->prefix}{$MceTemplates->table}
                    where (`ID`=%s) and (`author`={$current_user->ID} or `share`=1)
                        order by `modified` desc";
                $sql = $wpdb->prepare($sql, $_GET['id']);
                $template = $wpdb->get_var($sql);
                if ($template) {
                    echo stripslashes($template);
                }
                exit;
            }

            $sql = "select * from {$wpdb->prefix}{$MceTemplates->table}
                where `author`={$current_user->ID} or `share`=1
                    order by `modified` desc";
            $row = $wpdb->get_results($sql);

            header( 'Content-Type: application/x-javascript; charset=UTF-8' );
            echo 'var tinyMCETemplateList = [';
            $arr = array();
            foreach ($row as $tpl) {
                $ID = esc_html($tpl->ID);
                $name = $tpl->name;
                $desc = esc_html($tpl->desc);
                $arr[] = "[\"{$name}\", \"/mce_templates.js?id={$ID}\", \"{$desc}\"]";
            }
            echo join(',', $arr);
            echo ']';
            exit;
        }
    }
}

?>
