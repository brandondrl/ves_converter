<div class="ves-converter-container">
    <h2><?php _e('Bolivar to Dollar Converter', 'ves-converter'); ?></h2>
    
    <div class="ves-converter-rates">
        <div class="ves-converter-rate-selector">
            <label for="rate-type"><?php _e('Select rate to use:', 'ves-converter'); ?></label>
            <select id="rate-type" name="rate-type">
                <option value="bcv"><?php _e('BCV', 'ves-converter'); ?> (<?php echo isset($rates['bcv']) ? number_format($rates['bcv'], 2) : '0.00'; ?> Bs.)</option>
                <option value="average"><?php _e('Average', 'ves-converter'); ?> (<?php echo isset($rates['average']) ? number_format($rates['average'], 2) : '0.00'; ?> Bs.)</option>
                <option value="parallel"><?php _e('Parallel', 'ves-converter'); ?> (<?php echo isset($rates['parallel']) ? number_format($rates['parallel'], 2) : '0.00'; ?> Bs.)</option>
            </select>
        </div>
    </div>
    
    <div class="ves-converter-form">
        <div class="ves-converter-input-group">
            <label for="usd-amount"><?php _e('Amount in Dollars (USD):', 'ves-converter'); ?></label>
            <input type="number" id="usd-amount" name="usd-amount" step="0.01" min="0">
            <button type="button" id="convert-to-ves" class="ves-converter-button">
                <?php _e('Convert to VES', 'ves-converter'); ?>
            </button>
        </div>
        
        <div class="ves-converter-input-group">
            <label for="ves-amount"><?php _e('Amount in Bolivars (VES):', 'ves-converter'); ?></label>
            <input type="number" id="ves-amount" name="ves-amount" step="0.01" min="0">
            <button type="button" id="convert-to-usd" class="ves-converter-button">
                <?php _e('Convert to USD', 'ves-converter'); ?>
            </button>
        </div>
    </div>
    
    <div class="ves-converter-result">
        <div id="result-container" class="ves-converter-result-container" style="display: none;">
            <h3><?php _e('Conversion Result:', 'ves-converter'); ?></h3>
            <p id="result-text"></p>
            <button type="button" id="save-conversion" class="ves-converter-button">
                <?php _e('Save Conversion', 'ves-converter'); ?>
            </button>
        </div>
    </div>
    
    <?php if (is_user_logged_in()): ?>
    <div class="ves-converter-history">
        <h3><?php _e('Conversion History', 'ves-converter'); ?></h3>
        <div id="history-container">
            <p><?php _e('Loading history...', 'ves-converter'); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div> 