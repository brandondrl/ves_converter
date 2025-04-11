/**
 * VES Currency Switch
 * 
 * JavaScript para el switch de moneda USD/BS
 */
(function($) {
    'use strict';
    
    // Variables globales
    let currentCurrency = 'usd'; // Moneda actual (usd o bs)
    let originalPrices = {}; // Almacena los precios originales
    let rateValue = 0; // Valor de la tasa seleccionada
    
    // Al cargar el documento
    $(document).ready(function() {
        // Obtener datos pasados desde PHP
        if (typeof vesCurrencyData !== 'undefined') {
            // Obtener el valor de la tasa seleccionada
            rateValue = vesCurrencyData.rate_value || 0;
            
            // Configurar moneda inicial
            if (vesCurrencyData.initial_currency) {
                currentCurrency = vesCurrencyData.initial_currency;
            }
            
            // Verificar si hay una preferencia guardada
            const savedCurrency = localStorage.getItem('ves_current_currency');
            if (savedCurrency) {
                currentCurrency = savedCurrency;
            }
            
            // Inicializar
            initCurrencySwitch();
            
            // Catalogar todos los precios en la página
            catalogPrices();
            
            // Aplicar la moneda inicial
            applyCurrency(currentCurrency);
            
            // Escuchar cambios en el switch
            $('#ves-currency-switch').on('click', function() {
                toggleCurrency();
            });
        }
    });
    
    /**
     * Inicializar el estado del switch
     */
    function initCurrencySwitch() {
        updateSwitchUI();
    }
    
    /**
     * Catalogar todos los precios en la página
     */
    function catalogPrices() {
        // Buscar todos los elementos con precios ($ seguido de un número)
        $('body').find(':not(script, style, noscript)').contents().filter(function() {
            return this.nodeType === 3 && this.nodeValue.match(/\$\s*\d+(\.\d+)?/);
        }).each(function() {
            const textNode = this;
            const parent = textNode.parentNode;
            
            // Extraer el texto completo
            const text = textNode.nodeValue;
            
            // Extraer el valor numérico y el patrón completo
            const matches = text.match(/(\$\s*(\d+(?:\.\d+)?))/g);
            
            if (matches && matches.length) {
                // Para cada coincidencia en el texto
                matches.forEach(function(match) {
                    // Extraer el valor numérico
                    const valueMatch = match.match(/\$\s*(\d+(?:\.\d+)?)/);
                    if (valueMatch && valueMatch[1]) {
                        const priceValue = parseFloat(valueMatch[1]);
                        
                        // Crear un ID único para este precio
                        const id = 'price-' + Math.random().toString(36).substring(2, 15);
                        
                        // Guardar la referencia
                        originalPrices[id] = {
                            node: textNode,
                            parent: parent,
                            fullText: text,
                            originalMatch: match,
                            value: priceValue
                        };
                    }
                });
            }
        });
    }
    
    /**
     * Aplicar la moneda seleccionada a todos los precios
     * 
     * @param {string} currency Moneda a aplicar (usd o bs)
     */
    function applyCurrency(currency) {
        // Iterar sobre todos los precios catalogados
        Object.keys(originalPrices).forEach(function(id) {
            const item = originalPrices[id];
            const textNode = item.node;
            const fullText = item.fullText;
            const match = item.originalMatch;
            const value = item.value;
            
            let newText = fullText;
            
            if (currency === 'usd') {
                // No hay cambios, mantener el texto original
                // Esto ya maneja el caso de múltiples precios en el mismo nodo
            } else if (currency === 'bs') {
                // Convertir a bolívares
                const bsValue = value * rateValue;
                const formattedValue = formatNumber(bsValue);
                
                // Reemplazar solo la parte que coincide con el patrón
                newText = fullText.replace(match, 'Bs. ' + formattedValue);
            }
            
            // Actualizar el nodo de texto solo si ha cambiado
            if (textNode.nodeValue !== newText) {
                textNode.nodeValue = newText;
            }
        });
    }
    
    /**
     * Formatear número con separador de miles y decimales
     * 
     * @param {number} num Número a formatear
     * @return {string} Número formateado
     */
    function formatNumber(num) {
        return num.toLocaleString('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    /**
     * Alternar entre monedas
     */
    function toggleCurrency() {
        currentCurrency = currentCurrency === 'usd' ? 'bs' : 'usd';
        
        // Guardar preferencia
        localStorage.setItem('ves_current_currency', currentCurrency);
        
        // Actualizar UI
        updateSwitchUI();
        
        // Aplicar la nueva moneda
        applyCurrency(currentCurrency);
    }
    
    /**
     * Actualizar la interfaz del switch
     */
    function updateSwitchUI() {
        const $switch = $('#ves-currency-switch');
        
        if (currentCurrency === 'usd') {
            $switch.removeClass('switched');
        } else {
            $switch.addClass('switched');
        }

        // Actualizar también la etiqueta de tasa con información dinámica
        const rate_info = $('.ves-rate-tag');
        const rate_name = vesCurrencyData.selected_rate.toUpperCase();
        const rate_value = formatNumber(vesCurrencyData.rate_value);

        if (currentCurrency === 'usd') {
            rate_info.css('background-color', 'rgba(0, 102, 204, 0.8)');
        } else {
            rate_info.css('background-color', 'rgba(0, 153, 51, 0.8)');
        }
    }
    
})(jQuery); 