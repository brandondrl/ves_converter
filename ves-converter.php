<?php
/**
 * Plugin Name: VES Converter
 * Description: Plugin for currency conversion using exchange rates of the Sovereign Bolivar (VES) against the US Dollar (USD).
 * Version: 1.0.0
 * Author: Brand
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

// Initialize the plugin
function ves_converter_init() {
    $plugin = new VesConverter\Core\Plugin();
    $plugin->init();
}

// Hook to WordPress init
add_action('plugins_loaded', 'ves_converter_init');
