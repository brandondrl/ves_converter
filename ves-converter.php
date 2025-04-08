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
    // Create or update the database table
    require_once plugin_dir_path(__FILE__) . 'includes/class-converter-model.php';
    ConverterModel::create_table();
    
    // Add default options if they don't exist
    if (!get_option('ves_converter_default_rate_type')) {
        update_option('ves_converter_default_rate_type', 'bcv');
    }
}

// Hook to WordPress deactivation
register_deactivation_hook(__FILE__, 'ves_converter_deactivate');
function ves_converter_deactivate() {
    // We don't delete the table on deactivation to preserve data
    // If you want to delete the table, uncomment the following lines:
    /*
    global $wpdb;
    $table_name = $wpdb->prefix . 'ves_converter_rates';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    */
}

// Hook to WordPress uninstall
register_uninstall_hook(__FILE__, 'ves_converter_uninstall');
function ves_converter_uninstall() {
    // Delete the table and all plugin data
    global $wpdb;
    $table_name = $wpdb->prefix . 'ves_converter_rates';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Delete plugin options
    delete_option('ves_converter_default_rate_type');
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
