<?php

class ConverterModel {
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
    
    public static function save_rate($user_id, $type, $value) {
        global $wpdb;
        $table_name = self::get_table_name();
        
        // Get current rates or initialize empty array
        $current_rates = self::get_latest_rates();
        if (!$current_rates) {
            $current_rates = array(
                'bcv' => array('value' => 0, 'catch_date' => ''),
                'average' => array('value' => 0, 'catch_date' => ''),
                'parallel' => array('value' => 0, 'catch_date' => '')
            );
        }
        
        // Update the specific rate
        $current_rates[$type] = array(
            'value' => $value,
            'catch_date' => current_time('mysql')
        );
        
        // Save the updated rates
        $wpdb->insert(
            $table_name,
            array(
                'rates' => json_encode($current_rates)
            ),
            array('%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function get_latest_rates() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $result = $wpdb->get_row(
            "SELECT rates FROM $table_name ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($result && isset($result->rates)) {
            return json_decode($result->rates, true);
        }
        
        return null;
    }
    
    public static function get_all_rates() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10"
        );
    }
} 