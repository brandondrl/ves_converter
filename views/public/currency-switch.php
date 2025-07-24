<?php
/**
 * Vista del switch de moneda
 * 
 * Esta es la plantilla para el botón de cambio de moneda en el frontend
 */

// Salir si se accede directamente
if (!defined('ABSPATH')) {
    exit;
}

// Formatear el valor de la tasa para mostrarla
$rate_name = isset($rates_data['selected']) ? esc_html($rates_data['selected']) : 'bcv';
$rate_value = isset($rates_data['rates'][$rates_data['selected']]) ? number_format($rates_data['rates'][$rates_data['selected']], 2) : '0.00';

// Nombres más amigables para las tasas
$rate_display_names = [
    'bcv' => __('BCV', 'ves-converter'),
    'average' => __('Promedio', 'ves-converter'),
    'euro' => __('Paralelo', 'ves-converter'),
    'custom' => __('Personalizada', 'ves-converter')
];
$rate_display_name = isset($rate_display_names[$rate_name]) ? $rate_display_names[$rate_name] : $rate_name;
?>
<div class="ves-currency-switch-container <?php echo esc_attr($atts['position']); ?>">
    <div class="ves-rate-tag" translate="no"><?php echo $rate_display_name; ?>: <?php echo $rate_value; ?></div>
    <button id="ves-currency-switch" class="ves-currency-switch" title="<?php _e('Cambiar entre USD y Bolívares', 'ves-converter'); ?>" aria-label="<?php _e('Cambiar moneda', 'ves-converter'); ?>" translate="no">
        <div class="ves-currency-switch-inner">
            <span class="currency-icon usd-icon" translate="no" data-nocontent="$">&#36;</span>
            <span class="currency-icon bs-icon" translate="no" data-nocontent="Bs.">B&#115;.</span>
        </div>
    </button>
</div> 