<div class="wrap">
    <h1 class="text-2xl font-bold mb-6"><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800"><?php _e('Bolivar to Dollar Converter', 'ves-converter'); ?></h2>
            <p class="text-gray-600 mt-2"><?php _e('Configure and manage the Bolivar to Dollar converter.', 'ves-converter'); ?></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800"><?php _e('Usage Statistics', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600"><?php _e('Statistics about converter usage will be shown here soon.', 'ves-converter'); ?></p>
                </div>
            </div>
            
            <!-- Configuration -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800"><?php _e('Configuration', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4"><?php _e('Configuration options for the converter.', 'ves-converter'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('ves_converter_settings', 'ves_converter_nonce'); ?>
                        <div class="mb-6">
                            <label for="default_rate_type" class="block text-sm font-medium text-gray-700 mb-2"><?php _e('Default rate', 'ves-converter'); ?></label>
                            <select name="default_rate_type" id="default_rate_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="bcv"><?php _e('BCV', 'ves-converter'); ?></option>
                                <option value="average"><?php _e('Average', 'ves-converter'); ?></option>
                                <option value="parallel"><?php _e('Parallel', 'ves-converter'); ?></option>
                            </select>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" name="submit" id="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <?php _e('Save Changes', 'ves-converter'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- API Information -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-800"><?php _e('API Information', 'ves-converter'); ?></h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4"><?php _e('This plugin offers API endpoints for integration with other applications:', 'ves-converter'); ?></p>
                
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-md font-medium text-gray-800 mb-2"><?php _e('Save Conversion', 'ves-converter'); ?></h4>
                        <div class="bg-gray-100 p-3 rounded mb-3 font-mono text-sm">POST /wp-json/ves-converter/v1/save-conversion</div>
                        <button class="copy-endpoint inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-endpoint="<?php echo esc_url(rest_url('ves-converter/v1/save-conversion')); ?>">
                            <?php _e('Copy', 'ves-converter'); ?>
                        </button>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-md font-medium text-gray-800 mb-2"><?php _e('Conversion History', 'ves-converter'); ?></h4>
                        <div class="bg-gray-100 p-3 rounded mb-3 font-mono text-sm">GET /wp-json/ves-converter/v1/user-conversions</div>
                        <button class="copy-endpoint inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-endpoint="<?php echo esc_url(rest_url('ves-converter/v1/user-conversions')); ?>">
                            <?php _e('Copy', 'ves-converter'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 