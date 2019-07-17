<?php

/*
  Plugin Name:  Smart Dealer
  Plugin URI:   https://github.com/smartdealer/wordpress
  Description:  Plugin de integração com da plataforma Smart Dealer (c) com o Wordpress (versão 4 ou superior), listagem e detalhamento de veículos novos e usados, com registro de leads.
  Version:      2.0.3
  Author:       Smart Dealer Software
  Author URI:   http://smartdealer.com.br
  License:      GPL2
  License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

define('DS', DIRECTORY_SEPARATOR);
define('US', '/');
define('MODELS_DIR', __DIR__ . DS . 'models');

// load models
if (($models = scandir(MODELS_DIR))) {
    foreach ($models AS $file) {
        if (stristr($file, '.php')) {
            require_once MODELS_DIR . DS . $file;
        }
    }
}

// add custom routes
add_action('init', function() {

    global $wpdb;

    if (!class_exists('SmartDealer') OR ! $wpdb) {
        return false;
    }

    // instance
    $api = (new SmartDealer());

    // get uri (params)
    $uri = $api->uri(0);

    // check routes (reserved)
    if (in_array($uri, array('novo', 'usado'))) {

        // remove me tags
        remove_action('wp_head', '_wp_render_title_tag', 1);

        // set headers
        header('Pragma: public');
        header('Cache-Control: max-age=' . $api::WS_DATA_CACHE_TIME);
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + WS_DATA_CACHE_TIME));
        header('Content-Type: text/html;charset=utf-8');

        // compile content
        echo $api->compile($uri, true);

        // kill;
        exit();
    }

    // check routes (reserved)
    if (in_array($uri, array('smartdealer_data'))) {

        // set headers
        header('Pragma: public');
        header('Cache-Control: max-age=' . $api::WS_DATA_CACHE_TIME);
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + WS_DATA_CACHE_TIME));
        header('Content-type: application/json;charset=utf-8');

        // compile content
        echo $api->compile($uri, true);

        // kill;
        exit();
    }
});

// add smart dealer to WP menu
add_action('admin_menu', function() {
    (new SmartDealer())->createMenu();
});

// add custom scripts (post, page)
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('sdplugin-bootstrap', plugins_url('assets/css/bootstrap-minimal.css', __FILE__), array(), '1.0.0', false);
    wp_enqueue_style('sdplugin-custom', plugins_url('assets/css/custom.css', __FILE__), array(), '1.0.0', false);
    wp_enqueue_script('sdplugin-custom', plugins_url('assets/js/custom.js', __FILE__), array(), '1.0.0', false); // false = head
});

add_action('wp_head', function() {
    echo '<script type="text/javascript" src="' . plugins_url('assets/js/custom.js', __FILE__) . '"></script>';
});

// register shortcode
add_shortcode('novos', function() {
    return (new SmartDealer())->compile('novos');
});

// register shortcode
add_shortcode('usados', function() {
    return (new SmartDealer())->compile('usados');
});

// register shortcode
add_shortcode('ofertas', function() {
    return (new SmartDealer())->compile('ofertas', true);
});

// add seo description
add_action('wp_head', function($a) {
    return (new SmartDealer())->applyMetaTags($a);
}, 1);

