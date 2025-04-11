<?php
namespace VesConverter\Core;

use VesConverter\Models\ConverterModel;

/**
 * The shortcode functionality of the plugin
 */
class Shortcode {
    /**
     * Register the shortcode
     */
    public function register() {
        add_shortcode('ves_converter', [$this, 'render_converter']);
        
        // Registrar el shortcode para el switch de moneda
        add_shortcode('ves_currency_switch', [$this, 'render_currency_switch']);
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
     * Render the currency switch shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string The shortcode output
     */
    public function render_currency_switch($atts) {
        // Extract attributes
        $atts = shortcode_atts([
            'position' => 'bottom-right', // Posición del botón: bottom-right, bottom-left, top-right, top-left
            'initial_currency' => 'usd', // Moneda inicial: usd, bs
        ], $atts, 'ves_currency_switch');
        
        // Obtener las tasas actuales
        $rates_data = $this->get_rate_data();
        
        // Registrar scripts y estilos
        $this->register_currency_switch_assets($rates_data, $atts);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        include VES_CONVERTER_PLUGIN_DIR . 'views/public/currency-switch.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Get latest rates from database
     * 
     * @return array Array of rates or empty array if not available
     */
    private function get_latest_rates() {
        $default_rates = [
            'bcv' => 0,
            'average' => 0,
            'parallel' => 0
        ];
        
        // Try to get rates from the database using the model
        if (class_exists('VesConverter\\Models\\ConverterModel')) {
            $rates_data = ConverterModel::get_latest_rates();
            
            if ($rates_data) {
                $processed_rates = [];
                
                // Extraer los valores de cada tipo de tasa
                foreach (['bcv', 'parallel', 'average', 'custom'] as $rate_type) {
                    if (isset($rates_data[$rate_type]) && isset($rates_data[$rate_type]['value'])) {
                        $processed_rates[$rate_type] = floatval($rates_data[$rate_type]['value']);
                    }
                }
                
                if (!empty($processed_rates)) {
                    return $processed_rates;
                }
            }
        }
        
        // Fallback to API if database fails
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
    
    /**
     * Get rate data for currency switch
     * 
     * @return array Rate data with values and selected rate
     */
    private function get_rate_data() {
        // Valores por defecto
        $default_data = [
            'rates' => [
                'bcv' => 0,
                'average' => 0,
                'parallel' => 0,
                'custom' => 0
            ],
            'selected' => 'bcv'
        ];
        
        // Obtener datos de tasas del modelo
        if (class_exists('VesConverter\\Models\\ConverterModel')) {
            $rates = ConverterModel::get_latest_rates();
            
            if ($rates) {
                $processed_rates = [];
                $selected_type = 'bcv'; // Valor por defecto
                
                // Extraer los valores de cada tipo de tasa
                foreach (['bcv', 'parallel', 'average', 'custom'] as $rate_type) {
                    if (isset($rates[$rate_type])) {
                        $processed_rates[$rate_type] = floatval($rates[$rate_type]['value']);
                        
                        // Determinar cuál es la tasa seleccionada
                        if (isset($rates[$rate_type]['selected']) && $rates[$rate_type]['selected']) {
                            $selected_type = $rate_type;
                        }
                    }
                }
                
                return [
                    'rates' => $processed_rates,
                    'selected' => $selected_type
                ];
            }
        }
        
        return $default_data;
    }
    
    /**
     * Register and enqueue assets for currency switch
     * 
     * @param array $rates_data Rate data
     * @param array $atts Shortcode attributes
     */
    private function register_currency_switch_assets($rates_data, $atts) {
        // Registrar y cargar el script
        wp_enqueue_script(
            'ves-currency-switch-js',
            VES_CONVERTER_PLUGIN_URL . 'assets/js/currency-switch.js',
            ['jquery'],
            VES_CONVERTER_VERSION,
            true
        );
        
        // Pasar datos al script
        wp_localize_script('ves-currency-switch-js', 'vesCurrencyData', [
            'rates' => $rates_data['rates'],
            'selected_rate' => $rates_data['selected'],
            'rate_value' => isset($rates_data['rates'][$rates_data['selected']]) ? $rates_data['rates'][$rates_data['selected']] : 0,
            'initial_currency' => $atts['initial_currency'],
            'position' => $atts['position'],
            'symbols' => [
                'usd' => '$',
                'bs' => 'Bs.'
            ]
        ]);
        
        // Registrar y cargar los estilos
        wp_enqueue_style(
            'ves-currency-switch-css',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/currency-switch.css',
            [],
            VES_CONVERTER_VERSION
        );
    }
} 