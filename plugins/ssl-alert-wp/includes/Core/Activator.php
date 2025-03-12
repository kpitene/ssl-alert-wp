<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles plugin activation
 */
class Activator {
    /**
     * Activate the plugin
     */
    public static function activate() {
        // Schedule daily check if not already scheduled
        if (!wp_next_scheduled('ssl_alert_wp_daily_check')) {
            wp_schedule_event(time(), 'daily', 'ssl_alert_wp_daily_check');
        }

        // Create default settings
        $settings = new Settings();
    }
}
