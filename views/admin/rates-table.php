<?php if (!empty($rate_history)) : ?>
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mx-auto max-w-4xl">
    <table class="w-full divide-y divide-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-3 text-left"><?php _e('Tipo de Tasa', 'ves-converter'); ?></th>
                <th class="px-4 py-3 text-left"><?php _e('Valor de Tasa', 'ves-converter'); ?></th>
                <th class="px-4 py-3 text-left"><?php _e('Fecha', 'ves-converter'); ?></th>
                <th class="px-4 py-3 text-left"><?php _e('Hora (GMT-4)', 'ves-converter'); ?></th>
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
                $gmt4_timestamp = strtotime('-4 hours', $created_timestamp);
                $date_formatted = date('d/m/Y', $gmt4_timestamp);
                $time_formatted = date('h:i:s A', $gmt4_timestamp);

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