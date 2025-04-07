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

    /**
     * Create plugin tables
     */
    public static function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ves_converter';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            rate_type varchar(50) NOT NULL,
            rate_value decimal(20,10) NOT NULL,
            date_created datetime NOT NULL,
            PRIMARY KEY (id)
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
    public function save_conversion($data) {
        global $wpdb;
        
        $default_data = [
            'user_id' => get_current_user_id(),
            'rate_type' => '',
            'rate_value' => 0,
            'date_created' => current_time('mysql')
        ];
        
        $data = wp_parse_args($data, $default_data);
        
        return $wpdb->insert($this->table_name, $data);
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
     * Get all rates saved in the database
     * 
     * @param int $limit Maximum number of records to return
     * @return array Array of rate records
     */
    public static function get_all_rates($limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ves_converter';
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} 
            ORDER BY date_created DESC 
            LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
} 