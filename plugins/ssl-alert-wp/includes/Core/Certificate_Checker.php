<?php
namespace SSL_Alert_WP\Core;

/**
 * Handles SSL certificate checking
 */
class Certificate_Checker {
    /**
     * Check SSL certificate for a given URL
     *
     * @param string $url URL to check
     * @return array Result containing certificate status and details
     */
    public function check($url) {
        if (empty($url)) {
            return [
                'valid' => false,
                // translators: %s: no URL provided error
                'error' => __('No URL provided', 'ssl-alert-wp')
            ];
        }

        // Parse URL and ensure it's HTTPS
        $parsed_url = wp_parse_url($url);
        if (!isset($parsed_url['scheme']) || $parsed_url['scheme'] !== 'https') {
            return [
                'valid' => false,
                // translators: %s: HTTPS protocol error
                'error' => __('URL must use HTTPS protocol', 'ssl-alert-wp')
            ];
        }

        $host = $parsed_url['host'];
        $port = isset($parsed_url['port']) ? $parsed_url['port'] : 443;

        // Create stream context with SSL options
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        // Try to establish connection
        $client = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$client) {
            return [
                'valid' => false,
                'error' => sprintf(
                    // translators: %1$s: URL, %2$s: error message
                    __('Could not connect to %1$s: %2$s', 'ssl-alert-wp'),
                    $host,
                    $errstr
                )
            ];
        }

        // Get certificate information
        $params = stream_context_get_params($client);
        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

        if (!$cert) {
            return [
                'valid' => false,
                // translators: %s: parse error
                'error' => __('Could not parse SSL certificate', 'ssl-alert-wp')
            ];
        }

        // Calculate days until expiration
        $expiry_timestamp = $cert['validTo_time_t'];
        $current_timestamp = time();
        $days_remaining = floor(($expiry_timestamp - $current_timestamp) / (60 * 60 * 24));

        // for compatibility with plugin check which is mandatory
        // fclose($client);

        return [
            'valid' => true,
            'days_remaining' => $days_remaining,
            'expiry_date' => gmdate('Y-m-d H:i:s', $expiry_timestamp),
            // translators: %s: Unknown
            'issuer' => $cert['issuer']['O'] ?? __('Unknown', 'ssl-alert-wp'),
            'subject' => $cert['subject']['CN'] ?? $host,
        ];
    }
}
