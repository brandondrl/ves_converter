<?php
namespace VesConverter\Core;

/**
 * The shortcode functionality of the plugin
 */
class Shortcode {
    /**
     * Register the shortcode
     */
    public function register() {
        add_shortcode('ves_converter', [$this, 'render_converter']);
    }

    /**
     * Render the converter shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string The shortcode output
     */
    public function render_converter($atts) {
        // Extract attributes
        $atts = shortcode_atts([
            'default_rate' => 'bcv', // Default rate type: bcv, average, parallel
        ], $atts, 'ves_converter');
        
        // Get latest rates
        $rates = $this->get_latest_rates();
        
        // Start output buffering
        ob_start();
        
        // Include the view
        include VES_CONVERTER_PLUGIN_DIR . 'views/public/converter.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Get latest rates from VES Change Getter plugin
     * 
     * @return array Array of rates or empty array if not available
     */
    private function get_latest_rates() {
        $default_rates = [
            'bcv' => 0,
            'average' => 0,
            'parallel' => 0
        ];
        
        // Try to get rates from VES Change Getter
        $api_url = rest_url('ves-change-getter/v1/latest');
        $response = wp_remote_get($api_url);
        
        if (is_wp_error($response)) {
            return $default_rates;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['rates'])) {
            return $default_rates;
        }
        
        return $data['rates'];
    }
} 