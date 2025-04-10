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
     * Save conversion data
     * 
     * @param array $data Conversion data
     * @return int|false The number of rows inserted, or false on error
     */
    // public function save_conversion($data) {
    //     global $wpdb;
        
    //     $default_data = [
    //         'user_id' => get_current_user_id(),
    //         'rate_type' => '',
    //         'rate_value' => 0,
    //         'date_created' => current_time('mysql')
    //     ];
        
    //     $data = wp_parse_args($data, $default_data);
        
    //     return $wpdb->insert($this->table_name, $data);
    // }

    public static function save_rate() {
        // Verificar el nonce para seguridad
        check_ajax_referer('ves_converter_rate_save', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ves-converter')));
        }
        
        $rate_type = isset($_POST['rate_type']) ? sanitize_text_field($_POST['rate_type']) : '';
        $custom_rate = isset($_POST['custom_rate']) ? floatval($_POST['custom_rate']) : 0;
        
        if (empty($rate_type)) {
            wp_send_json_error(array('message' => __('Rate type is required.', 'ves-converter')));
        }
        
        // Get current rates from API
        $api_url = 'https://catalogo.grupoidsi.com/wp-json/ves-change-getter/v1/latest';
        $response = wp_remote_get($api_url);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Failed to connect to rates API. Please try again later.', 'ves-converter')));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['success']) || !$data['success'] || !isset($data['data']['rates'])) {
            wp_send_json_error(array('message' => __('Invalid response from API.', 'ves-converter')));
        }
        
        $api_rates = $data['data']['rates'];
        $current_time = current_time('mysql');
        
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
                'value' => ($rate_type === 'custom') ? $custom_rate : 0,
                'catch_date' => date('Y-m-d h:i:s A', strtotime('-4 hours', strtotime(gmdate('Y-m-d H:i:s')))),
                'selected' => ($rate_type === 'custom')
            )
        );
        
        if ($rate_type === 'custom' && $custom_rate <= 0) {
            wp_send_json_error(array('message' => __('Custom rate must be greater than 0.', 'ves-converter')));
        }
        
        // Verificar si la tabla existe, y crearla si no
        global $wpdb;
        $table_name = $wpdb->prefix . 'ves_converter_rates';
        
        // Verificar si la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        
        // Si la tabla no existe, intentar crearla
        if (!$table_exists) {
            // Verificar si el modelo está disponible y usarlo para crear la tabla
            if (class_exists('VesConverter\\Models\\ConverterModel')) {
                \VesConverter\Models\ConverterModel::create_table();
                // Verificar nuevamente si la tabla se creó correctamente
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
                
                if (!$table_exists) {
                    // Ocultar el error real para no exponer información sensible
                    wp_send_json_error(array('message' => __('Failed to save rates due to database configuration. Please contact the administrator.', 'ves-converter')));
                    return;
                }
            } else {
                // Si no podemos acceder al modelo, mostramos un error genérico
                wp_send_json_error(array('message' => __('Failed to save rates. Database configuration issue.', 'ves-converter')));
                return;
            }
        }
        
        // Silenciar errores directos de la base de datos
        $wpdb->suppress_errors(true);
        $result = $wpdb->insert(
            $table_name,
            array(
                'rates' => json_encode($rates),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ),
            array('%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Registrar el error internamente para debugging pero no exponerlo al usuario
            error_log("VES Converter Database Error: " . $wpdb->last_error);
            wp_send_json_error(array('message' => __('Failed to save rates. Please try again later.', 'ves-converter')));
            return;
        }
        
        wp_send_json_success(array('message' => __('Rates saved successfully.', 'ves-converter')));
    }
    
    /**
     * Get conversions by user
     * 
     * @param int $user_id User ID
     * @param int $limit Maximum number of records to return
     * @return array Array of conversion records
     */
    public function get_user_conversions($user_id, $limit = 10) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE user_id = %d 
            ORDER BY date_created DESC 
            LIMIT %d",
            $user_id,
            $limit
        );
        
        return $wpdb->get_results($query);
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