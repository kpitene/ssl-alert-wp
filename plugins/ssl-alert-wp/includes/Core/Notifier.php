<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles notifications
 */
class Notifier {
    /**
     * @var Settings
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = new Settings();
    }

    /**
     * Send expiry notification
     *
     * @param int $days_remaining Days until certificate expiry
     * @param string $expiry_date Certificate expiry date
     * @return bool Whether the notification was sent successfully
     */
    public function send_expiry_notification($days_remaining, $expiry_date) {
        $site_name = get_bloginfo('name');
        $site_url = $this->settings->get_monitored_url();

        if ($days_remaining <= 0) {
            $subject = sprintf(
                __('[%s] SSL Certificate Has Expired', 'ssl-alert-wp'),
                $site_name
            );
            $message = sprintf(
                __('The SSL certificate for %s has expired on %s. Please renew it immediately to avoid browser warnings and potential security risks.', 'ssl-alert-wp'),
                $site_url,
                $expiry_date
            );
        } else {
            $subject = sprintf(
                __('[%s] SSL Certificate Expires in %d Days', 'ssl-alert-wp'),
                $site_name,
                $days_remaining
            );
            $message = sprintf(
                __('The SSL certificate for %s will expire on %s (%d days from now). Please ensure it is renewed before expiration to avoid browser warnings and potential security risks.', 'ssl-alert-wp'),
                $site_url,
                $expiry_date,
                $days_remaining
            );
        }

        return $this->send_notification($subject, $message);
    }

    /**
     * Send error notification
     *
     * @param string $error Error message
     * @return bool Whether the notification was sent successfully
     */
    public function send_error_notification($error) {
        $site_name = get_bloginfo('name');
        $site_url = $this->settings->get_monitored_url();

        $subject = sprintf(
            __('[%s] SSL Certificate Check Failed', 'ssl-alert-wp'),
            $site_name
        );
        
        $message = sprintf(
            __('Failed to check SSL certificate for %s. Error: %s', 'ssl-alert-wp'),
            $site_url,
            $error
        );

        return $this->send_notification($subject, $message);
    }

    /**
     * Send notification email
     *
     * @param string $subject Email subject
     * @param string $message Email message
     * @return bool Whether the email was sent successfully
     */
    private function send_notification($subject, $message) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <%s>', get_bloginfo('name'), get_option('admin_email'))
        ];

        $message = $this->get_email_template($message);
        
        $emails = $this->settings->get_notification_emails();
        if (empty($emails)) {
            return false;
        }

        return wp_mail($emails, $subject, $message, $headers);
    }

    /**
     * Get HTML email template
     *
     * @param string $content Email content
     * @return string Formatted HTML email
     */
    private function get_email_template($content) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { background: #fff; padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?php echo get_bloginfo('name'); ?></h1>
            </div>
            <div class="content">
                <?php echo wpautop($content); ?>
            </div>
            <div class="footer">
                <?php echo sprintf(
                    __('This notification was sent by the SSL Alert WP plugin. You can configure notification settings in the WordPress admin panel at %s.', 'ssl-alert-wp'),
                    admin_url('options-general.php?page=ssl-alert-wp')
                ); ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
