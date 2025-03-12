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
                'error' => __('No URL provided', 'ssl-alert-wp')
            ];
        }

        // Parse URL and ensure it's HTTPS
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['scheme']) || $parsed_url['scheme'] !== 'https') {
            return [
                'valid' => false,
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
            ]
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
                    __('Could not connect to %s: %s', 'ssl-alert-wp'),
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
                'error' => __('Could not parse SSL certificate', 'ssl-alert-wp')
            ];
        }

        // Calculate days until expiration
        $expiry_timestamp = $cert['validTo_time_t'];
        $current_timestamp = time();
        $days_remaining = floor(($expiry_timestamp - $current_timestamp) / (60 * 60 * 24));

        fclose($client);

        return [
            'valid' => true,
            'days_remaining' => $days_remaining,
            'expiry_date' => date('Y-m-d H:i:s', $expiry_timestamp),
            'issuer' => $cert['issuer']['O'] ?? __('Unknown', 'ssl-alert-wp'),
            'subject' => $cert['subject']['CN'] ?? $host,
        ];
    }

    /**
     * Manually trigger certificate check
     *
     * @param string $url URL to check
     * @return array Check results with formatted message
     */
    public function manual_check($url) {
        $result = $this->check($url);
        
        if (!$result['valid']) {
            return [
                'status' => 'error',
                'message' => $result['error']
            ];
        }

        if ($result['days_remaining'] <= 0) {
            return [
                'status' => 'expired',
                'message' => sprintf(
                    __('Certificate expired on %s', 'ssl-alert-wp'),
                    $result['expiry_date']
                )
            ];
        }

        return [
            'status' => 'valid',
            'message' => sprintf(
                __('Certificate is valid. Expires in %d days on %s. Issued by %s for %s.', 'ssl-alert-wp'),
                $result['days_remaining'],
                $result['expiry_date'],
                $result['issuer'],
                $result['subject']
            )
        ];
    }
}
