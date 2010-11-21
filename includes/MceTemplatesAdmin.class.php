<?php

class MceTemplatesAdmin{
    function MceTemplatesAdmin($class)
    {
        global $wpdb;

        $this->domain   = $class->name;
        $this->table    = $wpdb->prefix.$class->table;

        echo '<link rel="stylesheet" href="'.TINYMCE_TEMPLATES_PLUGIN_URL.'/style.css" type="text/css" media="all" />';

        echo '<div class="wrap">';

        if (isset($_GET['page']) && $_GET['page'] == 'edittemplates') {
            if (isset($_GET['id']) && preg_match("/^[a-zA-Z0-9]{32}$/", $_GET['id'])) {
                $this->addView();
            } else {
                $this->editView();
            }
        } elseif (isset($_GET['page']) && $_GET['page'] == 'addnewtemplates') {
            $this->addView();
        }

        echo '</div>';
    }


    function editView()
    {
        global $wpdb;
        global $current_user;

        echo '<h2>'.__("Edit Templates", $this->domain).'</h2>';

        if (isset($_POST['templates']) && is_array($_POST['templates'])) {
            $this->delete();
            echo '<div id="message" class="updated fade"><p>'.$n.__("Templates permanently deleted.", $this->domain).'</p></div>';
        }

        echo "<form method=\"post\" action=\"{$_SERVER['REQUEST_URI']}\">";
        echo '<table class="widefat" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col">'.__('Name', $this->domain).'</th>';
        echo '<th scope="col">'.__('Description', $this->domain).'</th>';
        echo '<th scope="col">'.__('Author', $this->domain).'</th>';
        echo '<th scope="col">'.__('Share', $this->domain).'</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tfoot>';
        echo '<tr>';
        echo '<th scope="col" class="column-cb check-column"><input type="checkbox" /></th>';
        echo '<th scope="col">'.__('Name', $this->domain).'</th>';
        echo '<th scope="col">'.__('Description', $this->domain).'</th>';
        echo '<th scope="col">'.__('Author', $this->domain).'</th>';
        echo '<th scope="col">'.__('Share', $this->domain).'</th>';
        echo '</tr>';
        echo '</tfoot>';
        echo '<tbody>';

        $sql = "select * from {$this->table}
            where `author`={$current_user->ID} or `share`=1 order by `modified` desc";
        $row = $wpdb->get_results($sql);

        $i = 0;
        foreach ($row as $tpl) {
            if ($tpl->author === $current_user->ID) {
                $mine = true;
            } else {
                $mine = false;
            }
            if ($i % 2) {
                $class = 'alternate';
            } else {
                $class = '';
            }
            echo '<tr class="'.$class.'" valign="top">';
            if ($mine) {
                echo '<th scope="row" class="check-column">';
                echo "<input type=\"checkbox\" name=\"templates[]\"
                             value=\"{$tpl->ID}\" />";
                echo '</th>';
                echo '<td><a href="?page=edittemplates&id='.$tpl->ID.'">'.esc_html($tpl->name).'</a></td>';
            } else {
                echo '<th scope="row" class="check-column">&nbsp;</th>';
                echo '<td>'.esc_html($tpl->name).'</td>';
            }
            echo '<td>'.esc_html($tpl->desc).'</td>';
            $author = get_userdata($tpl->author);
            echo '<td>'.esc_html($author->nickname).'</td>';
            if ($tpl->share) {
                echo '<td>'.__('Shared', $this->domain).'</td>';
            } else {
                echo '<td>'.__('Private', $this->domain).'</td>';
            }
            echo "</tr>";
            $i = $i + 1;
        }
        echo "</tbody>";
        echo "</table>";
        echo '<input type="submit" value="'.__("Delete checked items", $this->domain).'" class="button-secondary" />';
        echo "</form>";
    }

