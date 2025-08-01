<?php
namespace VesConverter\Models;

/**
 * El modelo para gestionar los datos del conversor
 */
class ConverterModel {
    /**
     * Nombre de la tabla
     * 
     * @var string
     */
    private $table_name;
    private const API_URL = 'https://catalogo.grupoidsi.com/wp-json/ves-change-getter/v1/latest';
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ves_converter';
    }
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'ves_converter_rates';
    }

    /**
     * Crear tablas del plugin
     */

    public static function create_table() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            rates longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

        /**
     * Asegura que la tabla de tasas existe
     * 
     * @return bool True si la tabla existe o se creó correctamente
     */
    private static function ensure_table_exists() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        // Verificar si la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        
        // Si la tabla no existe, crearla
        if (!$table_exists) {
            self::create_table();
            
            // Verificar nuevamente si la tabla se creó correctamente
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        }
        
        return $table_exists;
    }

        /**
     * Obtiene los datos crudos de la API (función interna)
     * @return array|null Datos de la API o null en caso de error
     */
    private static function fetch_api_data($force_refresh = false) {
        static $cached_response = null;
        
        // Si tenemos respuesta cacheada y no se fuerza actualización
        if ($cached_response !== null && !$force_refresh) {
            return $cached_response;
        }
            
        $response = wp_remote_get(self::API_URL);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            error_log("VES Converter API Error: Code - $error_code, Message - $error_message");


            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log('VES Converter API Debug: Raw body: ' . $body);
        error_log('VES Converter API Debug: Decoded data: ' . print_r($data, true));
        if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data'])) {
            error_log('VES Converter API Error: Invalid data format received');
            return null;
        }
            
        // Cachear respuesta para esta ejecución
        $cached_response = $data;
        return $data;
    }

        /**
     * Obtiene solo las tasas de la API
     * @return array|null Array de tasas o null en caso de error
     */
    public static function get_rates_from_api() {
        $data = self::fetch_api_data();
        
        if ($data && isset($data['data']['rates'])) {
            return $data['data']['rates'];
        }
        
        return null; // Error ya registrado en fetch_api_data
    }

    /**
     * Obtiene solo la fecha de última actualización
     * @return string Fecha formateada o "Unknown"
     */
    public static function get_last_updated_from_api() {
        $data = self::fetch_api_data();
        
        if ($data && isset($data['data']['update_date'])) {
            return date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'), 
                strtotime($data['data']['update_date'])
            );
        }
        
        return __('Unknown', 'ves-converter');
    }

    /**
     * Guarda un registro de tasas en la base de datos
     * 
     * @param array $rates Datos de tasas a guardar
     * @param string $selected_type Tipo de tasa seleccionada (bcv, average, parallel, custom)
     * @param float $custom_rate Valor personalizado si el tipo es 'custom'
     * @return int|false ID del registro insertado o false si hay error
     */
    public static function store_rate_record($rates, $selected_type = 'bcv', $custom_rate = 0) {
        global $wpdb;
        $table_name = self::get_table_name();
        $current_time = current_time('mysql');
        
        // Asegurar que la tabla existe
        if (!self::ensure_table_exists()) {
            return false;
        }
        
        // Usar directamente la zona horaria de WordPress en lugar de ajustes manuales
        $formatted_date = date_i18n('d/m/Y h:i:s A', current_time('timestamp'));
        
        // Preparar datos de tasas con la selección aplicada
        $processed_rates = array(
            'bcv' => array(
                'value' => isset($rates['bcv']['value']) ? $rates['bcv']['value'] : 0,
                'catch_date' => isset($rates['bcv']['catch_date']) ? $rates['bcv']['catch_date'] : $formatted_date,
                'selected' => ($selected_type === 'bcv')
            ),
            'parallel' => array(
                'value' => isset($rates['parallel']['value']) ? $rates['parallel']['value'] : 0,
                'catch_date' => isset($rates['parallel']['catch_date']) ? $rates['parallel']['catch_date'] : $formatted_date,
                'selected' => ($selected_type === 'parallel')
            ),
            'average' => array(
                'value' => isset($rates['average']['value']) ? $rates['average']['value'] : 0,
                'catch_date' => isset($rates['average']['catch_date']) ? $rates['average']['catch_date'] : $formatted_date,
                'selected' => ($selected_type === 'average')
            ),
            'custom' => array(
                'value' => ($selected_type === 'custom') ? $custom_rate : 0,
                'catch_date' => $formatted_date,
                'selected' => ($selected_type === 'custom')
            )
        );
        
        // Insertar en la base de datos
        $result = $wpdb->insert(
            $table_name,
            array(
                'rates' => json_encode($processed_rates),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ),
            array('%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log("VES Converter Database Error: " . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }

        /**
     * Guarda el primer registro de tasas durante la activación del plugin
     * Este método se puede llamar desde el hook de activación
     * 
     * @return int|false ID del registro insertado o false si hay error
     */
    public static function store_initial_rates() {
        // Obtener tasas de la API
        $api_rates = self::get_rates_from_api();
        
        // Si no podemos obtener las tasas, registrar el error pero no fallar
        if ($api_rates === null) {
            error_log('VES Converter: Failed to get initial rates during plugin activation');
            return false;
        }

        $any_record_exists = self::get_latest_rates();
        if ($any_record_exists !== null) {
            return true;
        }

        // Por defecto usar BCV como tasa seleccionada
        return self::store_rate_record($api_rates, 'bcv');
    }

    public static function save_rate() {
        // Verificaciones AJAX y de seguridad
        check_ajax_referer('ves_converter_rate_save', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ves-converter')));
        }
        
        // Validar datos del formulario
        $rate_type = isset($_POST['rate_type']) ? sanitize_text_field($_POST['rate_type']) : '';
        $custom_rate = isset($_POST['custom_rate']) ? floatval($_POST['custom_rate']) : 0;
        
        if (empty($rate_type)) {
            wp_send_json_error(array('message' => __('Rate type is required.', 'ves-converter')));
        }
        
        if ($rate_type === 'custom' && $custom_rate <= 0) {
            wp_send_json_error(array('message' => __('Custom rate must be greater than 0.', 'ves-converter')));
        }
        
        // Obtener tasas de la API
        $api_rates = self::get_rates_from_api();
        
        if ($api_rates === null) {
            wp_send_json_error(array('message' => __('Failed to connect to rates API. Please try again later.', 'ves-converter')));
        }
        
        // Usar el método centralizado para guardar
        $result = self::store_rate_record($api_rates, $rate_type, $custom_rate);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to save rates. Please try again later.', 'ves-converter')));
        } else {
            wp_send_json_success(array('message' => __('Rates saved successfully.', 'ves-converter')));
        }
    }


    /**
     * Get latest rate saved in the database
     * 
     * @param int $limit Maximum number of records to return
     * @return array Array of rate records
     */
    public static function get_latest_rates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ves_converter_rates';
     
        $result = $wpdb->get_row(
            "SELECT rates FROM $table_name ORDER BY id DESC LIMIT 1"
        );
        
        if ($result && isset($result->rates)) {
            return json_decode($result->rates, true);
        }
        
        return null;
    }


    /**
     * Get all rates saved in the database
     * 
     * @param int $limit Maximum number of records to return
     * @return array Array of rate records
     */

    public static function get_all_rates() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT 10", ARRAY_A
        );
    }

        /**
     * Verifica si hay cambios en las tasas y guarda un nuevo registro si es necesario
     * Este método es llamado por el cron job
     * 
     * @return bool|int False si no hay cambios o error, ID del nuevo registro si se guardó
     */
    public static function check_and_update_rates() {
        // 1. Obtener el último registro guardado
        $latest_rates = self::get_latest_rates();
        if (!$latest_rates) {
            return false; // No hay registros previos
        }
        
        // 2. Determinar cuál es la tasa seleccionada actual
        $selected_type = null;
        $is_custom_selected = false;
        
        foreach ($latest_rates as $type => $data) {
            if (isset($data['selected']) && $data['selected']) {
                $selected_type = $type;
                if ($type === 'custom') {
                    $is_custom_selected = true;
                }
                break;
            }
        }
        
        // Si no se encontró una tasa seleccionada, no proceder
        if ($selected_type === null) {
            error_log('VES Converter: No se encontró una tasa seleccionada en el último registro');
            return false;
        }
        
        if ($is_custom_selected) {
            error_log('VES Converter: No se actualizará automáticamente porque hay una tasa personalizada seleccionada');
            return false;
        }
        
        // 3. Obtener tasas actuales de la API
        $api_rates = self::get_rates_from_api();
        if ($api_rates === null) {
            error_log('VES Converter: Error al obtener tasas desde la API');
            return false;
        }
        
        // 4. Comparar las tasas para ver si hay cambios
        if (isset($api_rates[$selected_type]['value']) && isset($latest_rates[$selected_type]['value'])) {
            $api_value = (float)$api_rates[$selected_type]['value'];
            $db_value = (float)$latest_rates[$selected_type]['value'];
            
            // Calcular el porcentaje de cambio
            $change_percentage = abs(($api_value - $db_value) / $db_value * 100);
            
           // Si el cambio es mayor al 0.009% (0.00009 en decimal), considerar que hay un cambio significativo
            if ($change_percentage > 0.000095) {
                error_log(sprintf(
                    'VES Converter: Cambio significativo detectado en tasa %s - API: %.2f, DB: %.2f, Cambio: %.2f%%',
                    $selected_type,
                    $api_value,
                    $db_value,
                    $change_percentage
                ));
                
                // Guardar el nuevo registro manteniendo la selección actual
                return self::store_rate_record($api_rates, $selected_type);
            } else {
                error_log(sprintf(
                    'VES Converter: No hay cambio significativo en la tasa %s - API: %.2f, DB: %.2f, Cambio: %.2f%%',
                    $selected_type,
                    $api_value,
                    $db_value,
                    $change_percentage
                ));
            }
        }
        
        // No hay cambios en las tasas
        return false;
    }

    /**
     * Verifica si se deben actualizar las tasas según el horario actual
     * 
     * @return bool True si se debe ejecutar la actualización, False si no
     */
    public static function should_run_update_by_schedule() {
        // Verificar día de la semana (no ejecutar en fin de semana)
        $current_timestamp = current_time('timestamp');
        $current_day = intval(date('w', $current_timestamp)); // 0 (domingo) a 6 (sábado)
        
        if ($current_day === 0 || $current_day === 6) {
            error_log('VES Converter: Skipping rate update - weekend day detected: ' . $current_day);
            return false;
        }
        
        // Obtener hora actual (formato 24h) y minuto actual
        $current_hour = intval(date('G', $current_timestamp));
        $current_minute = intval(date('i', $current_timestamp));
        $current_time_minutes = ($current_hour * 60) + $current_minute;
        
        // Verificar si la tasa seleccionada es 'average' y es antes de las 9am
        $latest_rates = self::get_latest_rates();
        if ($latest_rates) {
            foreach ($latest_rates as $type => $data) {
                if (isset($data['selected']) && $data['selected'] && $type === 'average') {
                    if ($current_hour < 9) {
                        error_log('VES Converter: Skipping rate update - average rate selected and before 9am');
                        return false;
                    }
                    break;
                }
            }
        }
        
        // Definir las 6 horas específicas de ejecución (en minutos desde medianoche)
        $execution_times = [
            8 * 60 + 45,  // 8:45 AM
            9 * 60 + 20,  // 9:20 AM
            10 * 60,      // 10:00 AM
            12 * 60 + 45, // 12:45 PM
            13 * 60 + 20, // 1:20 PM
            14 * 60,      // 2:00 PM
        ];
        
        // Verificar si estamos en uno de los momentos específicos de ejecución
        // Permitir un margen de +/- 15 minutos para compensar retrasos y frecuencia del cron
        foreach ($execution_times as $time) {
            if (abs($current_time_minutes - $time) <= 15) {
                error_log('VES Converter: Running update - scheduled time detected: ' . date('H:i', $current_timestamp));
                return true;
            }
        }
        
        // No es una hora programada para ejecución
        error_log('VES Converter: Skipping update - not a scheduled execution time: ' . date('H:i', $current_timestamp));
        return false;
    }

        /**
     * Método principal que combina verificación de horario y actualización de tasas
     * Este método se llama desde el callback de cron
     * 
     * @return bool|int False si no se actualiza, ID del registro si se creó uno nuevo
     */
    public static function process_scheduled_update() {
        error_log('VES Converter Cron: Starting scheduled update process at ' . date('Y-m-d H:i:s', current_time('timestamp')));
        
        // Primero verificar si debemos ejecutar según horario
        if (!self::should_run_update_by_schedule()) {
            error_log('VES Converter Cron: Schedule check determined not to run at this time');
            return false;
        }
        
        error_log('VES Converter Cron: Schedule check passed, proceeding with rate check');
        
        // Si pasó la verificación de horario, entonces verificar y actualizar tasas
        $result = self::check_and_update_rates();
        
        if ($result) {
            // Registro de éxito
            error_log('VES Converter Cron: Rates updated successfully with ID: ' . $result);
        } else {
            error_log('VES Converter Cron: No rate update performed (no changes or custom rate selected)');
        }
        
        return $result;
    }

    /**
     * Elimina todos los registros de la tabla de tasas
     *
     * @return bool True si se eliminaron los registros, False si hubo un error
     */
    public static function delete_all_records() {
        global $wpdb;
        $table_name = self::get_table_name();

        $result = $wpdb->query("TRUNCATE TABLE $table_name");

        if ($result === false) {
            error_log("VES Converter Database Error while deleting all records: " . $wpdb->last_error);
            return false;
        }
        return true;
    }

    /**
     * Obtiene los registros paginados de la base de datos
     *
     * @param int $limit Número de registros por página
     * @param int $offset Offset para la consulta
     * @return array Registros paginados
     */
    public static function get_paginated_rates($limit, $offset) {
        global $wpdb;
        $table_name = self::get_table_name();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Obtiene el número total de registros en la base de datos
     *
     * @return int Número total de registros
     */
    public static function get_total_rate_count() {
        global $wpdb;
        $table_name = self::get_table_name();

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
}