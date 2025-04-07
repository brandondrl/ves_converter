<?php
namespace VesConverter\Admin;

/**
 * The admin-specific functionality of the plugin
 */
class AdminPage {
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_menu_page(
            'VES Converter', 
            'VES Converter', 
            'manage_options', 
            'ves-converter', 
            [$this, 'display_admin_page'], 
            'dashicons-money-alt', 
            25
        );
    }

    /**
     * Display the admin page
     */
    public function display_admin_page() {
        // Include admin view
        include VES_CONVERTER_PLUGIN_DIR . 'views/admin/main.php';
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

        // Enqueue admin CSS
        wp_enqueue_style(
            'ves-converter-admin',
            VES_CONVERTER_PLUGIN_URL . 'assets/css/admin.css',
            [],
            VES_CONVERTER_VERSION
        );

        // Enqueue admin JS
        wp_enqueue_script(
            'ves-converter-admin',
            VES_CONVERTER_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
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