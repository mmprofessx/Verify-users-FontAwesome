<?php
/*
Plugin "Verify Users" 13.06.2019
2019 (c) itsmeJAY
Plugin by itsmeJAY - if you have questions or found bugs, please write me!
Version tested: 1.8.21 by itsmeJAY
*/

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('admin_formcontainer_output_row', 'verify_add_setting');
$plugins->add_hook('admin_user_users_edit_commit', 'verify_user_update');
$plugins->add_hook('member_profile_end', 'verify_user_profile');
$plugins->add_hook('postbit', 'verify_user_show');

function verify_info()
{
    // Sprachdatei laden
    global $lang, $db;
    $lang->load("userverified");

    return array(
        "name" => $db->escape_string($lang->vf_pfath_title),
        "description" => $db->escape_string($lang->vf_pfath_title_desc),
        "website" => "https://www.mybb.de/forum/user-10220.html",
        "author" => "itsmeJAY from MyBB.de",
        "authorsite" => "https://www.mybb.de/forum/user-10220.html",
        "version" => "1.4.1",
    );
}

function verify_install()
{
    global $db, $mybb, $lang;

    // Sprachdatei laden
    $lang->load("userverified");

    if (!$db->field_exists('verified', "users")) {
        $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `verified` INT( 1 ) NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `groupverified` INT( 1 ) NOT NULL DEFAULT '0';");
        $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `verificationdate` INT( 10 ) NOT NULL DEFAULT '0';");
    }

    $setting_group = array(
        'name' => 'vf_verified',
        'title' => "$lang->vf_pfath_title",
        'description' => "$lang->vf_pfath_title_desc",
        'disporder' => 5,
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    // Einstellungen

    $setting_array = array(
        'vf_icon_fa' => array(
            'title' => $db->escape_string($lang->vf_icon_fa),
            'description' => $db->escape_string($lang->vf_icon_fa_desc),
            'optionscode' => 'radio \n 1=Fontawesome \n 2=Picture',
            'value' => 1, // Default
            'disporder' => 1
        ),
        'vf_pfath' => array(
            'title' => $db->escape_string($lang->vf_pfath),
            'description' => $db->escape_string($lang->vf_pfath_desc),
            'optionscode' => 'text',
            'value' => 'images/icons/verified.png', // Default
            'disporder' => 2
        ),
        'vf_fa_icon' => array(
            'title' => $db->escape_string($lang->vf_fa_icon),
            'description' => $db->escape_string($lang->vf_fa_icon_desc),
            'optionscode' => 'text',
            'value' => 'fas fa-check-square', // Default
            'disporder' => 3
        ),
        'vf_fa_size' => array(
            'title' => $db->escape_string($lang->vf_fa_size),
            'description' => $db->escape_string($lang->vf_fa_size_desc),
            'optionscode' => 'numeric',
            'value' => '24', // Default
            'disporder' => 4
        ),
        'vf_fa_color' => array(
            'title' => $db->escape_string($lang->vf_fa_color),
            'description' => $db->escape_string($lang->vf_fa_color_desc),
            'optionscode' => 'text',
            'value' => '#1E90FF', // Default
            'disporder' => 5
        ),
        'vf_width' => array(
            'title' => $db->escape_string($lang->vf_width),
            'description' => $db->escape_string($lang->vf_width_desc),
            'optionscode' => 'numeric',
            'value' => '24', // Default
            'disporder' => 6
        ),
        'vf_height' => array(
            'title' => $db->escape_string($lang->vf_height),
            'description' => $db->escape_string($lang->vf_height_desc),
            'optionscode' => 'numeric',
            'value' => '24', // Default
            'disporder' => 7
        ),
        'vf_hovertext' => array(
            'title' => $db->escape_string($lang->vf_hovertext),
            'description' => $db->escape_string($lang->vf_hovertext_desc),
            'optionscode' => 'text',
            'value' => $db->escape_string($lang->vf_hovertext_default), // Default
            'disporder' => 8
        ),
        'vf_showdate' => array(
            'title' => $db->escape_string($lang->vf_showdate),
            'description' => $db->escape_string($lang->vf_showdate_desc),
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 9
        ),
        'vf_showdate_postbit' => array(
            'title' => $db->escape_string($lang->vf_showdate_postbit),
            'description' => $db->escape_string($lang->vf_showdate_postbit_desc),
            'optionscode' => 'yesno',
            'value' => 0, // Default
            'disporder' => 10
        ),
        'vf_showdate_text' => array(
            'title' => $db->escape_string($lang->vf_showdate_text),
            'description' => $db->escape_string($lang->vf_showdate_text_desc),
            'optionscode' => 'textarea',
            'value' => $db->escape_string($lang->vf_showdate_text_default), // Default
            'disporder' => 11
        ),
        'vf_date_format' => array(
            'title' => $db->escape_string($lang->vf_showdate_date_format),
            'description' => $db->escape_string($lang->vf_showdate_date_format_text),
            'optionscode' => 'radio \n 1=English (06/13/2019 at 09:33 PM) \n 2=German (13.06.2019 at 21:33)',
            'value' => 1,
            'disporder' => 12
        ),
        'vf_group' => array(
            'title' => $db->escape_string($lang->vf_groups),
            'description' => $db->escape_string($lang->vf_groups_desc),
            'optionscode' => 'groupselect',
            'value' => 1,
            'disporder' => 13
        ),
    );

    // Einstellungen in Datenbank speichern
    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // Rebuild Settings! :-)
    rebuild_settings();

}

function verify_uninstall()
{
    global $db;
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` DROP `verified`;");
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` DROP `groupverified`;");
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` DROP `verificationdate`;");
    $db->delete_query('settings', "name IN ('vf_icon_fa', 'vf_pfath', 'vf_fa_icon', 'vf_fa_size', 'vf_fa_color', 'vf_width', 'vf_height', 'vf_hovertext', 'vf_showdate', 'vf_showdate_postbit', 'vf_showdate_text', 'vf_date_format', 'vf_group')");
    $db->delete_query('settinggroups', "name = 'vf_verified'");

    // Rebuild Settings! :-)
    rebuild_settings();
}

function verify_activate()
{
    global $db, $mybb, $lang, $cache;

    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'userstars\']}') . "#i", '{$post[\'userstars\']}{$post[\'icon_vf\']}');
    find_replace_templatesets("member_profile", "#" . preg_quote('{$userstars}') . "#i", '{$userstars}<br/>{$icon_vf}');
    find_replace_templatesets("headerinclude", "#" . preg_quote('{$stylesheets}') . "#i", '{$stylesheets}<br/><link href="inc/plugins/css/all.min.css" rel="stylesheet">');

    $taskExists = $db->simple_select(
        'tasks',
        'tid',
        'file = \'verifyusers\'',
        array('limit' => '1')
    );

    $numRows = $db->num_rows($taskExists);

    if ($numRows == 0) {

        require_once MYBB_ROOT . '/inc/functions_task.php';

        $new_task = [
            'title' => $db->escape_string($lang->vf_pfath_title),
            'description' => $db->escape_string($lang->vf_task_desc),
            'file' => 'verifyusers',
            'minute' => '0',
            'hour' => '*',
            'day' => '*',
            'month' => '*',
            'weekday' => '*',
            'enabled' => '1',
            'logging' => '1',
        ];

        $new_task['nextrun'] = fetch_next_run($new_task);

        $db->insert_query('tasks', $new_task);
        $cache->update_tasks();
    }

}

function verify_deactivate()
{
    global $db;

    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'icon_vf\']}') . "#i", '');
    find_replace_templatesets("member_profile", "#" . preg_quote('{$icon_vf}<br/>') . "#i", '');
    find_replace_templatesets("headerinclude", "#" . preg_quote('<br/><link href="inc/plugins/css/all.min.css" rel="stylesheet">') . "#i", '');

    $taskExists = $db->simple_select(
        'tasks',
        'tid',
        'file = \'verifyusers\'',
        array('limit' => '1')
    );

    $numRows = $db->num_rows($taskExists);

    if ($numRows > 0) {
        $db->delete_query('tasks', 'file = \'verifyusers\'');
    }
}

function verify_is_installed()
{
    global $db;
    if ($db->field_exists('verified', "users")) {
        return true;
    } else {
        return false;
    }
}


//Funktionen

function verify_add_setting(&$pluginargs)
{
    global $mybb, $lang, $form, $user;
    $lang->load("userverified");

    if ($pluginargs['title'] == $lang->other_options && $lang->other_options) {
        $pluginargs['content'] .= "<tr><td><label>" . $lang->vf_pfath_title . "</label><br />";
        $pluginargs['content'] .= "<div class=\"form_row\"><div class=\"user_settings_bit\">" . $form->generate_check_box('verified', 1, $lang->vf_setting, array('checked' => $user['verified'], 'id' => 'verified')) . "";
        $pluginargs['content'] .= "</td></tr>";
    }
}

function verify_user_update()
{
    global $db, $mybb, $cache, $user;

    $uid = (int)$user['uid'];
    $update_array = array(
        "verified" => $mybb->get_input('verified', MyBB::INPUT_INT),
    );

    if ($update_array['verified'] == 1) {
        $update_array['verificationdate'] = time();
    } else if ($update_array['verified'] == 0) {
        $update_array['verificationdate'] = 0;
    }

    $db->update_query("users", $update_array, "uid='{$uid}'");

}

function verify_user_show(&$post)
{
    global $db, $mybb, $cache, $user;

    $post['icon_vf'] = "";

    if ($post['verified'] == 1 && $mybb->settings['vf_icon_fa'] == 2 || $post['groupverified'] == 1 && $mybb->settings['vf_icon_fa'] == 2) {
        $post['icon_vf'] = "<img src=\"" . $mybb->settings['vf_pfath'] . "\" title=\"" . $mybb->settings['vf_hovertext'] . "\" width=\"" . $mybb->settings['vf_width'] . "\" height=\"" . $mybb->settings['vf_height'] . "\">";
    } else if ($post['verified'] == 1 && $mybb->settings['vf_icon_fa'] == 1 || $post['groupverified'] == 1 && $mybb->settings['vf_icon_fa'] == 1) {
        $post['icon_vf'] = "<i class=\"" . $mybb->settings['vf_fa_icon'] . "\" style=\"font-size: " . $mybb->settings['vf_fa_size'] . "px; color: " . $mybb->settings['vf_fa_color'] . "\" title=\"" . $mybb->settings['vf_hovertext'] . "\"></i>";
    }

    if ($post['verificationdate'] > 0 && $mybb->settings['vf_showdate_postbit'] == 1 && $mybb->settings['vf_date_format'] == 1) {
        $shortcode = "{date}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcode, date("m/d/Y", $post['verificationdate']), $mybb->settings['vf_showdate_text']);
        $shortcodetwo = "{time}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcodetwo, date("h:i A", $post['verificationdate']), $mybb->settings['vf_showdate_text']);
        $post['icon_vf'] .= " " . $mybb->settings['vf_showdate_text'];
    } else if ($post['verificationdate'] > 0 && $mybb->settings['vf_showdate_postbit'] == 1 && $mybb->settings['vf_date_format'] == 2) {
        $shortcode = "{date}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcode, date("d.m.Y", $post['verificationdate']), $mybb->settings['vf_showdate_text']);
        $shortcodetwo = "{time}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcodetwo, date("H:i", $post['verificationdate']), $mybb->settings['vf_showdate_text']);
        $post['icon_vf'] .= " " . $mybb->settings['vf_showdate_text'];
    }

}

function verify_user_profile()
{
    global $db, $mybb, $cache, $user, $memprofile, $icon_vf;

    if ($memprofile['verified'] == 1 && $mybb->settings['vf_icon_fa'] == 2 || $memprofile['groupverified'] == 1 && $mybb->settings['vf_icon_fa'] == 2) {
        $icon_vf = "<img src=\"" . $mybb->settings['vf_pfath'] . "\" title=\"" . $mybb->settings['vf_hovertext'] . "\" width=\"" . $mybb->settings['vf_width'] . "\" height=\"" . $mybb->settings['vf_height'] . "\">";
        settype($icon_vf, "string");
    } else if ($memprofile['verified'] == 1 && $mybb->settings['vf_icon_fa'] == 1 || $memprofile['groupverified'] == 1 && $mybb->settings['vf_icon_fa'] == 1) {
        $icon_vf = "<i class=\"" . $mybb->settings['vf_fa_icon'] . "\" style=\"font-size: " . $mybb->settings['vf_fa_size'] . "px; color: " . $mybb->settings['vf_fa_color'] . "\" title=\"" . $mybb->settings['vf_hovertext'] . "\"></i>";
    }

    if ($memprofile['verificationdate'] > 0 && $mybb->settings['vf_showdate'] == 1 && $mybb->settings['vf_date_format'] == 1) {
        $shortcode = "{date}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcode, date("m/d/Y", $memprofile['verificationdate']), $mybb->settings['vf_showdate_text']);
        $shortcodetwo = "{time}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcodetwo, date("h:i A", $memprofile['verificationdate']), $mybb->settings['vf_showdate_text']);
        $icon_vf .= " " . $mybb->settings['vf_showdate_text'];
    } else if ($memprofile['verificationdate'] > 0 && $mybb->settings['vf_showdate'] == 1 && $mybb->settings['vf_date_format'] == 2) {
        $shortcode = "{date}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcode, date("d.m.Y", $memprofile['verificationdate']), $mybb->settings['vf_showdate_text']);
        $shortcodetwo = "{time}";
        $mybb->settings['vf_showdate_text'] = str_replace($shortcodetwo, date("H:i", $memprofile['verificationdate']), $mybb->settings['vf_showdate_text']);
        $icon_vf .= " " . $mybb->settings['vf_showdate_text'];
    }
}

?>