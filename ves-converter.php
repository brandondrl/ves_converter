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
    VesConverter\Models\ConverterModel::store_initial_rates();
    // Programar el cron job si no está ya programado
    if (!wp_next_scheduled('ves_converter_update_rates_event')) {
        // Programar para que se ejecute cada 5 minutos, y el callback decidirá si debe realmente ejecutarse
        wp_schedule_event(time(), 'ves_high_frequency', 'ves_converter_update_rates_event');
    }
}

/**
 * Plugin deactivation
 */
function ves_converter_deactivate() {
    // Cancelar el cron al desactivar el plugin
    $timestamp = wp_next_scheduled('ves_converter_update_rates_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'ves_converter_update_rates_event');
    }
}

// Registrar hook de desactivación
register_deactivation_hook(__FILE__, 'ves_converter_deactivate');

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
    /**
 * Registra intervalos personalizados de cron basados en hora y día
 * 
 * @param array $schedules Horarios existentes de WordPress
 * @return array Horarios actualizados
 */
function ves_converter_custom_cron_schedules($schedules) {
    // Horarios para las diferentes franjas
    $schedules['ves_high_frequency'] = array(
        'interval' => 5 * MINUTE_IN_SECONDS,  // Cada 5 minutos (alta frecuencia)
        'display' => __('Every 5 Minutes (High Priority)', 'ves-converter')
    );
    
    $schedules['ves_normal_frequency'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS, // Cada 15 minutos (frecuencia normal)
        'display' => __('Every 15 Minutes (Normal Priority)', 'ves-converter')
    );
    
    return $schedules;
}
add_filter('cron_schedules', 'ves_converter_custom_cron_schedules');
add_action('ves_converter_update_rates_event', 'ves_converter_cron_update_rates_callback');

function ves_converter_cron_update_rates_callback() {
    // Delegar toda la lógica al modelo
    VesConverter\Models\ConverterModel::process_scheduled_update();
}