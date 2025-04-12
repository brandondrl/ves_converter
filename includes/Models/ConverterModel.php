<?php
namespace VesConverter\Models;

/**
 * The model for managing converter data
 */
class ConverterModel {
    /**
     * Table name
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
     * Create plugin tables
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
            return wp_send_json_error(array('message' => __('Invalid response from API.', 'ves-converter')));;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data'])) {
            return wp_send_json_error(array('message' => __('Failed to connect to rates API. Please try again later.', 'ves-converter')));
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
        
       return wp_send_json_error(array('message' => __('Failed to connect to rates API. Please try again later.', 'ves-converter')));
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
        
        // Preparar datos de tasas con la selección aplicada
        $processed_rates = array(
            'bcv' => array(
                'value' => isset($rates['bcv']['value']) ? $rates['bcv']['value'] : 0,
                'catch_date' => isset($rates['bcv']['catch_date']) ? $rates['bcv']['catch_date'] : $current_time,
                'selected' => ($selected_type === 'bcv')
            ),
            'parallel' => array(
                'value' => isset($rates['parallel']['value']) ? $rates['parallel']['value'] : 0,
                'catch_date' => isset($rates['parallel']['catch_date']) ? $rates['parallel']['catch_date'] : $current_time,
                'selected' => ($selected_type === 'parallel')
            ),
            'average' => array(
                'value' => isset($rates['average']['value']) ? $rates['average']['value'] : 0,
                'catch_date' => isset($rates['average']['catch_date']) ? $rates['average']['catch_date'] : $current_time,
                'selected' => ($selected_type === 'average')
            ),
            'custom' => array(
                'value' => ($selected_type === 'custom') ? $custom_rate : 0,
                'catch_date' => date('Y-m-d h:i:s A', strtotime('-4 hours', strtotime(gmdate('Y-m-d H:i:s')))),
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
            "SELECT rates FROM $table_name ORDER BY created_at DESC LIMIT 1"
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
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10", ARRAY_A
        );
    }

} 