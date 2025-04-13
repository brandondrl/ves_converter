<div class="wrap">
    <h1 class="text-2xl font-bold mb-6"><?php echo esc_html('Statistics & API'); ?></h1>
    
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800"><?php _e('VES Converter Management', 'ves-converter'); ?></h2>
            <p class="text-gray-600 mt-2"><?php _e('Monitor usage and integrate with external systems', 'ves-converter'); ?></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800"><?php _e('Usage Statistics', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600"><?php _e('Statistics about converter usage will be shown here soon.', 'ves-converter'); ?></p>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-blue-800 mb-1"><?php _e('Total Conversions', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-blue-700">
                                <?php 
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'ves_converter';
                                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                                    echo $count ? $count : '0';
                                ?>
                            </p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-green-800 mb-1"><?php _e('Unique Users', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-green-700">
                                <?php 
                                    $unique_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_name");
                                    echo $unique_users ? $unique_users : '0';
                                ?>
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-purple-800 mb-1"><?php _e('Most Used Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-purple-700">
                                <?php 
                                    $most_used = $wpdb->get_var("SELECT rate_type FROM $table_name GROUP BY rate_type ORDER BY COUNT(*) DESC LIMIT 1");
                                    echo $most_used ? ucfirst($most_used) : 'N/A';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800"><?php _e('Recent Activity', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <?php 
                        $recent = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_created DESC LIMIT 5", ARRAY_A);
                        if ($recent && count($recent) > 0) :
                    ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php _e('User', 'ves-converter'); ?></th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php _e('Rate Type', 'ves-converter'); ?></th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php _e('Date', 'ves-converter'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent as $item) : ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                            $user_info = get_userdata($item['user_id']);
                                            echo $user_info ? $user_info->user_login : 'User ' . $item['user_id']; 
                                        ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo ucfirst($item['rate_type']); ?> (<?php echo number_format($item['rate_value'], 2); ?>)
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['date_created'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else : ?>
                    <p class="text-gray-600"><?php _e('No recent activity found.', 'ves-converter'); ?></p>
                    <?php endif; ?>
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