<?php
namespace VesConverter\Admin;

use VesConverter\Models\ConverterModel;

/**
 * The admin-specific functionality of the plugin
 */
class AdminPage {
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        // Add main menu page
        add_menu_page(
            'VES Converter', 
            'VES Converter', 
            'manage_options', 
            'ves-converter', 
            [$this, 'display_admin_page'], 
            'dashicons-money-alt', 
            25
        );

        // Add Statistics submenu
        add_submenu_page(
            'ves-converter',
            'Statistics & API',
            'Statistics & API',
            'manage_options',
            'ves-converter-stats',
            [$this, 'display_stats_page']
        );
    }

    /**
     * Display the main admin page
     */
    public function display_admin_page() {
        // Get saved rate history
        $rate_history = ConverterModel::get_all_rates();
        
        // Include admin view
        include VES_CONVERTER_PLUGIN_DIR . 'views/admin/main.php';
    }

    /**
     * Display the statistics & API page
     */
    public function display_stats_page() {
        // Include stats view
        include VES_CONVERTER_PLUGIN_DIR . 'views/admin/stats.php';
    }

    /**
     * Register the JavaScript and CSS for the admin area
     */
    public function enqueue_scripts() {
        // Only load on plugin pages
        $screen = get_current_screen();
        if (strpos($screen->id, 'ves-converter') === false) {
            return;
        }

        // Generate version with timestamp to avoid caching
        $version = VES_CONVERTER_VERSION . '.' . time();

        // Ensure dashicons are loaded
        wp_enqueue_style('dashicons');

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

        // Enqueue admin CSS
        wp_enqueue_style(
            'ves-converter-admin',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/admin.css',
            ['ves-converter-tailwind', 'dashicons', 'sweetalert2'],
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

        // Enqueue admin JS
        wp_enqueue_script(
            'ves-converter-admin',
            VES_CONVERTER_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'sweetalert2'],
            VES_CONVERTER_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ves-converter-admin', 'vesConverterAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ves_converter_admin_nonce')
        ]);
    }
} 