    function addView()
    {
        global $current_user;

        if (isset($_POST['save']) && $_POST['save']) {
            if ($this->validate() && $this->save()) {
                echo "<div id=\"message\" class=\"updated fade\"><p><strong>".__("Template saved.", $this->domain)."</strong></p></div>";
                global $wpdb;
                $sql = $wpdb->prepare("select * from {$this->table}
                            where `id`=%s", $_POST['id']);
                $r = $wpdb->get_row($sql);
                $id     = $r->ID;
                $name   = $r->name;
                $desc   = $r->desc;
                $html   = stripslashes($r->html);
                $share  = $r->share;
            } else {
                echo "<div id=\"message\" class=\"error fade\"><p><strong>".__("All entry must not be blank.", $this->domain)."</strong></p></div>";
                $id     = $_POST['id'];
                $name   = $_POST['name'];
                $desc   = $_POST['desc'];
                $html   = stripslashes($_POST['html']);
                $share  = $_POST['share'];
            }
        } elseif (isset($_GET['page']) && $_GET['page'] == 'edittemplates'
            && isset($_GET['id']) && preg_match("/^[a-zA-Z0-9]{32}$/", $_GET['id'])) {
                global $wpdb;
                $sql = $wpdb->prepare("select * from {$this->table}
                            where `ID`=%s and author=%s", $_GET['id'], $current_user->ID);
                $r = $wpdb->get_row($sql);
                if ($r) {
                    $id     = $r->ID;
                    $name   = $r->name;
                    $desc   = $r->desc;
                    $html   = stripslashes($r->html);
                    $share  = $r->share;
                } else {
                    return;
                }
        } else {
                $id = md5(uniqid(rand(), true));
                $name   = null;
                $desc   = null;
                $html   = null;
                $share  = null;
        }

        if (isset($_GET['page']) && $_GET['page'] == 'edittemplates'
                && isset($_GET['id']) && preg_match("/^[a-zA-Z0-9]{32}$/", $_GET['id'])) {
            echo '<h2>'.__("Edit Templates", $this->domain).'</h2>';
        } else {
            echo '<h2>'.__("Add New Templates", $this->domain).'</h2>';
        }
        echo "<form action=\"{$_SERVER["REQUEST_URI"]}\" method=\"post\">";
        echo "<input type=\"hidden\" name=\"save\" value=\"1\" />";
        echo "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />";
        echo "<h3>".__("Template Name", $this->domain)."*</h3>";
        echo "<input type=\"text\" id=\"name\" name=\"name\" value=\"{$name}\" />";
        echo "<h3>".__("Template Description", $this->domain)."*</h3>";
        echo "<textarea id=\"desc\" name=\"desc\">{$desc}</textarea>";
        echo "<h3>".__("Template Contents", $this->domain)."*</h3>";
        echo "<textarea id=\"html\" name=\"html\">{$html}</textarea>";
        echo "<h3>".__("Share", $this->domain)."*</h3>";
        echo "<select name=\"share\" id=\"share\">";
        if ($share == 1) {
            echo "<option value=\"1\" selected=\"selected\">".__("Share", $this->domain)."</option>";
            echo "<option value=\"0\">".__("Private", $this->domain)."</option>";
        } else {
            echo "<option value=\"1\">".__("Share", $this->domain)."</option>";
            echo "<option value=\"0\" selected=\"selected\">".__("Private", $this->domain)."</option>";
        }
        echo "</select>";
        echo "<div id=\"save\">";
        echo "<input type=\"submit\" value=\"".__("Save Template", $this->domain)."\" class=\"button-primary\" />";
        echo "</div>";
        echo "</form>";
    }

    function save()
    {
        global $wpdb;
        global $current_user;

        if (!$this->checkAuth($_POST['id'])) {
            return false;
        }

        $sql = "insert into {$this->table}
                    (`ID`, `name`, `desc`, `html`, `share`, `author`)
                values
                    (%s, %s, %s, %s, %d, %d)
                on duplicate key
                update
                    `name`=values(`name`),
                    `desc`=values(`desc`),
                    `html`=values(`html`),
                    `share`=values(`share`)
                ";
        $sql = $wpdb->prepare($sql, $_POST['id'], $_POST['name'], $_POST['desc'],
                            $_POST['html'], $_POST['share'], $current_user->ID);
        $wpdb->query($sql);
        return true;
    }

    function validate()
    {
        $pars = array('id', 'name', 'desc', 'html', 'share');
        foreach ($pars as $par):
        $_POST[$par] = trim($_POST[$par]);
        if (!isset($_POST[$par]) || !strlen($_POST[$par])) {
            return false;
        }
        endforeach;

        if (!preg_match("/^[a-zA-Z0-9]{32}$/", $_POST['id'])) {
            return false;
        }

        if ($_POST['share'] !== '1') {
            $_POST['share'] = '0';
        }

        return true;
    }

    function checkAuth($id)
    {
        global $wpdb;
        global $current_user;

        $sql = $wpdb->prepare("select author from {$this->table} where ID=%s", $id);
        $author = $wpdb->get_var($sql);

        if (!$author || $author === $current_user->ID) {
            return true;
        } else {
            return false;
        }
    }

    function delete()
    {
        global $wpdb;
        global $current_user;

        foreach ($_POST['templates'] as $tpl) {
            $sql = "delete from {$this->table} where ID=%s and author=%s";
            $sql = $wpdb->prepare($sql, $tpl, $current_user->ID);
            $wpdb->query($sql);
        }
    }
}

?>
