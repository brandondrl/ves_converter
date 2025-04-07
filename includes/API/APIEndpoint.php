<?php
namespace VesConverter\API;

use VesConverter\Models\ConverterModel;

/**
 * The API endpoints for the plugin
 */
class APIEndpoint {
    /**
     * The namespace for the API
     * 
     * @var string
     */
    private $namespace = 'ves-converter/v1';

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Register endpoint for saving conversions
        register_rest_route($this->namespace, '/save-conversion', [
            'methods' => 'POST',
            'callback' => [$this, 'save_conversion'],
            'permission_callback' => [$this, 'check_permission']
        ]);

        // Register endpoint for getting user conversions
        register_rest_route($this->namespace, '/user-conversions', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_conversions'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }

    /**
     * Check if user has permission to access the endpoint
     * 
     * @return bool
     */
    public function check_permission() {
        return is_user_logged_in();
    }

    /**
     * Save a conversion
     * 
     * @param \WP_REST_Request $request Full data about the request
     * @return \WP_REST_Response
     */
    public function save_conversion($request) {
        $params = $request->get_params();
        
        // Validate required fields
        $required_fields = ['rate_type', 'rate_value', 'amount_usd', 'amount_ves'];
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Missing required field: ', 'ves-converter') . $field
                ], 400);
            }
        }
        
        // Save conversion
        $model = new ConverterModel();
        $result = $model->save_conversion([
            'rate_type' => sanitize_text_field($params['rate_type']),
            'rate_value' => floatval($params['rate_value']),
            'amount_usd' => floatval($params['amount_usd']),
            'amount_ves' => floatval($params['amount_ves'])
        ]);
        
        if ($result) {
            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Conversion saved successfully', 'ves-converter')
            ], 200);
        } else {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Failed to save conversion', 'ves-converter')
            ], 500);
        }
    }
    
    /**
     * Get user conversions
     * 
     * @param \WP_REST_Request $request Full data about the request
     * @return \WP_REST_Response
     */
    public function get_user_conversions($request) {
        $params = $request->get_params();
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        
        $model = new ConverterModel();
        $conversions = $model->get_user_conversions(get_current_user_id(), $limit);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $conversions
        ], 200);
    }
} 