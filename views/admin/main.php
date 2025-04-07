<div class="wrap">
    <h1 class="text-2xl font-bold mb-6"><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-xl font-semibold"><?php _e('Bolivar to Dollar Converter', 'ves-converter'); ?></h2>
            <p class="mt-2"><?php _e('Configure and manage the Bolivar to Dollar converter.', 'ves-converter'); ?></p>
        </div>
        
        <div class="flex justify-between gap-4">
            <!-- Configuration -->
            <div class="bg-white rounded shadow flex-1">
                <div class="bg-gray-50 p-4 border-b">
                    <h3 class="font-medium"><?php _e('Set Your Rate Today', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <p class="mb-4"><?php _e('Set the default exchange rate to use across your site.', 'ves-converter'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('ves_converter_settings', 'ves_converter_nonce'); ?>
                        <div class="mb-6">
                            <label for="default_rate_type" class="block mb-2"><?php _e('Default rate', 'ves-converter'); ?></label>
                            <select name="default_rate_type" id="default_rate_type" class="w-full p-2 border rounded">
                                <option value="bcv"><?php _e('BCV', 'ves-converter'); ?></option>
                                <option value="average"><?php _e('Average', 'ves-converter'); ?></option>
                                <option value="parallel"><?php _e('Parallel', 'ves-converter'); ?></option>
                            </select>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" name="submit" id="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                <?php _e('Save Changes', 'ves-converter'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Rates Information -->
            <div class="bg-white rounded shadow flex-1">
                <div class="bg-gray-50 p-4 border-b">
                    <h3 class="font-medium"><?php _e('Current Exchange Rates', 'ves-converter'); ?></h3>
                </div>
                <div class="p-6">
                    <p class="mb-4"><?php _e('The latest exchange rates from VES Change Getter:', 'ves-converter'); ?></p>
                    
                    <?php
                    // Get latest rates from VES Change Getter
                    $api_url = rest_url('ves-change-getter/v1/latest');
                    $response = wp_remote_get($api_url);
                    $rates = [];
                    
                    if (!is_wp_error($response)) {
                        $body = wp_remote_retrieve_body($response);
                        $data = json_decode($body, true);
                        
                        if ($data && isset($data['rates'])) {
                            $rates = $data['rates'];
                        }
                    }
                    ?>
                    
                    <div class="flex flex-wrap gap-4">
                        <div class="bg-blue-50 p-4 rounded flex-1">
                            <h4 class="font-medium text-blue-800 mb-1"><?php _e('BCV Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-blue-700">
                                <?php echo isset($rates['bcv']) ? number_format($rates['bcv'], 2) : 'N/A'; ?> Bs.
                            </p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded flex-1">
                            <h4 class="font-medium text-green-800 mb-1"><?php _e('Average Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-green-700">
                                <?php echo isset($rates['average']) ? number_format($rates['average'], 2) : 'N/A'; ?> Bs.
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded flex-1">
                            <h4 class="font-medium text-purple-800 mb-1"><?php _e('Parallel Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-purple-700">
                                <?php echo isset($rates['parallel']) ? number_format($rates['parallel'], 2) : 'N/A'; ?> Bs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historical Rates Table -->
        <div class="bg-white rounded shadow mt-4">
            <div class="bg-gray-50 p-4 border-b">
                <h3 class="font-medium"><?php _e('Historical Rate Records', 'ves-converter'); ?></h3>
            </div>
            <div class="p-6">
                <p class="mb-4"><?php _e('Recent rate records saved by users:', 'ves-converter'); ?></p>
                
                <?php if (!empty($rate_history)) : ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left"><?php _e('User', 'ves-converter'); ?></th>
                                <th class="p-3 text-left"><?php _e('Rate Type', 'ves-converter'); ?></th>
                                <th class="p-3 text-left"><?php _e('Rate Value', 'ves-converter'); ?></th>
                                <th class="p-3 text-left"><?php _e('Date', 'ves-converter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rate_history as $index => $record) : ?>
                            <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> border-t">
                                <td class="p-3">
                                    <?php 
                                    $user_info = get_userdata($record['user_id']);
                                    echo $user_info ? esc_html($user_info->user_login) : 'User ' . esc_html($record['user_id']); 
                                    ?>
                                </td>
                                <td class="p-3 font-medium">
                                    <?php echo esc_html(ucfirst($record['rate_type'])); ?>
                                </td>
                                <td class="p-3">
                                    <?php echo esc_html(number_format($record['rate_value'], 2)) . ' Bs.'; ?>
                                </td>
                                <td class="p-3">
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record['date_created']))); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 font-medium"><?php _e('No records found', 'ves-converter'); ?></h3>
                    <p class="mt-1"><?php _e('No rate records have been saved yet.', 'ves-converter'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 