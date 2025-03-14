<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles plugin settings
 */
class Settings {
    /**
     * Option name in WordPress options table
     */
    const OPTION_NAME = 'ssl_alert_wp_settings';

    /**
     * Default settings
     *
     * @var array
     */
    private $defaults = [
        'monitored_url' => '',
        'notification_days' => [14, 7, 1],
        'notification_emails' => [],
        'last_check' => '',
        'last_check_result' => [],
    ];

    /**
     * Current settings
     *
     * @var array
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option(self::OPTION_NAME, $this->defaults);
        
        // If no URL is set, use site URL by default
        if (empty($this->settings['monitored_url'])) {
            $this->settings['monitored_url'] = get_site_url(null, '', 'https');
            $this->save();
        }

        // If no notification emails are set, use admin email by default
        if (empty($this->settings['notification_emails'])) {
            $this->settings['notification_emails'] = [get_option('admin_email')];
            $this->save();
        }
    }

    /**
     * Get monitored URL
     *
     * @return string
     */
    public function get_monitored_url() {
        return isset($this->settings['monitored_url']) ? $this->settings['monitored_url'] : $this->defaults['monitored_url'];
    }

    /**
     * Set monitored URL
     *
     * @param string $url
     * @return bool
     */
    // public function set_monitored_url($url) {
    //     $this->settings['monitored_url'] = esc_url_raw($url);
    //     return $this->save();
    // }

    /**
     * Get notification days
     *
     * @return array
     */
    public function get_notification_days() {
        $days = isset($this->settings['notification_days']) ? $this->settings['notification_days'] : $this->defaults['notification_days'];
        // Ensure we always return an array
        if (is_string($days)) {
            $days = array_filter(array_map('trim', explode(',', $days)));
            $days = array_map('absint', $days);
            // Update the setting to store as array
            $this->settings['notification_days'] = $days;
            $this->save();
        }
        return (array)$days;
    }

    /**
     * Set notification days
     *
     * @param array $days
     * @return bool
     */
    // public function set_notification_days($days) {
    //     if (is_string($days)) {
    //         $days = array_filter(array_map('trim', explode(',', $days)));
    //     }
    //     $this->settings['notification_days'] = array_map('absint', $days);
    //     return $this->save();
    // }

    /**
     * Get notification emails
     *
     * @return array
     */
    public function get_notification_emails() {
        return isset($this->settings['notification_emails']) ? (array) $this->settings['notification_emails'] : $this->defaults['notification_emails'];
    }

    /**
     * Set notification emails
     *
     * @param array $emails
     * @return bool
     */
    // public function set_notification_emails($emails) {
    //     $this->settings['notification_emails'] = array_map('sanitize_email', (array) $emails);
    //     return $this->save();
    // }

    /**
     * Update last check information
     *
     * @param array $result Check result
     * @return bool
     */
    public function update_last_check($result) {
        $this->settings['last_check'] = current_time('mysql');
        $this->settings['last_check_result'] = $result;
        return $this->save();
    }

    /**
     * Get last check information
     *
     * @return array
     */
    public function get_last_check() {
        return [
            'time' => isset($this->settings['last_check']) ? $this->settings['last_check'] : $this->defaults['last_check'],
            'result' => isset($this->settings['last_check_result']) ? $this->settings['last_check_result'] : $this->defaults['last_check_result']
        ];
    }

    /**
     * Save settings to database
     *
     * @return bool
     */
    private function save() {
        return update_option(self::OPTION_NAME, $this->settings);
    }
}
