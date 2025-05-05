<?php
namespace VesConverter\Admin;

use VesConverter\Models\ConverterModel;

/**
 * Test Runner Utility for VES Converter
 * 
 * This class provides methods to test various aspects of the plugin
 * functionality, especially focusing on cron jobs and scheduling.
 */
class TestRunner {
    
    /**
     * Verifica que el usuario actual tenga permisos de administrador
     * 
     * @return bool True si el usuario es administrador, False si no
     */
    private static function verify_admin_permissions() {
        if (!current_user_can('manage_options')) {
            error_log('VES Converter: Unauthorized access attempt to TestRunner by user ID: ' . get_current_user_id());
            return false;
        }
        return true;
    }
    
    /**
     * Run a test of the rate update process
     * 
     * This method will execute the cron update process immediately
     * and return detailed diagnostic information.
     * 
     * @return array Test results with diagnostics information
     */
    public static function test_rate_update() {
        // Verificar permisos de administrador
        if (!self::verify_admin_permissions()) {
            return [
                'error' => 'Acceso denegado. Se requieren permisos de administrador.',
                'timestamp' => current_time('mysql')
            ];
        }
        
        $results = [
            'timestamp' => current_time('mysql'),
            'should_run' => false,
            'result' => false,
            'logs' => [],
            'diagnostics' => []
        ];
        
        // Start logging
        $results['logs'][] = 'Iniciando prueba de actualización de tasas';
        
        // Test if we're in a scheduled window
        $current_timestamp = current_time('timestamp');
        $current_time = date('H:i:s', $current_timestamp);
        $current_day = intval(date('w', $current_timestamp));
        
        $results['diagnostics']['current_time'] = $current_time;
        $results['diagnostics']['current_day'] = $current_day;
        $results['diagnostics']['day_name'] = date('l', $current_timestamp);
        
        // Check if should run by schedule
        $should_run = ConverterModel::should_run_update_by_schedule();
        $results['should_run'] = $should_run;
        
        if (!$should_run) {
            $results['logs'][] = 'La prueba indica que no se debería ejecutar en este momento según el horario configurado';
        } else {
            $results['logs'][] = 'La prueba indica que se debería ejecutar en este momento';
        }
        
        // Get the next scheduled event
        $next_scheduled = wp_next_scheduled('ves_converter_update_rates_event');
        if ($next_scheduled) {
            $results['diagnostics']['next_scheduled'] = date('Y-m-d H:i:s', $next_scheduled);
            $results['logs'][] = 'Próxima ejecución programada: ' . date('Y-m-d H:i:s', $next_scheduled);
        } else {
            $results['diagnostics']['next_scheduled'] = 'No programado';
            $results['logs'][] = 'Advertencia: No hay ninguna ejecución programada. El cron puede estar deshabilitado.';
        }
        
        // Get latest rates
        $latest_rates = ConverterModel::get_latest_rates();
        if ($latest_rates) {
            $results['diagnostics']['has_latest_rates'] = true;
            
            // Check if using custom rate
            $is_custom_selected = false;
            foreach ($latest_rates as $type => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    $results['diagnostics']['selected_rate_type'] = $type;
                    if ($type === 'custom') {
                        $is_custom_selected = true;
                    }
                }
            }
            
            if ($is_custom_selected) {
                $results['logs'][] = 'Actualmente está seleccionada una tasa personalizada, por lo que la actualización automática está deshabilitada';
            } else {
                $results['logs'][] = 'Tipo de tasa seleccionada: ' . $results['diagnostics']['selected_rate_type'];
            }
        } else {
            $results['diagnostics']['has_latest_rates'] = false;
            $results['logs'][] = 'No se encontraron tasas guardadas en la base de datos';
        }
        
        // Try to get API rates
        $api_rates = ConverterModel::get_rates_from_api();
        if ($api_rates) {
            $results['diagnostics']['api_rates_available'] = true;
            $results['logs'][] = 'Tasas obtenidas correctamente desde la API';
            
            // Compare with latest rates to see if there are changes
            if ($latest_rates) {
                $has_changes = false;
                $rate_types = ['bcv', 'parallel', 'average'];
                
                foreach ($rate_types as $type) {
                    if (isset($api_rates[$type]['value']) && isset($latest_rates[$type]['value'])) {
                        $api_value = (float)$api_rates[$type]['value'];
                        $db_value = (float)$latest_rates[$type]['value'];
                        
                        $results['diagnostics']['rate_comparison'][$type] = [
                            'api_value' => $api_value,
                            'db_value' => $db_value,
                            'difference' => abs($api_value - $db_value)
                        ];
                        
                        if (abs($api_value - $db_value) > 0.001) {
                            $has_changes = true;
                        }
                    }
                }
                
                $results['diagnostics']['has_rate_changes'] = $has_changes;
                if ($has_changes) {
                    $results['logs'][] = 'Se detectaron cambios en las tasas comparadas con las guardadas localmente';
                } else {
                    $results['logs'][] = 'No hay cambios en las tasas comparadas con las guardadas localmente';
                }
            }
        } else {
            $results['diagnostics']['api_rates_available'] = false;
            $results['logs'][] = 'Error: No se pudieron obtener tasas desde la API';
        }
        
        // Now force the update regardless of scheduling
        $results['logs'][] = 'Ejecutando proceso de actualización...';
        $update_result = ConverterModel::check_and_update_rates();
        
        if ($update_result === false) {
            $results['logs'][] = 'No se realizó ninguna actualización. Posibles razones: tasa personalizada seleccionada, no hay cambios en las tasas, o error al guardar';
        } else {
            $results['logs'][] = 'Actualización completada exitosamente. ID del nuevo registro: ' . $update_result;
        }
        
        $results['result'] = $update_result;
        
        return $results;
    }
    
    /**
     * Force a reschedule of the cron job
     * 
     * @return array Result information
     */
    public static function force_cron_reschedule() {
        // Verificar permisos de administrador
        if (!self::verify_admin_permissions()) {
            return [
                'action' => 'force_reschedule',
                'success' => false,
                'message' => 'Acceso denegado. Se requieren permisos de administrador.',
                'timestamp' => current_time('mysql')
            ];
        }
        
        $results = [
            'timestamp' => current_time('mysql'),
            'action' => 'force_reschedule',
            'success' => false,
            'message' => ''
        ];
        
        // Clear existing schedule
        $timestamp = wp_next_scheduled('ves_converter_update_rates_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ves_converter_update_rates_event');
            $results['message'] = 'Programación anterior eliminada';
        } else {
            $results['message'] = 'No se encontró ninguna programación anterior';
        }
        
        // Reschedule
        $schedule_result = wp_schedule_event(time(), 'ves_high_frequency', 'ves_converter_update_rates_event');
        
        if ($schedule_result === false) {
            $results['message'] .= '. Error al programar nuevo evento';
        } else {
            $results['success'] = true;
            $next_run = wp_next_scheduled('ves_converter_update_rates_event');
            $results['message'] .= '. Nuevo evento programado para: ' . date('Y-m-d H:i:s', $next_run);
        }
        
        return $results;
    }
} 