<?php
namespace VesConverter\Models;
use VesConverter\Models\ConverterModel;

// Helper methods for the Ves Converter plugin

class Helper {
    
    public static function ves_converter_get_rates_data($current_page = 1, $records_per_page = 10) {

        $offset = ($current_page - 1) * $records_per_page;

        // Obtener los registros paginados y el total de registros
        $rate_history = ConverterModel::get_paginated_rates($records_per_page, $offset);
        $total_records = ConverterModel::get_total_rate_count();
        $total_pages = ceil($total_records / $records_per_page);

        return [
            'rate_history' => $rate_history,
            'total_pages' => $total_pages,
            'current_page' => $current_page,
        ];
    }
}