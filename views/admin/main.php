<?php
use VesConverter\Models\ConverterModel;
use VesConverter\Models\Helper;

// Get latest rates from VES Change Getter using model methods
$rates = ConverterModel::get_rates_from_api();
$last_updated = ConverterModel::get_last_updated_from_api();

if (!$rates) {
    $rates = [];
    $last_updated = __('Unknown', 'ves-converter');
}

// Obtener el número de página actual desde la URL
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$records_per_page = 10;

// Obtener los datos de la tabla
$rates_data = Helper::ves_converter_get_rates_data($current_page, $records_per_page);
$rate_history = $rates_data['rate_history'];
$total_pages = $rates_data['total_pages'];
$current_page = $rates_data['current_page'];
?>
<div class="wrap bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="ml-2">
                <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php _e('Conversor de Bolívares', 'ves-converter'); ?></h1>
                <p class="text-sm text-gray-600"><?php _e('Configure y administre sus tasas de conversión de Dólar a Bolívares', 'ves-converter'); ?></p>
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
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden flex-1 flex flex-col">
                <div class="bg-blue-50 p-4 border-b border-blue-100">
                    <h3 class="text-lg font-medium text-blue-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <?php _e('Establezca su tasa de hoy', 'ves-converter'); ?>
                    </h3>
                </div>
                <div class="p-6 flex-1 flex flex-col justify-center">
                    <p class="mb-5 text-lg font-medium text-gray-700"><?php _e('Elija la tasa del dia', 'ves-converter'); ?></p>                    
                    <form method="post" action="">
                        <?php wp_nonce_field('ves_converter_settings', 'ves_converter_nonce'); ?>
                        <div class="mb-6">
                            <select name="default_rate_type" id="default_rate_type" class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="bcv">
                                    <?php 
                                    _e('BCV (Banco Central)', 'ves-converter');
                                    if (!empty($rates) && isset($rates['bcv']) && isset($rates['bcv']['value'])) {
                                        echo ' (' . number_format($rates['bcv']['value'], 2) . ' Bs.)';
                                    } else {
                                        echo ' (Sin datos)';
                                    }
                                    ?>
                                </option>
                                <option value="average">
                                    <?php 
                                    _e('Tasa Promedio', 'ves-converter');
                                    if (!empty($rates) && isset($rates['average']) && isset($rates['average']['value'])) {
                                        echo ' (' . number_format($rates['average']['value'], 2) . ' Bs.)';
                                    } else {
                                        echo ' (Sin datos)';
                                    }
                                    ?>
                                </option>
                                <option value="parallel">
                                    <?php 
                                    _e('Dolar Paralelo', 'ves-converter');
                                    if (!empty($rates) && isset($rates['parallel']) && isset($rates['parallel']['value'])) {
                                        echo ' (' . number_format($rates['parallel']['value'], 2) . ' Bs.)';
                                    } else {
                                        echo ' (Sin datos)';
                                    }
                                    ?>
                                </option>
                                <option value="custom"><?php _e('Tasa Personalizada', 'ves-converter'); ?></option>
                                    </select>
                            
                            <div id="custom-rate-field" class="mt-4 hidden">
                                <label for="custom_rate_value" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Valor de Tasa Personalizada', 'ves-converter'); ?></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="custom_rate_value" 
                                           id="custom_rate_value" 
                                           step="0.01" 
                                           min="0" 
                                           class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="<?php _e('Ingrese valor de tasa personalizada', 'ves-converter'); ?>">
                                    <span class="text-gray-500 whitespace-nowrap ml-2">Bs.</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500"><?php _e('Ingrese un valor de tasa de cambio personalizado con hasta 2 decimales', 'ves-converter'); ?></p>
                            </div>
                            <p class="mt-2 text-sm text-gray-500"><?php _e('El valor se aplicará a todas las conversiones en su catálogo.', 'ves-converter'); ?></p>
                       
                        </div>
                                                
                        <div class="mt-6">
                            <button type="button" id="submit" class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <?php _e('Guardar tasa del dia', 'ves-converter'); ?>
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2zm-1 14H8v-7h8v7zm-1-11H9a1 1 0 010-2h6a1 1 0 110 2z"></path>
                                </svg>
                            </button>
                        </div>

                        <p id="rate-update-info" class="mt-2 text-sm text-gray-500">
                            <span id="rate-update-info-bcv" style="display:none;">
                                <?php _e('Se actualiza automáticamente entre las 8:00 AM y las 10:00 AM.', 'ves-converter'); ?>
                            </span>
                            <span id="rate-update-info-parallel" style="display:none;">
                                <?php _e('Se actualiza automáticamente entre las 8:45 AM y 10:00 AM - 12:45 PM y 2:00 PM.', 'ves-converter'); ?>
                            </span>
                            <span id="rate-update-info-average" style="display:none;">
                                <?php _e('Se actualiza automáticamente al variar el dolar Paralelo y BCV.', 'ves-converter'); ?>
                            </span>
                            <span id="rate-update-info-custom" style="display:none;">
                                <?php _e('Esta tasa NO cambia de forma automática.', 'ves-converter'); ?>
                            </span>
                        </p>
                    </form>
                    <script>
                    jQuery(document).ready(function($) {
                        // Mostrar el mensaje inicial según el valor del select
                        function updateRateInfo() {
                            var selectedType = $('#default_rate_type').val();
                            // Ocultar todos los mensajes
                            $('#rate-update-info-bcv, #rate-update-info-parallel, #rate-update-info-average, #rate-update-info-custom').hide();
                            
                            // Mostrar el mensaje correspondiente al tipo seleccionado
                            $('#rate-update-info-' + selectedType).show();
                        }
                        
                        // Configurar el mensaje correcto al cargar la página
                        updateRateInfo();
                        
                        // Actualizar mensaje cuando el usuario cambie la selección
                        $('#default_rate_type').on('change', function() {
                            updateRateInfo();
                            
                            // También mostrar/ocultar el campo de tasa personalizada
                            if ($(this).val() === 'custom') {
                                $('#custom-rate-field').removeClass('hidden');
                            } else {
                                $('#custom-rate-field').addClass('hidden');
                            }
                        });
                        
                        // Validate custom rate input
                        $('#custom_rate_value').on('input', function() {
                            var value = $(this).val();
                            if (value) {
                                // Ensure only 2 decimal places
                                var parts = value.split('.');
                                if (parts.length > 1 && parts[1].length > 2) {
                                    $(this).val(parseFloat(value).toFixed(2));
                                }
                            }
                        });
                        
                        // Form submission validation
                        $('form').on('submit', function(e) {
                            if ($('#default_rate_type').val() === 'custom') {
                                var customValue = $('#custom_rate_value').val();
                                if (!customValue || isNaN(customValue) || parseFloat(customValue) <= 0) {
                                    e.preventDefault();
                                    Swal.fire({
                                        icon: 'error',
                                        title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                                        text: '<?php _e('Por favor, ingrese un valor válido para la tasa personalizada.', 'ves-converter'); ?>'
                                    });
                                }
                            }
                        });
                    });
                    </script>
                </div>
                
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 mt-auto">
                    <p class="text-sm text-gray-600">
                        <?php 
                        // Usar el modelo para obtener la tasa actual
                        $current_rates = ConverterModel::get_latest_rates();
                        $selected_type = '';
                        $selected_value = 0;
                        $selected_date = '';
                        
                        if ($current_rates) {
                            foreach ($current_rates as $type => $data) {
                                if (isset($data['selected']) && $data['selected']) {
                                    $selected_type = $type;
                                    $selected_value = $data['value'];
                                    $selected_date = $data['catch_date'];
                                    break;
                                }
                            }
                            
                            if (!empty($selected_type)) {
                                $type_label = '';
                                switch ($selected_type) {
                                    case 'bcv':
                                        $type_label = __('BCV (Banco Central)', 'ves-converter');
                                        $color_class = 'text-blue-700';
                                        break;
                                    case 'average':
                                        $type_label = __('Tasa Promedio', 'ves-converter');
                                        $color_class = 'text-green-700';
                                        break;
                                    case 'parallel':
                                        $type_label = __('Dolar Paralelo', 'ves-converter');
                                        $color_class = 'text-purple-700';
                                        break;
                                    case 'custom':
                                        $type_label = __('Tasa Personalizada', 'ves-converter');
                                        $color_class = 'text-amber-700';
                                        break;
                                }
                                
                                _e('Actualmente usando:', 'ves-converter'); 
                                echo ' <span class="font-medium ' . $color_class . '">' . $type_label . '</span>';
                                echo ' <span class="mx-1">|</span> ';
                                echo '<span class="font-medium">' . number_format($selected_value, 2) . ' Bs.</span>';
                                echo ' <span class="mx-1">|</span> ';
                                echo '<span class="text-gray-500">' . $selected_date . '</span>';
                            } else {
                                _e('Actualmente usando:', 'ves-converter'); ?> <span class="font-medium text-blue-700"><?php _e('BCV (Banco Central)', 'ves-converter'); ?></span>
                            <?php
                            }
                        } else {
                            _e('Actualmente usando:', 'ves-converter'); ?> <span class="font-medium text-blue-700"><?php _e('BCV (Banco Central)', 'ves-converter'); ?></span>
                        <?php
                        }
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Rates Information -->
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden flex-1 flex flex-col">
                <div class="bg-green-50 p-4 border-b border-green-100">
                    <h3 class="text-lg font-medium text-green-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <?php _e('Referencias de Cambio Actuales', 'ves-converter'); ?>
                    </h3>
                </div>
                <div class="p-6 flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-gray-600"><?php _e('Tasas de cambio más recientes del proveedor (no es la tasa guardada en su web)', 'ves-converter'); ?></p>
                        <div class="flex gap-2">
                            <button type="button" id="update-rates" style="background-color: #f59e0b; color: white; padding: 8px 12px; border-radius: 6px; font-size: 14px; display: flex; align-items: center;">
                                <?php _e('Actualizar tasa manualmente', 'ves-converter'); ?>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <h4 class="text-sm font-medium text-blue-800 mb-1"><?php _e('Tasa BCV', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-blue-700">
                                <?php 
                                if (!empty($rates) && isset($rates['bcv']) && isset($rates['bcv']['value'])) {
                                    echo number_format($rates['bcv']['value'], 2);
                                } else {
                                    echo 'N/A';
                                }
                                ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                            <p class="text-xs text-blue-600 mt-1">
                                <?php 
                                if (!empty($rates) && isset($rates['bcv']) && isset($rates['bcv']['catch_date'])) {
                                    echo esc_html($rates['bcv']['catch_date']); 
                                }
                                ?>
                            </p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <h4 class="text-sm font-medium text-green-800 mb-1"><?php _e('Tasa Promedio', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-green-700">
                                <?php 
                                if (!empty($rates) && isset($rates['average']) && isset($rates['average']['value'])) {
                                    echo number_format($rates['average']['value'], 2);
                                } else {
                                    echo 'N/A';
                                }
                                ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <?php 
                                if (!empty($rates) && isset($rates['average']) && isset($rates['average']['catch_date'])) {
                                    echo esc_html($rates['average']['catch_date']); 
                                }
                                ?>
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                            <h4 class="text-sm font-medium text-purple-800 mb-1"><?php _e('Tasa del Dolar Paralelo', 'ves-converter'); ?></h4>
                            <p class="text-2xl font-bold text-purple-700">
                                <?php 
                                if (!empty($rates) && isset($rates['parallel']) && isset($rates['parallel']['value'])) {
                                    echo number_format($rates['parallel']['value'], 2);
                                } else {
                                    echo 'N/A';
                                }
                                ?> <span class="text-sm font-normal">Bs.</span>
                            </p>
                            <p class="text-xs text-purple-600 mt-1">
                                <?php 
                                if (!empty($rates) && isset($rates['parallel']) && isset($rates['parallel']['catch_date'])) {
                                    echo esc_html($rates['parallel']['catch_date']); 
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 mt-auto">
                    <p class="text-sm text-gray-600 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php _e('Última actualización:', 'ves-converter'); ?> <span class="font-medium text-green-700 ml-1"><?php echo $last_updated; ?></span>
                    </p>
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
                    <?php _e('Historial de Tasas', 'ves-converter'); ?>
                </h3>
            </div>
            <div class="p-6">
                <p class="mb-4 text-gray-600"><?php _e('Tasas de cambio guardadas previamente:', 'ves-converter'); ?></p>
                
                <?php if (!empty($rate_history)) : ?>
                <div id="rates-table-container">
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mx-auto max-w-4xl">
                        <table class="w-full divide-y divide-gray-200">
                        <thead>
                                <tr class="bg-gray-100">
                                    <th class="group px-4 py-3 text-left">
                                        <div class="flex items-center space-x-1 text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span><?php _e('Tipo de Tasa', 'ves-converter'); ?></span>
                                        </div>
                                    </th>
                                    <th class="group px-4 py-3 text-left">
                                        <div class="flex items-center space-x-1 text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><?php _e('Valor de Tasa', 'ves-converter'); ?></span>
                                        </div>
                                    </th>
                                    <th class="group px-4 py-3 text-left">
                                        <div class="flex items-center space-x-1 text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span><?php _e('Fecha', 'ves-converter'); ?></span>
                                        </div>
                                    </th>
                                    <th class="group px-4 py-3 text-left">
                                        <div class="flex items-center space-x-1 text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><?php _e('Hora', 'ves-converter'); ?></span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($rate_history as $index => $record) : 
                                    $rates_data = json_decode($record['rates'], true);
                                    
                                    // Encontrar la tasa seleccionada
                                    $selected_type = '';
                                    $selected_value = 0;
                                    $selected_date = '';
                                    
                                    foreach ($rates_data as $type => $data) {
                                        if (isset($data['selected']) && $data['selected']) {
                                            $selected_type = $type;
                                            $selected_value = $data['value'];
                                            $selected_date = $data['catch_date'];
                                            break;
                                        }
                                    }
                                    
                                    // Si no hay tasa seleccionada, mostrar la primera
                                    if (empty($selected_type) && !empty($rates_data)) {
                                        $first_type = array_key_first($rates_data);
                                        $selected_type = $first_type;
                                        $selected_value = $rates_data[$first_type]['value'];
                                        $selected_date = $rates_data[$first_type]['catch_date'];
                                    }
                                    
                                    // Formatear fecha y hora del created_at
                                    $created_timestamp = strtotime($record['created_at']);
                                    $date_formatted = date_i18n('d/m/Y', $created_timestamp);
                                    $time_formatted = date_i18n('h:i:s A', $created_timestamp);
                                    
                                    // Configurar color y etiqueta según el tipo
                                    $badge_color = '';
                                    $type_label = '';
                                    
                                    switch ($selected_type) {
                                        case 'bcv':
                                            $badge_color = 'bg-blue-500 text-white border border-blue-600';
                                            $type_label = 'BCV';
                                            $hover_color = 'hover:bg-blue-50';
                                            break;
                                        case 'average':
                                            $badge_color = 'bg-green-100 text-green-800 border border-green-200';
                                            $type_label = __('Promedio', 'ves-converter');
                                            $hover_color = 'hover:bg-green-50';
                                            break;
                                        case 'parallel':
                                            $badge_color = 'bg-red-100 text-red-800 border border-red-200';
                                            $type_label = __('Paralelo', 'ves-converter');
                                            $hover_color = 'hover:bg-red-50';
                                            break;
                                        case 'custom':
                                            $badge_color = 'bg-gray-100 text-gray-800 border border-gray-200';
                                            $type_label = __('Personalizada', 'ves-converter');
                                            $hover_color = 'hover:bg-gray-50';
                                            break;
                                        default:
                                            $badge_color = 'bg-gray-100 text-gray-800 border border-gray-200';
                                            $type_label = __('Desconocido', 'ves-converter');
                                            $hover_color = 'hover:bg-gray-50';
                                            break;
                                    }
                                    
                                    // Alternar colores de fila
                                    $row_class = ($index % 2 === 0) ? 'bg-white' : 'bg-gray-50';
                                ?>
                                <tr class="<?php echo $row_class . ' ' . $hover_color; ?> transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $badge_color; ?>">
                                            <?php echo esc_html($type_label); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold">
                                            <?php echo esc_html(number_format($selected_value, 2)); ?>
                                            <span class="text-xs font-normal text-gray-500">Bs.</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-700"><?php echo esc_html($date_formatted); ?></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-700"><?php echo esc_html($time_formatted); ?></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        <?php if ($total_pages > 1) : ?>
                            <div class="flex justify-center space-x-2">
                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                    <a href="#" class="pagination-link px-3 py-1 rounded <?php echo $i === $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?>" data-page="<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else : ?>
                <div class="text-center p-10 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900"><?php _e('No se encontraron registros', 'ves-converter'); ?></h3>
                    <p class="mt-1 text-gray-500"><?php _e('Aún no se han guardado registros de tasas.', 'ves-converter'); ?></p>
                    <div class="mt-6">
                        <p class="text-sm text-gray-500"><?php _e('Cuando usted u otros usuarios guarden tasas, aparecerán aquí.', 'ves-converter'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-500">
            <p>VES Converter v1.0 | <?php _e('Desarrollado con ❤️ por Grupo IDSI', 'ves-converter'); ?></p>
        </div>
    </div>
</div> 

<script>
jQuery(document).ready(function($) {
    // Save changes button
    $('#submit').on('click', function() {
        var rateType = $('#default_rate_type').val();
        var customRate = $('#custom_rate_value').val();
        
        if (rateType === 'custom' && (!customRate || isNaN(customRate) || parseFloat(customRate) <= 0)) {
            Swal.fire({
                icon: 'error',
                title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                text: '<?php _e('Por favor, ingrese un valor válido para la tasa personalizada.', 'ves-converter'); ?>'
            });
            return;
        }
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'ves_converter_rate_save',
                nonce: '<?php echo wp_create_nonce('ves_converter_rate_save'); ?>',
                rate_type: rateType,
                custom_rate: customRate
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '<?php _e('¡Éxito!', 'ves-converter'); ?>',
                        text: '<?php _e('Cambios guardados correctamente.', 'ves-converter'); ?>',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                        text: response.data.message || '<?php _e('No se pudieron guardar los cambios.', 'ves-converter'); ?>'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                    text: '<?php _e('No se pudieron guardar los cambios. Por favor, inténtelo de nuevo.', 'ves-converter'); ?>'
                });
            }
        });
    });
    
    // Update rates button
    $('#update-rates').on('click', function() {
        var button = $(this);
        button.prop('disabled', true);
        
        // Mostrar un pequeño indicador de carga
        Swal.fire({
            title: '<?php _e('Actualizando...', 'ves-converter'); ?>',
            text: '<?php _e('Obteniendo datos de tasas de cambio.', 'ves-converter'); ?>',
            icon: 'info',
            showConfirmButton: false,
            timer: 1000,
            willClose: function() {
                // Recargar la página actual
                window.location.reload();
            }
        });
    });
});
</script>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();

        var page = $(this).data('page');
        var container = $('#rates-table-container');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'load_paginated_rates',
                page: page,
                nonce: '<?php echo wp_create_nonce('load_paginated_rates'); ?>'
            },
            beforeSend: function() {
                container.css('opacity', '0.5'); // Mostrar un efecto de carga
            },
            success: function(response) {
                if (response.success) {
                    container.html(response.data.html);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                        text: response.data.message || '<?php _e('No se pudieron cargar los datos.', 'ves-converter'); ?>'
                    });
                }
            },
            complete: function() {
                container.css('opacity', '1');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '<?php _e('¡Error!', 'ves-converter'); ?>',
                    text: '<?php _e('No se pudieron cargar los datos. Por favor, inténtelo de nuevo.', 'ves-converter'); ?>'
                });
            }
        });
    });
});
</script>