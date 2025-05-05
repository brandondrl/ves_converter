<?php if (!empty($rate_history)) : ?>
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
                                            <span><?php _e('Guardado', 'ves-converter'); ?></span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($rate_history as $index => $record) : 
                $rates_data = json_decode($record['rates'], true);
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
                $hover_color = '';

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
<?php else : ?>
<div class="text-center p-10 bg-gray-50 rounded-lg border border-gray-200">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    <h3 class="mt-2 text-lg font-medium text-gray-900"><?php _e('No se encontraron registros', 'ves-converter'); ?></h3>
    <p class="mt-1 text-gray-500"><?php _e('Aún no se han guardado registros de tasas.', 'ves-converter'); ?></p>
</div>
<?php endif; ?>