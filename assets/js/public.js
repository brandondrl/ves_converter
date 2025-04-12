/**
 * JavaScript Público de VES Converter
 */
(function($) {
    'use strict';

    // Valores y tipo de tasa actual
    let currentRates = {
        bcv: 0,
        average: 0,
        parallel: 0
    };
    let currentRateType = 'bcv';
    let currentRateValue = 0;
    
    // Valores de conversión actuales
    let currentUSD = 0;
    let currentVES = 0;

    // DOM Ready
    $(document).ready(function() {
        // Inicializar
        init();
        
        // Manejar cambio de tipo de tasa
        $('#rate-type').on('change', function() {
            currentRateType = $(this).val();
            updateCurrentRate();
        });
        
        // Manejar botón de convertir a VES
        $('#convert-to-ves').on('click', function() {
            const usdAmount = parseFloat($('#usd-amount').val());
            if (isNaN(usdAmount) || usdAmount <= 0) {
                showError('Por favor, introduzca un monto válido en USD');
                return;
            }
            
            currentUSD = usdAmount;
            currentVES = usdAmount * currentRateValue;
            
            $('#ves-amount').val(currentVES.toFixed(2));
            showResult();
        });
        
        // Manejar botón de convertir a USD
        $('#convert-to-usd').on('click', function() {
            const vesAmount = parseFloat($('#ves-amount').val());
            if (isNaN(vesAmount) || vesAmount <= 0) {
                showError('Por favor, introduzca un monto válido en VES');
                return;
            }
            
            currentVES = vesAmount;
            currentUSD = vesAmount / currentRateValue;
            
            $('#usd-amount').val(currentUSD.toFixed(2));
            showResult();
        });
        
        // Manejar botón de guardar conversión
        $('#save-conversion').on('click', function() {
            if (!isLoggedIn()) {
                showError('Debe iniciar sesión para guardar conversiones');
                return;
            }
            
            saveConversion();
        });
    });
    
    /**
     * Inicializar el conversor
     */
    function init() {
        // Obtener tasas más recientes
        fetchLatestRates();
        
        // Cargar historial del usuario si está conectado
        if (isLoggedIn()) {
            loadUserHistory();
        }
    }
    
    /**
     * Obtener las tasas más recientes de la API
     */
    function fetchLatestRates() {
        $.ajax({
            url: '/wp-json/ves-change-getter/v1/latest',
            type: 'GET',
            success: function(response) {
                if (response && response.rates) {
                    currentRates = {
                        bcv: parseFloat(response.rates.bcv) || 0,
                        average: parseFloat(response.rates.average) || 0,
                        parallel: parseFloat(response.rates.parallel) || 0
                    };
                    
                    // Actualizar visualización de tasas en opciones de selección
                    updateRateDisplay();
                    
                    // Establecer valor de tasa actual
                    updateCurrentRate();
                }
            },
            error: function() {
                showError('Error al obtener tasas de cambio');
            }
        });
    }
    
    /**
     * Actualizar visualización de tasas en opciones de selección
     */
    function updateRateDisplay() {
        $('#rate-type option[value="bcv"]').text('BCV (' + currentRates.bcv.toFixed(2) + ' Bs.)');
        $('#rate-type option[value="average"]').text('Promedio (' + currentRates.average.toFixed(2) + ' Bs.)');
        $('#rate-type option[value="parallel"]').text('Paralelo (' + currentRates.parallel.toFixed(2) + ' Bs.)');
    }
    
    /**
     * Actualizar valor de tasa actual basado en el tipo de tasa seleccionado
     */
    function updateCurrentRate() {
        currentRateValue = currentRates[currentRateType];
    }
    
    /**
     * Mostrar resultado de conversión
     */
    function showResult() {
        const resultText = currentUSD.toFixed(2) + ' USD = ' + currentVES.toFixed(2) + ' VES (Tasa: ' + currentRateValue.toFixed(2) + ')';
        $('#result-text').text(resultText);
        $('#result-container').removeClass('hidden').addClass('block');
    }
    
    /**
     * Guardar conversión en API
     */
    function saveConversion() {
        $.ajax({
            url: '/wp-json/ves-converter/v1/save-conversion',
            type: 'POST',
            data: {
                rate_type: currentRateType,
                rate_value: currentRateValue
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Conversión guardada exitosamente');
                    loadUserHistory(); // Recargar historial
                } else {
                    showError(response.message || 'Error al guardar la conversión');
                }
            },
            error: function() {
                showError('Error al guardar la conversión');
            }
        });
    }
    
    /**
     * Cargar historial de conversiones del usuario
     */
    function loadUserHistory() {
        $.ajax({
            url: '/wp-json/ves-converter/v1/user-conversions',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    renderHistory(response.data);
                } else {
                    $('#history-container').html('<p>No hay conversiones en el historial</p>');
                }
            },
            error: function() {
                $('#history-container').html('<p>Error al cargar el historial</p>');
            }
        });
    }
    
    /**
     * Renderizar historial de conversiones del usuario
     * 
     * @param {Array} data Datos del historial
     */
    function renderHistory(data) {
        if (!data.length) {
            $('#history-container').html('<p class="text-gray-600">No hay conversiones en el historial</p>');
            return;
        }
        
        let html = '<table class="min-w-full divide-y divide-gray-200">';
        html += '<thead class="bg-gray-50"><tr>';
        html += '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>';
        html += '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Tasa</th>';
        html += '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor de Tasa</th>';
        html += '</tr></thead>';
        html += '<tbody class="bg-white divide-y divide-gray-200">';
        
        data.forEach(function(item) {
            html += '<tr>';
            html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + formatDate(item.date) + '</td>';
            html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + getRateTypeName(item.rate_type) + '</td>';
            html += '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + item.rate_value.toFixed(2) + ' Bs.</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        $('#history-container').html(html);
    }
    
    /**
     * Obtener nombre de visualización para el tipo de tasa
     * 
     * @param {string} type Tipo de tasa
     * @return {string} Nombre de visualización
     */
    function getRateTypeName(type) {
        const types = {
            bcv: 'BCV',
            average: 'Promedio',
            parallel: 'Paralelo',
            custom: 'Personalizada'
        };
        
        return types[type] || type;
    }
    
    /**
     * Formatear fecha para visualización
     * 
     * @param {string} dateString Cadena de fecha
     * @return {string} Fecha formateada
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        
        // Formatear como dd/mm/aaaa h:mm a
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        let hours = date.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // La hora '0' debe ser '12'
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
    }
    
    /**
     * Mostrar mensaje de error
     * 
     * @param {string} message Mensaje de error
     */
    function showError(message) {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    }
    
    /**
     * Mostrar mensaje de éxito
     * 
     * @param {string} message Mensaje de éxito
     */
    function showSuccess(message) {
        Swal.fire({
            title: 'Éxito',
            text: message,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    }
    
    /**
     * Verificar si el usuario ha iniciado sesión
     * 
     * @return {boolean} Verdadero si ha iniciado sesión
     */
    function isLoggedIn() {
        return !!$('.ves-converter-history').length;
    }
})(jQuery); 