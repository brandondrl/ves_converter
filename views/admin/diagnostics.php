<?php
// Verificar que el usuario tenga permisos de administrador
if (!current_user_can('manage_options')) {
    wp_die(__('Acceso denegado. Necesitas permisos de administrador para acceder a esta página.', 'ves-converter'));
}

use VesConverter\Models\ConverterModel;

// Load TestRunner class
require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Admin/TestRunner.php';
use VesConverter\Admin\TestRunner;

// Get next scheduled cron time
$next_scheduled = wp_next_scheduled('ves_converter_update_rates_event');
$formatted_next_scheduled = $next_scheduled ? date_i18n('d/m/Y h:i:s A', $next_scheduled) : 'No programado';

// Get current time
$current_time = current_time('timestamp');
$formatted_current_time = date_i18n('d/m/Y h:i:s A', $current_time);

// Check if the current time is within a scheduled window
$in_schedule_window = ConverterModel::should_run_update_by_schedule();

// Get all cron jobs
$cron_jobs = _get_cron_array();
$ves_cron_jobs = [];

if (is_array($cron_jobs)) {
    foreach ($cron_jobs as $timestamp => $cron) {
        if (isset($cron['ves_converter_update_rates_event'])) {
            foreach ($cron['ves_converter_update_rates_event'] as $hook => $event) {
                $ves_cron_jobs[] = [
                    'timestamp' => $timestamp,
                    'formatted_time' => date_i18n('d/m/Y h:i:s A', $timestamp),
                    'schedule' => $event['schedule'] ?? 'once',
                    'interval' => $event['interval'] ?? 0
                ];
            }
        }
    }
}

// Get the cron schedule definitions
$schedules = wp_get_schedules();

// Execute test if requested
$test_result = null;
$test_message = '';
$test_details = null;
if (isset($_GET['test_cron']) && $_GET['test_cron'] === '1' && check_admin_referer('ves_test_cron')) {
    $test_details = TestRunner::test_rate_update();
    $test_result = $test_details['result'];
    
    if ($test_result === false) {
        $test_message = 'La prueba de actualización no ejecutó ninguna actualización. Ver detalles abajo.';
    } else {
        $test_message = 'La prueba de actualización se ejecutó exitosamente. ID de registro creado: ' . $test_result;
    }
}

// Force schedule check if requested
$reschedule_message = '';
if (isset($_GET['force_schedule']) && $_GET['force_schedule'] === '1' && check_admin_referer('ves_force_schedule')) {
    $reschedule_result = TestRunner::force_cron_reschedule();
    
    if ($reschedule_result['success']) {
        $reschedule_message = 'Tarea programada exitosamente. ' . $reschedule_result['message'];
    } else {
        $reschedule_message = 'Error al programar la tarea: ' . $reschedule_result['message'];
    }
    
    // Redirect to remove the query string and avoid re-scheduling on refresh
    if (empty($_GET['test_cron'])) {
        wp_redirect(remove_query_arg(['force_schedule', '_wpnonce']));
        exit;
    }
}

// Get logs from error_log file
$log_entries = [];

// Try several common log file locations
$possible_logs = [
    WP_CONTENT_DIR . '/debug.log',
    ABSPATH . 'wp-content/debug.log',
    WP_CONTENT_DIR . '/logs/debug.log',
    dirname(ABSPATH) . '/logs/debug.log',
    ini_get('error_log')
];

$log_content = '';
$found_log = false;

foreach ($possible_logs as $log_path) {
    if ($log_path && file_exists($log_path) && is_readable($log_path)) {
        // Get only the last portion of the file to avoid memory issues with large logs
        $file_size = filesize($log_path);
        $max_size = 500 * 1024; // 500KB max
        
        $handle = fopen($log_path, 'r');
        if ($handle) {
            if ($file_size > $max_size) {
                fseek($handle, -$max_size, SEEK_END);
                // Skip current line to start from a fresh line
                fgets($handle);
            }
            
            while (!feof($handle)) {
                $log_content .= fgets($handle);
            }
            
            fclose($handle);
            $found_log = true;
            break;
        }
    }
}

