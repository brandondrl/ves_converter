<?php
namespace VesConverter\Core;

/**
 * Main Plugin class
 * 
 * Responsible for initializing the plugin and loading all components
 */
class Plugin {
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_api_endpoints();
        $this->register_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        // Load Admin class
        require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Admin/AdminPage.php';
        
        // Load API
        require_once VES_CONVERTER_PLUGIN_DIR . 'includes/API/APIEndpoint.php';
        
        // Load Shortcode
        require_once VES_CONVERTER_PLUGIN_DIR . 'includes/Core/Shortcode.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $admin = new \VesConverter\Admin\AdminPage();
        
        // Add admin menu
        add_action('admin_menu', [$admin, 'add_admin_menu']);
        
        // Register admin scripts and styles
        add_action('admin_enqueue_scripts', [$admin, 'enqueue_scripts']);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        // Enqueue public scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_scripts']);
    }

    /**
     * Register REST API endpoints
     */
    private function register_api_endpoints() {
        $api = new \VesConverter\API\APIEndpoint();
        add_action('rest_api_init', [$api, 'register_routes']);
    }
    
    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        $shortcode = new Shortcode();
        $shortcode->register();
    }

    /**
     * Register public scripts and styles
     */
    public function enqueue_public_scripts() {
        // Generate version with timestamp to avoid caching
        $version = VES_CONVERTER_VERSION . '.' . time();

        // Enqueue SweetAlert2 CSS
        wp_enqueue_style(
            'sweetalert2',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/sweetalert2.min.css',
            [],
            $version
        );

        // Enqueue Tailwind CSS
        wp_enqueue_style(
            'ves-converter-tailwind',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/tailwind.min.css',
            [],
            $version
        );

        // Enqueue public CSS
        wp_enqueue_style(
            'ves-converter-public',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/public.css',
            ['ves-converter-tailwind', 'sweetalert2'],
            $version
        );

        // Enqueue SweetAlert2
        wp_enqueue_script(
            'sweetalert2',
            VES_CONVERTER_PLUGIN_URL . 'assets/js/sweetalert2.all.min.js',
            [],
            VES_CONVERTER_VERSION,
            true
        );

        // Enqueue public JS
        wp_enqueue_script(
            'ves-converter-public',
            VES_CONVERTER_PLUGIN_URL . 'assets/js/public.js',
            ['jquery', 'sweetalert2'],
            VES_CONVERTER_VERSION,
            true
        );
    }
} 