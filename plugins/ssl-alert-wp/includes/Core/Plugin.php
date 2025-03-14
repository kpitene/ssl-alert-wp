<?php
namespace SSL_Alert_WP\Core;

/**
 * Main plugin class
 */
class Plugin {
    /**
     * @var Certificate_Checker
     */
    private $certificate_checker;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->setup_admin();
        $this->setup_cron();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        $this->settings = new Settings();
        $this->certificate_checker = new Certificate_Checker();
        $this->notifier = new Notifier();
    }

    /**
     * Setup admin interface
     */
    private function setup_admin() {
        if (is_admin()) {
            $admin = new Admin($this->settings, $this->certificate_checker, $this);
            $admin->init();
        }
    }

    /**
     * Setup cron jobs
     */
    private function setup_cron() {
        add_action('ssl_alert_wp_daily_check', [$this, 'check_certificate']);
    }

    /**
     * Check certificate and send notifications if needed
     */
    public function check_certificate() {
        $url = $this->settings->get_monitored_url();
        $result = $this->certificate_checker->check($url);
        
        if ($result['valid']) {
            $days_remaining = (int)$result['days_remaining'];
            $notification_days = (array)$this->settings->get_notification_days();
            
            if ($days_remaining <= 0) {
                // Certificate has expired - always send notification
                $this->notifier->send_expiry_notification($days_remaining, $result['expiry_date']);
                $this->settings->update_last_check([
                    'status' => 'error',
                    'message' => sprintf(
                        // translators: %1$s: expired certificate message
                        __('Certificate has expired on %1$s.', 'ssl-alert-wp'),
                        $result['expiry_date']
                    )
                ]);
            } elseif (!empty($notification_days) && in_array($days_remaining, $notification_days, true)) {
                // Certificate is valid but matches a notification threshold
                $this->notifier->send_expiry_notification($days_remaining, $result['expiry_date']);
                
                $this->settings->update_last_check([
                    'status' => 'warning',
                    'message' => sprintf(
                        // translators: %1$d: days remaining, %2$s: expiry date, %3$s: issuer, %4$s: subject
                        __('WARNING: Certificate will expire in %1$d days (on %2$s). A notification email has been sent. Issued by %3$s for %4$s.', 'ssl-alert-wp'),
                        $days_remaining,
                        $result['expiry_date'],
                        $result['issuer'],
                        $result['subject']
                    )
                ]);
            } else {
                // Certificate is valid and not near expiration
                $this->settings->update_last_check([
                    'status' => 'valid',
                    'message' => sprintf(
                        // translators: %1$d: days remaining, %2$s: expiry date, %3$s: issuer, %4$s: subject
                        __('Certificate is valid. Expires in %1$d days on %2$s. Issued by %3$s for %4$s.', 'ssl-alert-wp'),
                        $days_remaining,
                        $result['expiry_date'],
                        $result['issuer'],
                        $result['subject']
                    )
                ]);
            }
        } else {
            $this->notifier->send_error_notification($result['error']);
            
            $this->settings->update_last_check([
                'status' => 'error',
                'message' => $result['error']
            ]);
        }
    }
}
