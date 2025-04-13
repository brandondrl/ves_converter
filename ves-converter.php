<?php
/**
 * Plugin Name: VES Converter
 * Description: Plugin para la conversión de moneda usando tasas de cambio del Bolívar Soberano (VES) contra el Dólar Estadounidense (USD).
 * Version: 1.1.0
 * Author: IDSI
 * Author URI: https://grupoidsi.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ves-converter
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Definir constantes del plugin
define('VES_CONVERTER_VERSION', '1.1.0');
define('VES_CONVERTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VES_CONVERTER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos principales
require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Core/Plugin.php';
require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Models/ConverterModel.php';

// Usar namespace
use VesConverter\Models\ConverterModel;

// Registrar hook de activación
register_activation_hook(__FILE__, 'ves_converter_activate');

/**
 * Activación del plugin
 */
function ves_converter_activate() {
    // Crear tablas en la base de datos
    VesConverter\Models\ConverterModel::create_table();
    VesConverter\Models\ConverterModel::store_initial_rates();
    // Programar el cron job si no está ya programado
    if (!wp_next_scheduled('ves_converter_update_rates_event')) {
        // Programar para que se ejecute cada 5 minutos, y el callback decidirá si debe realmente ejecutarse
        wp_schedule_event(time(), 'ves_high_frequency', 'ves_converter_update_rates_event');
    }
}

/**
 * Desactivación del plugin
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

// Inicializar el plugin
function ves_converter_init() {
    $plugin = new VesConverter\Core\Plugin();
    $plugin->init();
}

// Hook al init de WordPress
add_action('plugins_loaded', 'ves_converter_init');

// Añadir handler AJAX para guardado de tasas
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
        'display' => __('Cada 5 Minutos (Alta Prioridad)', 'ves-converter')
    );
    
    $schedules['ves_normal_frequency'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS, // Cada 15 minutos (frecuencia normal)
        'display' => __('Cada 15 Minutos (Prioridad Normal)', 'ves-converter')
    );
    
    return $schedules;
}
add_filter('cron_schedules', 'ves_converter_custom_cron_schedules');
add_action('ves_converter_update_rates_event', 'ves_converter_cron_update_rates_callback');

function ves_converter_cron_update_rates_callback() {
    // Delegar toda la lógica al modelo
    VesConverter\Models\ConverterModel::process_scheduled_update();
}