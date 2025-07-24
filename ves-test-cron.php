<?php
/**
 * Script para probar el cron job de VES Converter manualmente
 */

// Cargar WordPress
define('WP_USE_THEMES', false);
require_once dirname(dirname(dirname(__FILE__))) . '/wp-load.php';

// Verificar permisos de administrador
if (!current_user_can('manage_options')) {
    status_header(403);
    die('Acceso restringido. Debe ser administrador para acceder a esta página.');
}

// Incluir las clases necesarias
require_once __DIR__ . '/includes/Models/ConverterModel.php';

use VesConverter\Models\ConverterModel;

// Información de depuración sobre la configuración de tiempo
echo "WordPress Timezone: " . get_option('timezone_string') . "<br>";
echo "WordPress GMT Offset: " . get_option('gmt_offset') . "<br>";
echo "Current WordPress Time: " . current_time('mysql') . "<br>";
echo "Current Server Time: " . date('Y-m-d H:i:s') . "<br>";
echo "<hr>";

// Verificar si el cron está programado
$timestamp = wp_next_scheduled('ves_converter_update_rates_event');
echo "Next scheduled execution: " . ($timestamp ? date('Y-m-d H:i:s', $timestamp) : 'Not scheduled') . "<br>";
echo "<hr>";

// Probar la función de verificación de horario
echo "<h3>Testing should_run_update_by_schedule()</h3>";
$should_run = ConverterModel::should_run_update_by_schedule();
echo "Should run based on current time: " . ($should_run ? 'Yes' : 'No') . "<br>";
echo "<hr>";

// Ejecutar el update manualmente
echo "<h3>Testing process_scheduled_update()</h3>";
$result = ConverterModel::process_scheduled_update();
echo "Update result: " . ($result ? "Updated with ID: $result" : "No update performed") . "<br>";
echo "<hr>";

// Instrucciones para verificar el log de errores
echo "<p>Please check your error log for more detailed information.</p>";
echo "<p>Common locations for error logs:</p>";
echo "<ul>";
echo "<li>WordPress debug.log (if WP_DEBUG_LOG is enabled)</li>";
echo "<li>/var/log/apache2/error.log (Apache)</li>";
echo "<li>/var/log/nginx/error.log (Nginx)</li>";
echo "<li>Check your PHP configuration for error_log setting</li>";
echo "</ul>";

// Enlace para reiniciar el cron
echo "<hr>";
echo "<p><a href='ves-fix-cron.php' class='button'>Reiniciar programación de cron</a></p>";