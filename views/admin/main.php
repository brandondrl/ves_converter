<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ves-converter-admin-content">
        <div class="ves-converter-admin-header">
            <h2><?php _e('Bolivar to Dollar Converter', 'ves-converter'); ?></h2>
            <p><?php _e('Configure and manage the Bolivar to Dollar converter.', 'ves-converter'); ?></p>
        </div>
        
        <div class="ves-converter-admin-cards">
            <!-- Usage Statistics -->
            <div class="ves-converter-admin-card">
                <h3><?php _e('Usage Statistics', 'ves-converter'); ?></h3>
                <div class="ves-converter-admin-card-content">
                    <p><?php _e('Statistics about converter usage will be shown here soon.', 'ves-converter'); ?></p>
                </div>
            </div>
            
            <!-- Configuration -->
            <div class="ves-converter-admin-card">
                <h3><?php _e('Configuration', 'ves-converter'); ?></h3>
                <div class="ves-converter-admin-card-content">
                    <p><?php _e('Configuration options for the converter.', 'ves-converter'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('ves_converter_settings', 'ves_converter_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Default rate', 'ves-converter'); ?></th>
                                <td>
                                    <select name="default_rate_type">
                                        <option value="bcv"><?php _e('BCV', 'ves-converter'); ?></option>
                                        <option value="average"><?php _e('Average', 'ves-converter'); ?></option>
                                        <option value="parallel"><?php _e('Parallel', 'ves-converter'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'ves-converter'); ?>">
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- API Information -->
        <div class="ves-converter-admin-section">
            <h3><?php _e('API Information', 'ves-converter'); ?></h3>
            <p><?php _e('This plugin offers API endpoints for integration with other applications:', 'ves-converter'); ?></p>
            
            <div class="ves-converter-api-endpoints">
                <div class="ves-converter-api-endpoint">
                    <h4><?php _e('Save Conversion', 'ves-converter'); ?></h4>
                    <code>POST /wp-json/ves-converter/v1/save-conversion</code>
                    <button class="button copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('ves-converter/v1/save-conversion')); ?>">
                        <?php _e('Copy', 'ves-converter'); ?>
                    </button>
                </div>
                
                <div class="ves-converter-api-endpoint">
                    <h4><?php _e('Conversion History', 'ves-converter'); ?></h4>
                    <code>GET /wp-json/ves-converter/v1/user-conversions</code>
                    <button class="button copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('ves-converter/v1/user-conversions')); ?>">
                        <?php _e('Copy', 'ves-converter'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 