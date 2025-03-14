<?php
/**
 * Plugin Name: SSL Alert WP
 * Plugin URI: https://wordpress.org/plugins/ssl-alert-wp/
 * Description: Monitor your SSL certificate expiration and receive notifications before it expires.
 * Version: 1.0.0
 * Author: Kpitene
 * Author URI: https://kpitene.fr
 * Text Domain: ssl-alert-wp
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package SSL_Alert_WP
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('SSL_ALERT_WP_VERSION', '1.0.0');
define('SSL_ALERT_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SSL_ALERT_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'SSL_Alert_WP\\';
    $base_dir = SSL_ALERT_WP_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function ssl_alert_wp_init() {
    if (class_exists('SSL_Alert_WP\\Core\\Plugin')) {
        $plugin = new SSL_Alert_WP\Core\Plugin();
        $plugin->init();
    }
}
// add_action('plugins_loaded', 'ssl_alert_wp_init');
add_action('init', 'ssl_alert_wp_init');


// add_action('init', function()
// {
//     // since 6.7
//     load_plugin_textdomain(
//         'ssl-alert-wp',
//         false,
//         SSL_ALERT_WP_PLUGIN_DIR . '/languages/'
//     );});

// Activation hook
register_activation_hook(__FILE__, function() {
    if (class_exists('SSL_Alert_WP\\Core\\Activator')) {
        SSL_Alert_WP\Core\Activator::activate();
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    if (class_exists('SSL_Alert_WP\\Core\\Deactivator')) {
        SSL_Alert_WP\Core\Deactivator::deactivate();
    }
});
