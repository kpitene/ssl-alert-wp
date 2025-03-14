<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles admin interface
 */
class Admin {
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Certificate_Checker
     */
    private $certificate_checker;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * Constructor
     *
     * @param Settings $settings
     * @param Certificate_Checker $certificate_checker
     * @param Plugin $plugin
     */
    public function __construct($settings, $certificate_checker, $plugin) {
        $this->settings = $settings;
        $this->certificate_checker = $certificate_checker;
        $this->plugin = $plugin;
    }

    /**
     * Initialize admin features
     */
    public function init() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_ssl_alert_wp_check_now', [$this, 'ajax_check_now']);
        add_action('wp_ajax_ssl_alert_wp_test_notification', [$this, 'ajax_test_notification']);
    }

    /**
     * Add menu page
     */
    public function add_menu_page() {
        add_options_page(
            // translators: %s: SSL certificate monitor title
            __('SSL Certificate Monitor', 'ssl-alert-wp'),
            // translators: %s: SSL monitor title
            __('SSL Monitor', 'ssl-alert-wp'),
            'manage_options',
            'ssl-alert-wp',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(  
            'ssl_alert_wp', 
            'ssl_alert_wp_settings',
            [$this, 'register_settings_options_sanitize'],
        );



        add_settings_section(
            'ssl_alert_wp_main',
            // translators: %s: SSL certificate monitoring settings title
            __('SSL Certificate Monitoring Settings', 'ssl-alert-wp'),
            [$this, 'render_section_info'],
            'ssl-alert-wp'
        );

        add_settings_field(
            'monitored_url',
            // translators: %s: URL to monitor
            __('URL to Monitor', 'ssl-alert-wp'),
            [$this, 'render_url_field'],
            'ssl-alert-wp',
            'ssl_alert_wp_main'
        );

        add_settings_field(
            'notification_days',
            // translators: %s: notification days
            __('Notification Days', 'ssl-alert-wp'),
            [$this, 'render_days_field'],
            'ssl-alert-wp',
            'ssl_alert_wp_main'
        );

        add_settings_field(
            'notification_emails',
            // translators: %s: notification emails
            __('Notification Emails', 'ssl-alert-wp'),
            [$this, 'render_emails_field'],
            'ssl-alert-wp',
            'ssl_alert_wp_main'
        );
    }

    /**
     * Register settings sanitize callback
     */
    public function register_settings_options_sanitize($input) {
        // sanitize monitored_url
        if (!empty($input['monitored_url'])) {
            $input['monitored_url'] = sanitize_url($input['monitored_url']);
        } else {
            $input['monitored_url'] = '';
        }
        
        // Sanitize notification days - handle both string and array input
        if (!empty($input['notification_days'])) {
            if (is_string($input['notification_days'])) {
                $days = array_map('trim', explode(',', $input['notification_days']));
            } else {
                $days = (array)$input['notification_days'];
            }
            $input['notification_days'] = array_filter(array_map('absint', $days));
        } else {
            $input['notification_days'] = [];
        }
        
        // Sanitize notification emails - handle both string and array input
        if (!empty($input['notification_emails'])) {
            if (is_string($input['notification_emails'])) {
                $emails = array_map('trim', explode(',', $input['notification_emails']));
            } else {
                $emails = (array)$input['notification_emails'];
            }
            $input['notification_emails'] = array_filter(array_map('sanitize_email', $emails));
        } else {
            $input['notification_emails'] = [];
        }
        return $input;
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'settings_page_ssl-alert-wp') {
            return;
        }

        wp_enqueue_script(
            'ssl-alert-wp-admin',
            SSL_ALERT_WP_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            SSL_ALERT_WP_VERSION,
            true
        );

        wp_localize_script('ssl-alert-wp-admin', 'wpSslAlert', [
            // translators: %s: checking text
            'checkingText' => esc_html__('Checking...', 'ssl-alert-wp'),
            // translators: %s: check now text
            'checkNowText' => esc_html__('Check Now', 'ssl-alert-wp'),
            // translators: %s: test notification text
            'testNotificationText' => esc_html__('Send Test Notification', 'ssl-alert-wp'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ssl_alert_wp_check'),
        ]);
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $last_check = $this->settings->get_last_check();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (!empty($last_check['time'])): ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    printf(
                        // translators: %s: last check time
                        esc_html__('Last check: %s', 'ssl-alert-wp'),
                        esc_html($last_check['time'])
                    );
                    if (!empty($last_check['result']) && isset($last_check['result']['message'])) {
                        echo '<br>';
                        echo esc_html($last_check['result']['message']);
                    }
                    ?>
                </p>
            </div>
            <?php endif; ?>

            <form action="options.php" method="post">
                <?php
                settings_fields('ssl_alert_wp');
                do_settings_sections('ssl-alert-wp');
                submit_button();
                ?>
            </form>

            <div class="ssl-alert-wp-check-now">
                <h2><?php echo esc_html__('Manual Check', 'ssl-alert-wp'); ?></h2>
                <p><?php echo esc_html__('Click the button below to check the SSL certificate status immediately:', 'ssl-alert-wp'); ?></p>
                <button type="button" class="button button-primary" id="ssl-alert-wp-check-now">
                    <?php echo esc_html__('Check Now', 'ssl-alert-wp'); ?>
                </button>
                <div id="ssl-alert-wp-check-result" class="hidden"></div>
            </div>

            <div class="ssl-alert-wp-test-notification">
                <h2><?php echo esc_html__('Test Notification', 'ssl-alert-wp'); ?></h2>
                <p><?php echo esc_html__('Click the button below to send a test notification:', 'ssl-alert-wp'); ?></p>
                <button type="button" class="button button-secondary" id="ssl-alert-wp-test-notification">
                    <?php echo esc_html__('Send Test Notification', 'ssl-alert-wp'); ?>
                </button>
                <div id="ssl-alert-wp-test-result" class="hidden"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render section info
     */
    public function render_section_info() {
        // translators: %s: configuration message
        echo '<p>' . esc_html__('Configure how and when you want to be notified about SSL certificate expiration.', 'ssl-alert-wp') . '</p>';
    }

    /**
     * Render URL field
     */
    public function render_url_field() {
        $url = $this->settings->get_monitored_url();
        ?>
        <input type="url" name="ssl_alert_wp_settings[monitored_url]" value="<?php echo esc_attr($url); ?>" class="regular-text">
        <p class="description">
            <?php echo esc_html__('The HTTPS URL to monitor. Leave empty to use your site URL.', 'ssl-alert-wp'); ?>
        </p>
        <?php
    }

    /**
     * Render notification days field
     */
    public function render_days_field() {
        $days = $this->settings->get_notification_days();
        ?>
        <input type="text" name="ssl_alert_wp_settings[notification_days]" value="<?php echo esc_attr(implode(',', $days)); ?>" class="regular-text">
        <p class="description">
            <?php echo esc_html__('Comma-separated list of days before expiration to send notifications. Example: 14,7,1', 'ssl-alert-wp'); ?>
        </p>
        <?php
    }

    /**
     * Render notification emails field
     */
    public function render_emails_field() {
        $emails = $this->settings->get_notification_emails();
        ?>
        <input type="text" name="ssl_alert_wp_settings[notification_emails]" value="<?php echo esc_attr(implode(',', $emails)); ?>" class="regular-text">
        <p class="description">
            <?php echo esc_html__('Comma-separated list of email addresses to notify. Leave empty to use admin email.', 'ssl-alert-wp'); ?>
        </p>
        <?php
    }

    /**
     * Handle AJAX certificate check
     */
    public function ajax_check_now() {
        check_ajax_referer('ssl_alert_wp_check');

        if (!current_user_can('manage_options')) {
            // translators: %s: unauthorized error
            wp_send_json_error(__('Unauthorized', 'ssl-alert-wp'));
        }
        // Execute the same check as daily cron
        $this->plugin->check_certificate();
        // Get the last check result for UI feedback
        $last_check = $this->settings->get_last_check();
        if (empty($last_check['result'])) {
            // translators: %s: check failed error
            wp_send_json_error(__('Check failed - no result available', 'ssl-alert-wp'));
            return;
        }

        $result = $last_check['result'];
        $class = 'notice-error'; // Default to error styling
        
        switch ($result['status']) {
            case 'valid':
                $class = 'notice-success';
                break;
            case 'warning':
                $class = 'notice-warning';
                break;
            // error status keeps the default notice-error class
        }
        
        $html = sprintf('<div class="notice %s"><p>%s</p></div>', esc_attr($class), esc_html($result['message']));

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Handle AJAX test notification
     */
    public function ajax_test_notification() {
        check_ajax_referer('ssl_alert_wp_check');

        if (!current_user_can('manage_options')) {
            // translators: %s: unauthorized error
            wp_send_json_error(__('Unauthorized', 'ssl-alert-wp'));
        }

        $emails = $this->settings->get_notification_emails();
        // translators: %s: test notification subject
        $subject = esc_html__('Test Notification from SSL Alert WP', 'ssl-alert-wp');
        // translators: %s: test notification message
        $message = esc_html__('This is a test notification from SSL Alert WP.', 'ssl-alert-wp');

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = wp_mail($emails, $subject, $message, $headers);

        if ($sent) {
            // translators: %s: test notification success message
            wp_send_json_success(esc_html__('Test notification sent successfully.', 'ssl-alert-wp'));
        } else {
            // translators: %s: test notification failure message
            wp_send_json_error(esc_html__('Failed to send test notification.', 'ssl-alert-wp'));
        }
    }
}
