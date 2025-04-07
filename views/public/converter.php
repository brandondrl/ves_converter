<div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6"><?php _e('Bolivar to Dollar Converter', 'ves-converter'); ?></h2>
    
    <div class="bg-gray-50 rounded-md p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-4">
            <label for="rate-type" class="font-medium text-gray-700 w-full sm:w-auto"><?php _e('Select rate to use:', 'ves-converter'); ?></label>
            <select id="rate-type" name="rate-type" class="form-select mt-1 block w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="bcv"><?php _e('BCV', 'ves-converter'); ?> (<?php echo isset($rates['bcv']) ? number_format($rates['bcv'], 2) : '0.00'; ?> Bs.)</option>
                <option value="average"><?php _e('Average', 'ves-converter'); ?> (<?php echo isset($rates['average']) ? number_format($rates['average'], 2) : '0.00'; ?> Bs.)</option>
                <option value="parallel"><?php _e('Parallel', 'ves-converter'); ?> (<?php echo isset($rates['parallel']) ? number_format($rates['parallel'], 2) : '0.00'; ?> Bs.)</option>
            </select>
        </div>
    </div>
    
    <div class="bg-gray-50 rounded-md p-6 mb-6 shadow-sm">
        <div class="mb-4">
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <label for="usd-amount" class="font-medium text-gray-700 w-full sm:w-40"><?php _e('Amount in Dollars (USD):', 'ves-converter'); ?></label>
                <input type="number" id="usd-amount" name="usd-amount" step="0.01" min="0" class="mt-1 block w-full sm:w-40 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" id="convert-to-ves" class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?php _e('Convert to VES', 'ves-converter'); ?>
                </button>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex flex-wrap items-center gap-4">
                <label for="ves-amount" class="font-medium text-gray-700 w-full sm:w-40"><?php _e('Amount in Bolivars (VES):', 'ves-converter'); ?></label>
                <input type="number" id="ves-amount" name="ves-amount" step="0.01" min="0" class="mt-1 block w-full sm:w-40 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" id="convert-to-usd" class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?php _e('Convert to USD', 'ves-converter'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <div class="mt-6">
        <div id="result-container" class="bg-green-50 rounded-md p-4 mb-6 shadow-sm hidden">
            <h3 class="text-xl font-medium text-green-800 mb-2"><?php _e('Conversion Result:', 'ves-converter'); ?></h3>
            <p id="result-text" class="text-green-700"></p>
            <button type="button" id="save-conversion" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <?php _e('Save Conversion', 'ves-converter'); ?>
            </button>
        </div>
    </div>
    
    <?php if (is_user_logged_in()): ?>
    <div class="bg-gray-50 rounded-md p-6 shadow-sm mt-8">
        <h3 class="text-xl font-medium text-gray-800 pb-3 border-b border-gray-200 mb-4"><?php _e('Conversion History', 'ves-converter'); ?></h3>
        <div id="history-container" class="overflow-x-auto">
            <p class="text-gray-600"><?php _e('Loading history...', 'ves-converter'); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div> 