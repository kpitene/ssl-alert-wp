<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles plugin deactivation
 */
class Deactivator {
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Remove scheduled check
        $timestamp = wp_next_scheduled('ssl_alert_wp_daily_check');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ssl_alert_wp_daily_check');
        }
    }
}
