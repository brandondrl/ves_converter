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

// Formatear el valor de la tasa para mostrarla
$rate_name = isset($rates_data['selected']) ? esc_html($rates_data['selected']) : 'bcv';
$rate_value = isset($rates_data['rates'][$rates_data['selected']]) ? number_format($rates_data['rates'][$rates_data['selected']], 2) : '0.00';

// Nombres más amigables para las tasas
$rate_display_names = [
    'bcv' => __('BCV', 'ves-converter'),
    'average' => __('Average', 'ves-converter'),
    'parallel' => __('Parallel', 'ves-converter'),
    'custom' => __('Custom', 'ves-converter')
];
$rate_display_name = isset($rate_display_names[$rate_name]) ? $rate_display_names[$rate_name] : $rate_name;
?>
<div class="ves-currency-switch-container <?php echo esc_attr($atts['position']); ?>">
    <div class="ves-rate-tag"><?php echo $rate_display_name; ?>: <?php echo $rate_value; ?></div>
    <button id="ves-currency-switch" class="ves-currency-switch" title="<?php _e('Switch between USD and Bolivares', 'ves-converter'); ?>" aria-label="<?php _e('Change currency', 'ves-converter'); ?>">
        <div class="ves-currency-switch-inner">
            <span class="currency-icon usd-icon">$</span>
            <span class="currency-icon bs-icon">Bs.</span>
        </div>
    </button>
</div> 