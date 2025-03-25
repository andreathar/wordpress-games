<?php

/**
 * Plugin Name: TigerForge UniREST
 * Plugin URI: https://tigerforge.altervista.org
 * Description: This plugin is to be used in conjunction with the UniREST asset for Unity.
 * Version: 4.1
 * Author: TigerForge
 * Author URI: https://tigerforge.altervista.org
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('UNIREST_PLUGIN_VERSION', '4.1');
define('UNIREST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UNIREST_WPLOAD_PATH', "../../../../../../wp-load.php");
define('UNIREST_IS_INSTALLED', !(get_option("UniREST_Secret_Key1") === false));
define('UNIREST_AJAX_URL', admin_url('admin-ajax.php'));
define('UNIREST_TABLE_PAGE', admin_url('admin.php?page=unirest-plugin-table'));
define('UNIREST_API_PAGE', admin_url('admin.php?page=unirest-plugin-api'));

include_once "plugins/medoo/Medoo.php";
use Medoo\Medoo;

$UNIREST_DB = new Medoo([
    'type' => 'mysql',
    'host' => DB_HOST,
    'database' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    'charset' => 'utf8mb4',
]);

function unirest_plugin_aggiungi_menu()
{
    $myIcon = "data:image/svg+xml;base64," . base64_encode('<svg width="32" height="32" viewBox="0 0 280 280" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M256.57,75.26v-1.28h-0.67C233.25,31.49,188.48,2.5,137.06,2.5C62.86,2.5,2.5,62.86,2.5,137.06c0,74.2,60.36,134.56,134.56,134.56c74.2,0,134.56-60.36,134.56-134.56C271.63,114.8,266.19,93.78,256.57,75.26z M137.06,27.5c36.95,0,69.68,18.39,89.54,46.49h-68.65h-3.49H47.53C67.38,45.89,100.12,27.5,137.06,27.5z M240.71,101.55c2.89,8.42,4.79,17.31,5.54,26.51h-55.24v-26.51H240.71z M137.06,246.63c-60.41,0-109.56-49.15-109.56-109.56c0-12.43,2.08-24.37,5.91-35.51H76.7v114.94h36.75V101.55h41.01v114.94h36.56v-60.87h54.03C236.21,207.23,191.15,246.63,137.06,246.63z"/></svg>');

    add_menu_page(
        'UniREST',
        'UniREST',
        'manage_options',
        'unirest-plugin-main',
        'unirest_plugin_main',
        $myIcon,
        20
    );

    if (UNIREST_IS_INSTALLED) {
        add_submenu_page(
            'unirest-plugin-main',
            'Database',
            'Database',
            'manage_options',
            'unirest-plugin-database',
            'unirest_plugin_database'
        );

        add_submenu_page(
            'unirest-plugin-main',
            'API',
            'API',
            'manage_options',
            'unirest-plugin-apigroup',
            'unirest_plugin_apigroup'
        );

        add_submenu_page(
            'unirest-plugin-main',
            'Uploads',
            'Uploads',
            'manage_options',
            'unirest-plugin-uploads',
            'unirest_plugin_uploads'
        );

        add_submenu_page(
            'unirest-plugin-main',
            'Unity',
            'Unity API Script',
            'manage_options',
            'unirest-plugin-unity',
            'unirest_plugin_unity'
        );

        add_submenu_page(
            'unirest-plugin-main',
            'Settings',
            'Settings',
            'manage_options',
            'unirest-plugin-settings',
            'unirest_plugin_settings'
        );

        add_submenu_page(
            null,
            'Table',
            'Table',
            'manage_options',
            'unirest-plugin-table',
            'unirest_plugin_table'
        );

        add_submenu_page(
            null,
            'API',
            'API',
            'manage_options',
            'unirest-plugin-api',
            'unirest_plugin_api'
        );
    }
}
add_action('admin_menu', 'unirest_plugin_aggiungi_menu');

function unirest_plugin_main()
{
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/main.php";?>
    </div>
    <?php
}

function unirest_plugin_database()
{
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/database.php";?>
    </div>
    <?php
}

function unirest_plugin_table() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap" >
        <?php include_once "app/views/table.php";?>
    </div>
    <?php
}

function unirest_plugin_apigroup() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/apigroup.php";?>
    </div>
    <?php
}

function unirest_plugin_api() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/apis.php";?>
    </div>
    <?php
}

function unirest_plugin_uploads() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/uploads.php";?>
    </div>
    <?php
}

function unirest_plugin_unity() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/unity.php";?>
    </div>
    <?php
}

function unirest_plugin_settings() {
    ?>
    <script>
        var WP_ADMIN_AJAX_URL = "<?=UNIREST_AJAX_URL?>";
    </script>

    <div id="unirest" class="wrap">
        <?php include_once "app/views/settings.php";?>
    </div>
    <?php
}

function unirest_enqueue_script()
{
    $uri = plugin_dir_url(__FILE__);
    $random_version = wp_generate_password(6, false, false);

    $screen = get_current_screen();
    if (strpos($screen->base, "unirest-plugin") !== false) {

        // PLUGINS CSS
        $css = array(
            "awesome/css/fontawesome.min.css",
            "awesome/css/regular.min.css",
            "awesome/css/solid.min.css",
            "awesome/css/brands.min.css",
            "jquery-ui/jquery-ui.min.css",
            "jquery-ui/jquery-ui.structure.min.css",
            "jquery-ui/jquery-ui.theme.min.css",
            "toastr/toastr.min.css",
            "sweetalert2/sweetalert2.min.css",
            "select2/select2.min.css",
        );
        for ($i = 0; $i < count($css); $i++) {
            wp_enqueue_style("unirest-style-$i", $uri . "plugins/" . $css[$i], array(), $random_version);
        }

        wp_enqueue_style("unirest-style-main", $uri . "style.css", array(), $random_version);

        // PLUGINS JS
        $js = array(
            "jquery/jquery-3.7.1.min.js",
            "jquery-ui/jquery-ui.min.js",
            "sweetalert2/sweetalert2.all.min.js",
            "toastr/toastr.min.js",
            "ace/ace.js",
            "knockout/knockout.js",
            "select2/select2.min.js",
        );
        for ($i = 0; $i < count($js); $i++) {
            wp_enqueue_script("unirest-plugins-$i", $uri . "plugins/" . $js[$i], array(), $random_version, false);
        }

        // APPLICATION
        $js = array(
            "app.js",
            "controllers/main.js",
            "controllers/database.js",
            "controllers/table.js",
            "controllers/apigroup.js",
            "controllers/apis.js",
            "controllers/unity.js",
            "controllers/uploads.js",
            "controllers/settings.js",
        );
        for ($i = 0; $i < count($js); $i++) {
            wp_enqueue_script("unirest-$i", $uri . "app/" . $js[$i], array(), $random_version, false);
        }
    }
}
add_action('admin_enqueue_scripts', 'unirest_enqueue_script');

include_once "apis.php";
