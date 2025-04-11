<?php
/**
 * Currency switch view
 * 
 * This is the template for the currency switch button in the frontend
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="ves-currency-switch-container <?php echo esc_attr($atts['position']); ?>">
    <button id="ves-currency-switch" class="ves-currency-switch" title="<?php _e('Switch between USD and Bolivares', 'ves-converter'); ?>" aria-label="<?php _e('Change currency', 'ves-converter'); ?>">
        $
    </button>
</div> 