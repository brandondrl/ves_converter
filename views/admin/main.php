<div class="wrap bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="ml-2">
                <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php _e('VES Converter', 'ves-converter'); ?></h1>
                <p class="text-sm text-gray-600"><?php _e('Configure and manage your Bolivar to Dollar conversion rates', 'ves-converter'); ?></p>
            </div>
            <div class="h-6 w-10 mr-2">
                <svg width="100%" height="100%" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                    <!-- Franja amarilla -->
                    <rect width="900" height="200" fill="#FFDA00"/>
                    <!-- Franja azul -->
                    <rect width="900" height="200" y="200" fill="#0033AB"/>
                    <!-- Franja roja -->
                    <rect width="900" height="200" y="400" fill="#D31034"/>
                    <!-- Estrellas en semiarco centrado en la franja azul -->
                    <g fill="#FFFFFF">
                        <!-- Estrella central superior (punto más alto del arco) -->
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(0,-20)" />
                        
                        <!-- Estrellas a la izquierda, bajando en arco -->
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(-75,0)" />
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(-150,20)" />
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(-225,40)" />
                        
                        <!-- Estrellas a la derecha, bajando en arco -->
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(75,0)" />
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(150,20)" />
                        <polygon points="450,270 457,290 479,290 461,302 468,324 450,311 432,324 439,302 421,290 443,290" transform="translate(225,40)" />
                    </g>
                </svg>
            </div>
        </div>
        
        <div class="flex justify-between gap-4">
            <!-- Configuration -->
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden flex-1">
                <div class="bg-blue-50 p-4 border-b border-blue-100">
                    <h3 class="text-lg font-medium text-blue-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <?php _e('Set Your Rate Today', 'ves-converter'); ?>
                    </h3>
                </div>
                <div class="p-6">
                    <p class="mb-5 text-gray-600"><?php _e('Select which exchange rate you want to use across your site:', 'ves-converter'); ?></p>
                    <form method="post" action="">
                        <?php wp_nonce_field('ves_converter_settings', 'ves_converter_nonce'); ?>
                        <div class="mb-6">
                            <label for="default_rate_type" class="block mb-2 font-medium text-gray-700"><?php _e('Default rate', 'ves-converter'); ?></label>
                            <select name="default_rate_type" id="default_rate_type" class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="bcv"><?php _e('BCV (Central Bank)', 'ves-converter'); ?></option>
                                <option value="average"><?php _e('Average Rate', 'ves-converter'); ?></option>
                                <option value="parallel"><?php _e('Parallel Market', 'ves-converter'); ?></option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500"><?php _e('This will be applied to all conversions on your website', 'ves-converter'); ?></p>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" name="submit" id="submit" class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <?php _e('Save Changes', 'ves-converter'); ?>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2zm-1 14H8v-7h8v7zm-1-11H9a1 1 0 010-2h6a1 1 0 110 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Rates Information -->
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden flex-1">
                <div class="bg-green-50 p-4 border-b border-green-100">
                    <h3 class="text-lg font-medium text-green-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <?php _e('Current Exchange Rates', 'ves-converter'); ?>
                    </h3>
                </div>
                <div class="p-6">
                    <p class="mb-4 text-gray-600"><?php _e('Latest exchange rates from VES Change Getter:', 'ves-converter'); ?></p>
                    
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
                    
                    // Get last update time
                    $last_updated = isset($data['timestamp']) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $data['timestamp']) : __('Unknown', 'ves-converter');
                    ?>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <h4 class="text-sm font-medium text-blue-800 mb-1"><?php _e('BCV Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-blue-700">
                                <?php echo isset($rates['bcv']) ? number_format($rates['bcv'], 2) : 'N/A'; ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <h4 class="text-sm font-medium text-green-800 mb-1"><?php _e('Average Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-green-700">
                                <?php echo isset($rates['average']) ? number_format($rates['average'], 2) : 'N/A'; ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                            <h4 class="text-sm font-medium text-purple-800 mb-1"><?php _e('Parallel Rate', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-purple-700">
                                <?php echo isset($rates['parallel']) ? number_format($rates['parallel'], 2) : 'N/A'; ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php _e('Last updated:', 'ves-converter'); ?> <?php echo $last_updated; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historical Rates Table -->
        <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden mt-4">
            <div class="bg-amber-50 p-4 border-b border-amber-100">
                <h3 class="text-lg font-medium text-amber-700 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <?php _e('Historical Rate Records', 'ves-converter'); ?>
                </h3>
            </div>
            <div class="p-6">
                <p class="mb-4 text-gray-600"><?php _e('Previous exchange rates saved by users:', 'ves-converter'); ?></p>
                
                <?php if (!empty($rate_history)) : ?>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                                <th class="p-3 text-left border-b"><?php _e('User', 'ves-converter'); ?></th>
                                <th class="p-3 text-left border-b"><?php _e('Rate Type', 'ves-converter'); ?></th>
                                <th class="p-3 text-left border-b"><?php _e('Rate Value', 'ves-converter'); ?></th>
                                <th class="p-3 text-left border-b"><?php _e('Date', 'ves-converter'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($rate_history as $index => $record) : ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-sm">
                                    <?php 
                                    $user_info = get_userdata($record['user_id']);
                                    echo $user_info ? esc_html($user_info->user_login) : 'User ' . esc_html($record['user_id']); 
                                    ?>
                                </td>
                                <td class="p-3 text-sm font-medium">
                                    <?php 
                                    $type = esc_html(ucfirst($record['rate_type']));
                                    $color_class = '';
                                    if ($record['rate_type'] == 'bcv') {
                                        $color_class = 'text-blue-600';
                                    } elseif ($record['rate_type'] == 'average') {
                                        $color_class = 'text-green-600';
                                    } elseif ($record['rate_type'] == 'parallel') {
                                        $color_class = 'text-purple-600';
                                    }
                                    echo '<span class="' . $color_class . '">' . $type . '</span>';
                                    ?>
                                </td>
                                <td class="p-3 text-sm font-medium">
                                    <?php echo esc_html(number_format($record['rate_value'], 2)) . ' Bs.'; ?>
                                </td>
                                <td class="p-3 text-sm text-gray-500">
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record['date_created']))); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="text-center p-10 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900"><?php _e('No records found', 'ves-converter'); ?></h3>
                    <p class="mt-1 text-gray-500"><?php _e('No rate records have been saved yet.', 'ves-converter'); ?></p>
                    <div class="mt-6">
                        <p class="text-sm text-gray-500"><?php _e('When you or other users save rates, they will appear here.', 'ves-converter'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-500">
            <p>VES Converter v1.0 | <?php _e('Developed with ❤️ by IDSI', 'ves-converter'); ?></p>
        </div>
    </div>
</div> 