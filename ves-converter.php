<?php
/**
 * Plugin Name: VES Converter
 * Description: Plugin for currency conversion using exchange rates of the Sovereign Bolivar (VES) against the US Dollar (USD).
 * Version: 1.0.0
 * Author: IDSI
 * Author URI: https://grupoidsi.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ves-converter
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('VES_CONVERTER_VERSION', '1.0.0');
define('VES_CONVERTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VES_CONVERTER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include core files
require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Core/Plugin.php';
require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Models/ConverterModel.php';

// Register activation hook
register_activation_hook(__FILE__, 'ves_converter_activate');

/**
 * Plugin activation
 */
function ves_converter_activate() {
    // Create database tables
    VesConverter\Models\ConverterModel::create_tables();
}

// Initialize the plugin
function ves_converter_init() {
    $plugin = new VesConverter\Core\Plugin();
    $plugin->init();
}

// Hook to WordPress init
add_action('plugins_loaded', 'ves_converter_init');

// Add AJAX handler for updating rates
add_action('wp_ajax_ves_converter_update_rates', 'ves_converter_update_rates_callback');
function ves_converter_update_rates_callback() {
    check_ajax_referer('ves_converter_update_rates', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ves-converter')));
    }
    
    $api_url = 'https://catalogo.grupoidsi.com/wp-json/ves-change-getter/v1/latest';
    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data']['rates'])) {
        wp_send_json_error(array('message' => __('Invalid response from API.', 'ves-converter')));
    }
    
    $rates = $data['data']['rates'];
    $user_id = get_current_user_id();
    
    // Save each rate type
    foreach ($rates as $type => $rate_data) {
        if (isset($rate_data['value'])) {
            ConverterModel::save_rate($user_id, $type, $rate_data['value']);
        }
    }
    
    wp_send_json_success();
}

// Add AJAX handler for test save
add_action('wp_ajax_ves_converter_test_save', 'ves_converter_test_save_callback');
function ves_converter_test_save_callback() {
    check_ajax_referer('ves_converter_test_save', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ves-converter')));
    }
    
    $rate_type = isset($_POST['rate_type']) ? sanitize_text_field($_POST['rate_type']) : '';
    $custom_rate = isset($_POST['custom_rate']) ? floatval($_POST['custom_rate']) : 0;
    
    if (empty($rate_type)) {
        wp_send_json_error(array('message' => __('Rate type is required.', 'ves-converter')));
    }
    
    // Get current rates from API
    $api_url = 'https://catalogo.grupoidsi.com/wp-json/ves-change-getter/v1/latest';
    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data']['rates'])) {
        wp_send_json_error(array('message' => __('Invalid response from API.', 'ves-converter')));
    }
    
    $api_rates = $data['data']['rates'];
    $current_time = current_time('mysql');
    
    // Prepare rates data
    $rates = array(
        'bcv' => array(
            'value' => isset($api_rates['bcv']['value']) ? $api_rates['bcv']['value'] : 0,
            'catch_date' => isset($api_rates['bcv']['catch_date']) ? $api_rates['bcv']['catch_date'] : $current_time,
            'selected' => ($rate_type === 'bcv')
        ),
        'parallel' => array(
            'value' => isset($api_rates['parallel']['value']) ? $api_rates['parallel']['value'] : 0,
            'catch_date' => isset($api_rates['parallel']['catch_date']) ? $api_rates['parallel']['catch_date'] : $current_time,
            'selected' => ($rate_type === 'parallel')
        ),
        'average' => array(
            'value' => isset($api_rates['average']['value']) ? $api_rates['average']['value'] : 0,
            'catch_date' => isset($api_rates['average']['catch_date']) ? $api_rates['average']['catch_date'] : $current_time,
            'selected' => ($rate_type === 'average')
        ),
        'custom' => array(
            'value' => ($rate_type === 'custom') ? $custom_rate : 0,
            'catch_date' => date('Y-m-d h:i:s A', strtotime('-4 hours', strtotime(gmdate('Y-m-d H:i:s')))),
            'selected' => ($rate_type === 'custom')
        )
    );
    
    if ($rate_type === 'custom' && $custom_rate <= 0) {
        wp_send_json_error(array('message' => __('Custom rate must be greater than 0.', 'ves-converter')));
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ves_converter_rates';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'rates' => json_encode($rates),
            'created_at' => $current_time,
            'updated_at' => $current_time
        ),
        array('%s', '%s', '%s')
    );
    
    if ($result === false) {
        wp_send_json_error(array('message' => $wpdb->last_error));
    }
    
    wp_send_json_success();
}