if ($found_log && !empty($log_content)) {
    // Try different log patterns (standard WordPress, custom formats, etc.)
    $patterns = [
        // Standard WordPress debug.log format
        '/\[(\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2}).*?\].*?(VES Converter.*?)(?=\n\[|$)/s',
        // Alternative datetime format
        '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).*?\].*?(VES Converter.*?)(?=\n\[|$)/s',
        // Common PHP error_log format
        '/^\[([^\]]+)\](.+?VES Converter.+?)$/m',
        // Very simple format (just look for VES Converter)
        '/^(.+?) VES Converter(.+?)$/m'
    ];
    
    $matched = false;
    
    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $log_content, $matches, PREG_SET_ORDER);
        
        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (count($match) >= 3) {
                    $log_entries[] = [
                        'timestamp' => $match[1],
                        'message' => $match[2]
                    ];
                } elseif (count($match) == 2 && strpos($match[1], 'VES Converter') !== false) {
                    // Simple format fallback
                    $log_entries[] = [
                        'timestamp' => 'Unknown',
                        'message' => $match[1]
                    ];
                }
            }
            $matched = true;
            break;
        }
    }
    
    // If no patterns matched, try a very loose pattern as last resort
    if (!$matched) {
        preg_match_all('/VES Converter.*$/m', $log_content, $simple_matches);
        if (!empty($simple_matches[0])) {
            foreach ($simple_matches[0] as $match) {
                $log_entries[] = [
                    'timestamp' => 'Unknown',
                    'message' => $match
                ];
            }
        }
    }
    
    // Sort logs by time (latest first) - only if we have timestamps
    if (!empty($log_entries) && $log_entries[0]['timestamp'] !== 'Unknown') {
        usort($log_entries, function($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });
    }
    
    // Limit to last 100 entries
    $log_entries = array_slice($log_entries, 0, 100);
}
?>

