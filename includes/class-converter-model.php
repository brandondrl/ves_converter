<?php

class ConverterModel {

    
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
        
} 