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

// Use namespace
use VesConverter\Models\ConverterModel;

// Register activation hook
register_activation_hook(__FILE__, 'ves_converter_activate');

/**
 * Plugin activation
 */
function ves_converter_activate() {
    // Create database tables
    VesConverter\Models\ConverterModel::create_table();
    
    // Fetch and save initial rates
    fetch_and_save_initial_rates();
}

/**
 * Fetch and save initial rates from API
 */
function fetch_and_save_initial_rates() {
    // Get current rates from API
    $api_url = 'https://catalogo.grupoidsi.com/wp-json/ves-change-getter/v1/latest';
    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        error_log('VES Converter: Failed to connect to rates API during plugin activation.');
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data']['rates'])) {
        error_log('VES Converter: Invalid response from API during plugin activation.');
        return;
    }
    
    $api_rates = $data['data']['rates'];
    $current_time = current_time('mysql');
    
    // Por defecto, utilizamos la tasa BCV como seleccionada
    $rate_type = 'bcv';
    
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
            'value' => 0,
            'catch_date' => date('Y-m-d h:i:s A', strtotime('-4 hours', strtotime(gmdate('Y-m-d H:i:s')))),
            'selected' => false
        )
    );
    
    // Guardar en la base de datos
    global $wpdb;
    $table_name = $wpdb->prefix . 'ves_converter_rates';
    
    $wpdb->insert(
        $table_name,
        array(
            'rates' => json_encode($rates),
            'created_at' => $current_time,
            'updated_at' => $current_time
        ),
        array('%s', '%s', '%s')
    );
    
    if ($wpdb->last_error) {
        error_log('VES Converter Database Error during initial rates save: ' . $wpdb->last_error);
    }
}

// Initialize the plugin
function ves_converter_init() {
    $plugin = new VesConverter\Core\Plugin();
    $plugin->init();
}

// Hook to WordPress init
add_action('plugins_loaded', 'ves_converter_init');

// Add AJAX handler for rates save
add_action('wp_ajax_ves_converter_rate_save', 'ves_converter_rate_save_callback');
function ves_converter_rate_save_callback() {
    try {
        $result = ConverterModel::save_rate();
        wp_send_json_success(['message' => 'Operación completada', 'result' => $result]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Error: ' . $e->getMessage()]);
    }
       
}