<div class="wrap bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="ml-2">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php _e('Diagnóstico de VES Converter', 'ves-converter'); ?></h1>
                <p class="text-sm text-gray-600"><?php _e('Herramientas y diagnóstico para depurar y verificar el funcionamiento del plugin', 'ves-converter'); ?></p>
            </div>
        </div>

        <?php if (!empty($test_message)) : ?>
            <div class="mb-4 p-4 border-l-4 <?php echo $test_result === false ? 'border-yellow-500 bg-yellow-50' : 'border-green-500 bg-green-50'; ?>">
                <p class="text-sm <?php echo $test_result === false ? 'text-yellow-700' : 'text-green-700'; ?>"><?php echo esc_html($test_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($reschedule_message)) : ?>
            <div class="mb-4 p-4 border-l-4 border-blue-500 bg-blue-50">
                <p class="text-sm text-blue-700"><?php echo esc_html($reschedule_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($test_details)): ?>
        <!-- Detailed Test Results -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Resultados Detallados de Prueba', 'ves-converter'); ?></h2>
            
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-2"><?php _e('Información de Diagnóstico', 'ves-converter'); ?></h3>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                        <div class="col-span-2 py-1 border-b border-gray-200">
                            <dt class="text-sm font-medium text-gray-500"><?php _e('Timestamp de prueba', 'ves-converter'); ?></dt>
                            <dd class="text-sm text-gray-900"><?php echo esc_html($test_details['timestamp']); ?></dd>
                        </div>
                        
                        <div class="py-1">
                            <dt class="text-sm font-medium text-gray-500"><?php _e('¿Debería ejecutarse?', 'ves-converter'); ?></dt>
                            <dd class="text-sm">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $test_details['should_run'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $test_details['should_run'] ? __('Sí', 'ves-converter') : __('No', 'ves-converter'); ?>
                                </span>
                            </dd>
                        </div>
                        
                        <div class="py-1">
                            <dt class="text-sm font-medium text-gray-500"><?php _e('Resultado', 'ves-converter'); ?></dt>
                            <dd class="text-sm">
                                <?php if ($test_details['result'] === false): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                    <?php _e('Sin actualización', 'ves-converter'); ?>
                                </span>
                                <?php else: ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    <?php echo sprintf(__('Actualizado (ID: %d)', 'ves-converter'), $test_details['result']); ?>
                                </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        
                        <?php if (!empty($test_details['diagnostics'])): 
                            foreach ($test_details['diagnostics'] as $key => $value):
                                if ($key === 'rate_comparison') continue; // Handle separately
                        ?>
                            <div class="py-1">
                                <dt class="text-sm font-medium text-gray-500"><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?></dt>
                                <dd class="text-sm text-gray-900">
                                    <?php 
                                    if (is_bool($value)) {
                                        echo $value ? __('Sí', 'ves-converter') : __('No', 'ves-converter');
                                    } elseif (is_array($value)) {
                                        echo esc_html(json_encode($value, JSON_PRETTY_PRINT));
                                    } else {
                                        echo esc_html($value);
                                    }
                                    ?>
                                </dd>
                            </div>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </dl>
                    
                    <?php if (!empty($test_details['diagnostics']['rate_comparison'])): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-2"><?php _e('Comparación de Tasas', 'ves-converter'); ?></h4>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Tipo', 'ves-converter'); ?></th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('API', 'ves-converter'); ?></th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('DB', 'ves-converter'); ?></th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Diferencia', 'ves-converter'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($test_details['diagnostics']['rate_comparison'] as $type => $comparison): ?>
                                <tr>
                                    <td class="py-2 px-3"><?php echo esc_html(ucfirst($type)); ?></td>
                                    <td class="py-2 px-3"><?php echo number_format($comparison['api_value'], 4); ?></td>
                                    <td class="py-2 px-3"><?php echo number_format($comparison['db_value'], 4); ?></td>
                                    <td class="py-2 px-3">
                                        <span class="<?php echo $comparison['difference'] > 0.001 ? 'text-red-600 font-medium' : 'text-gray-500'; ?>">
                                            <?php echo number_format($comparison['difference'], 4); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-2"><?php _e('Registro de Ejecución', 'ves-converter'); ?></h3>
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <ol class="list-decimal list-inside space-y-1">
                        <?php foreach ($test_details['logs'] as $log): ?>
                        <li class="text-sm text-gray-700"><?php echo esc_html($log); ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Current Status -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Estado Actual', 'ves-converter'); ?></h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600"><?php _e('Hora actual del servidor:', 'ves-converter'); ?></span>
                        <span class="font-medium"><?php echo esc_html($formatted_current_time); ?></span>
                    </div>
                    
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600"><?php _e('Próxima ejecución programada:', 'ves-converter'); ?></span>
                        <span class="font-medium"><?php echo esc_html($formatted_next_scheduled); ?></span>
                    </div>
                    
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600"><?php _e('En ventana de ejecución:', 'ves-converter'); ?></span>
                        <span class="font-medium px-2 py-1 rounded-full text-xs <?php echo $in_schedule_window ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $in_schedule_window ? __('Sí', 'ves-converter') : __('No', 'ves-converter'); ?>
                        </span>
                    </div>
                </div>
                
                <div class="mt-6 space-y-2">
                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('test_cron', '1'), 'ves_test_cron')); ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded text-sm transition-colors duration-150">
                        <?php _e('Ejecutar Actualización de Prueba', 'ves-converter'); ?>
                    </a>
                    
                    <?php if (!$next_scheduled) : ?>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('force_schedule', '1'), 'ves_force_schedule')); ?>" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded text-sm transition-colors duration-150">
                            <?php _e('Forzar Programación de Cron', 'ves-converter'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Scheduled Cron Jobs -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Tareas Programadas', 'ves-converter'); ?></h2>
                
                <?php if (empty($ves_cron_jobs)) : ?>
                    <div class="text-center py-6 bg-red-50 rounded-lg">
                        <p class="text-red-700"><?php _e('No se encontraron tareas programadas para VES Converter.', 'ves-converter'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Próxima Ejecución', 'ves-converter'); ?></th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Frecuencia', 'ves-converter'); ?></th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Intervalo', 'ves-converter'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($ves_cron_jobs as $job) : ?>
                                    <tr>
                                        <td class="py-2 px-3 text-sm"><?php echo esc_html($job['formatted_time']); ?></td>
                                        <td class="py-2 px-3 text-sm">
                                            <?php 
                                            if ($job['schedule'] === 'once') {
                                                echo 'Una vez';
                                            } else {
                                                echo isset($schedules[$job['schedule']]['display']) ? 
                                                    esc_html($schedules[$job['schedule']]['display']) : 
                                                    esc_html($job['schedule']);
                                            }
                                            ?>
                                        </td>
                                        <td class="py-2 px-3 text-sm">
                                            <?php 
                                            if ($job['interval'] > 0) {
                                                // Format interval in minutes
                                                echo esc_html(floor($job['interval'] / 60) . ' minutos');
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Log Entries -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800"><?php _e('Últimos Registros de Actividad', 'ves-converter'); ?></h2>
                <div class="text-xs text-gray-500">
                    <?php 
                    if (!empty($log_entries)) {
                        _e('Mostrando últimas 100 entradas', 'ves-converter');
                    }
                    ?>
                </div>
            </div>
            
            <?php if (empty($log_entries)) : ?>
                <div class="text-center py-6 bg-gray-50 rounded-lg">
                    <p class="text-gray-700"><?php _e('No se encontraron registros de actividad.', 'ves-converter'); ?></p>
                    <p class="text-sm text-gray-500 mt-2"><?php _e('Asegúrate de que el registro de errores esté habilitado y sea accesible.', 'ves-converter'); ?></p>
                </div>
            <?php else : ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Fecha y Hora', 'ves-converter'); ?></th>
                                <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase"><?php _e('Mensaje', 'ves-converter'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($log_entries as $entry) : ?>
                                <tr>
                                    <td class="py-2 px-3 text-sm whitespace-nowrap"><?php echo esc_html($entry['timestamp']); ?></td>
                                    <td class="py-2 px-3 text-sm">
                                        <div class="<?php echo (strpos($entry['message'], 'Error') !== false) ? 'text-red-600' : ''; ?>">
                                            <?php echo esc_html($entry['message']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Schedule Explanation -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php _e('Horarios Programados de Ejecución', 'ves-converter'); ?></h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-700 mb-2"><?php _e('Sesión Matutina', 'ves-converter'); ?></h3>
                    <ul class="list-disc pl-5 text-gray-600 text-sm">
                        <li><?php _e('8:45 AM', 'ves-converter'); ?></li>
                        <li><?php _e('9:20 AM', 'ves-converter'); ?></li>
                        <li><?php _e('10:00 AM', 'ves-converter'); ?></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-700 mb-2"><?php _e('Sesión de Mediodía', 'ves-converter'); ?></h3>
                    <ul class="list-disc pl-5 text-gray-600 text-sm">
                        <li><?php _e('12:45 PM', 'ves-converter'); ?></li>
                        <li><?php _e('1:20 PM', 'ves-converter'); ?></li>
                        <li><?php _e('2:00 PM', 'ves-converter'); ?></li>
                    </ul>
                </div>
                
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-sm text-gray-500">
                        <?php _e('Nota: El sistema ejecutará la comprobación de actualizaciones en estos horarios específicos durante días laborables (lunes a viernes), con un margen de ±2 minutos. Solo se guardarán registros nuevos si se detecta un cambio en la tasa del dólar.', 'ves-converter'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 