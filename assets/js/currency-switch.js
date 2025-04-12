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
    let rateType = 'bcv'; // Tipo de tasa seleccionada
    let processedElements = []; // Elementos ya procesados
    let isProcessing = false; // Bandera para evitar procesamiento simultáneo
    
    // Al cargar el documento
    $(document).ready(function() {
        // Obtener datos pasados desde PHP
        if (typeof vesCurrencyData !== 'undefined') {
            // Obtener el valor de la tasa seleccionada y asegurar que sea un número
            rateValue = parseFloat(vesCurrencyData.rate_value) || 0;
            
            // Obtener el tipo de tasa seleccionada
            rateType = vesCurrencyData.selected_rate || 'bcv';
            
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
            
            // Escuchar cambios en el switch - Usar delegación de eventos para mayor fiabilidad
            $(document).on('click', '#ves-currency-switch', function(e) {
                e.preventDefault();
                toggleCurrency();
                return false;
            });
            
            // Comprobar periódicamente si los símbolos de moneda están intactos (enfoque más ligero)
            setInterval(checkCurrencySymbols, 2000);
            
            // Actualizar los precios periódicamente (para manejar cambios en el DOM por traducciones)
            // pero solo catalogar nuevos precios, no los ya existentes
            setInterval(function() {
                if (!isProcessing) {
                    isProcessing = true;
                    
                    // Buscar solo precios nuevos que no hayan sido procesados
                    catalogNewPrices();
                    
                    // Aplicar la moneda actual a todos los precios
                    applyCurrency(currentCurrency);
                    
                    isProcessing = false;
                }
            }, 3000);
        }
    });
    
    /**
     * Verificar y restaurar los símbolos de moneda si han sido cambiados
     */
    function checkCurrencySymbols() {
        // Usar los valores de los atributos data-nocontent en lugar de texto directo
        const $usdIcon = $('.usd-icon');
        const $bsIcon = $('.bs-icon');
        
        if ($usdIcon.length) {
            $usdIcon.html('&#36;');
        }
        
        if ($bsIcon.length) {
            $bsIcon.html('B&#115;.');
        }
    }
    
    /**
     * Inicializar el estado del switch
     */
    function initCurrencySwitch() {
        updateSwitchUI();
    }
    
    /**
     * Catalogar todos los precios en la página inicialmente
     */
    function catalogPrices() {
        // Estrategia 1: Buscar y marcar texto directo (como en la versión anterior)
        findPricesInTextNodes();
        
        // Estrategia 2: Buscar texto en todo el HTML (incluso dentro de elementos anidados)
        findPricesInHTML();
    }
    
    /**
     * Catalogar solo precios nuevos para evitar anidamiento
     */
    function catalogNewPrices() {
        // Solo buscar en elementos que no sean .ves-price-element ni contengan estos elementos
        $('body').find(':not(.ves-price-element)').filter(function() {
            return $(this).find('.ves-price-element').length === 0;
        }).each(function() {
            const $element = $(this);
            
            // Verificar si ya procesamos este elemento
            if (processedElements.includes(this)) {
                return;
            }
            
            // Verificar si el elemento es visible
            if (!$element.is(':visible')) {
                return;
            }
            
            // Buscar nodos de texto directos que contengan precios
            $element.contents().filter(function() {
                return this.nodeType === 3 && this.nodeValue && this.nodeValue.match(/\$\s*\d+(\.\d+)?/);
            }).each(function() {
                // Procesar este nodo de texto
                processTextNodeForPrices(this);
            });
            
            // Verificar si hay precios en el HTML
            const html = $element.html();
            if (html && typeof html === 'string' && html.indexOf('$') >= 0 && !html.includes('ves-price-element')) {
                processPricesInHTML($element);
            }
        });
    }
    
    /**
     * Busca precios en nodos de texto directos
     */
    function findPricesInTextNodes() {
        // Buscar todos los elementos con precios ($ seguido de un número)
        $('body').find(':not(script, style, noscript, .ves-price-element)').contents().filter(function() {
            return this.nodeType === 3 && this.nodeValue && this.nodeValue.match(/\$\s*\d+(\.\d+)?/);
        }).each(function() {
            processTextNodeForPrices(this);
        });
    }
    
    /**
     * Procesa un nodo de texto para encontrar y marcar precios
     */
    function processTextNodeForPrices(textNode) {
        const parent = textNode.parentNode;
        
        // Verificar si ya procesamos este elemento
        if (processedElements.includes(parent)) {
            return;
        }
        
        // Extraer el texto completo
        const text = textNode.nodeValue;
        if (!text) return;
        
        // Extraer el valor numérico y el patrón completo
        const matches = text.match(/(\$\s*(\d+(?:\.\d+)?))/g);
        
        if (matches && matches.length) {
            // Para cada coincidencia en el texto
            matches.forEach(function(match) {
                // Extraer el valor numérico
                const valueMatch = match.match(/\$\s*(\d+(?:\.\d+)?)/);
                if (valueMatch && valueMatch[1]) {
                    const priceValue = parseFloat(valueMatch[1]);
                    
                    // Crear un contenedor para el precio y reemplazar el nodo de texto
                    const priceWrapper = document.createElement('span');
                    priceWrapper.classList.add('ves-price-element');
                    priceWrapper.setAttribute('data-price-value', priceValue);
                    priceWrapper.setAttribute('data-price-original', match);
                    priceWrapper.setAttribute('data-currency', 'usd');
                    priceWrapper.textContent = match;
                    
                    // Reemplazar el nodo de texto con el nuevo elemento envuelto
                    const beforeText = text.substring(0, text.indexOf(match));
                    const afterText = text.substring(text.indexOf(match) + match.length);
                    
                    // Crear nodos de texto para antes y después del precio
                    if (beforeText) {
                        parent.insertBefore(document.createTextNode(beforeText), textNode);
                    }
                    
                    parent.insertBefore(priceWrapper, textNode);
                    
                    if (afterText) {
                        parent.insertBefore(document.createTextNode(afterText), textNode);
                    }
                    
                    // Eliminar el nodo de texto original
                    parent.removeChild(textNode);
                    
                    // Marcar el padre como procesado
                    processedElements.push(parent);
                }
            });
        }
    }
    
    /**
     * Procesa precios en el HTML de un elemento
     */
    function processPricesInHTML($element) {
        const html = $element.html();
        if (!html || typeof html !== 'string' || html.includes('class="ves-price-element"')) {
            return;
        }
        
        // Comprobar si el HTML contiene un patrón de precio
        const pricePattern = /\$\s*\d+(\.\d+)?/g;
        if (pricePattern.test(html)) {
            // Verificar si el texto visible tiene el patrón de precio
            const visibleText = $element.text();
            const priceMatches = visibleText.match(pricePattern);
            
            if (priceMatches && priceMatches.length) {
                let tempHTML = html;
                let modified = false;
                
                // Para cada coincidencia
                priceMatches.forEach(function(match) {
                    // Extraer el valor numérico
                    const valueMatch = match.match(/\$\s*(\d+(?:\.\d+)?)/);
                    if (valueMatch && valueMatch[1]) {
                        const priceValue = parseFloat(valueMatch[1]);
                        
                        // Solo si es un precio válido
                        if (!isNaN(priceValue) && priceValue > 0) {
                            // Reemplazar solo la primera aparición para evitar reemplazos innecesarios
                            // Usamos un patrón de escape para el simbolo $
                            const escapedMatch = match.replace('$', '\\$');
                            const replaceRegex = new RegExp(escapedMatch);
                            
                            // Solo reemplazo si no está ya dentro de un .ves-price-element
                            if (!tempHTML.includes('class="ves-price-element"')) {
                                tempHTML = tempHTML.replace(
                                    replaceRegex, 
                                    `<span class="ves-price-element" data-price-value="${priceValue}" data-price-original="${match}" data-currency="usd">${match}</span>`
                                );
                                modified = true;
                            }
                        }
                    }
                });
                
                // Solo actualizar si ha habido cambios
                if (modified && tempHTML !== html) {
                    $element.html(tempHTML);
                    processedElements.push($element[0]);
                }
            }
        }
    }
    
    /**
     * Busca precios en el HTML (incluso dentro de elementos anidados con font, etc.)
     */
    function findPricesInHTML() {
        // Buscar todos los elementos que no tengan la clase ves-price-element
        $('body *:not(.ves-price-element)').filter(function() {
            // Excluir elementos que ya contienen precios procesados
            return $(this).find('.ves-price-element').length === 0;
        }).each(function() {
            processPricesInHTML($(this));
        });
    }
    
    /**
     * Aplicar la moneda seleccionada a todos los precios
     * 
     * @param {string} currency Moneda a aplicar (usd o bs)
     */
    function applyCurrency(currency) {
        // Actualizar todos los elementos con clase ves-price-element
        $('.ves-price-element').each(function() {
            const $element = $(this);
            const priceValue = parseFloat($element.attr('data-price-value'));
            const originalFormat = $element.attr('data-price-original');
            const currentDataCurrency = $element.attr('data-currency');
            
            // Solo actualizar si hay un cambio en la moneda
            if (currentDataCurrency !== currency) {
                if (currency === 'usd') {
                    // Volver al formato original en dólares
                    $element.text(originalFormat);
                    $element.attr('data-currency', 'usd');
                } else {
                    // Convertir a bolívares
                    const bsValue = priceValue * rateValue;
                    const formattedValue = formatNumber(bsValue);
                    $element.text('Bs. ' + formattedValue);
                    $element.attr('data-currency', 'bs');
                }
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
        const rate_info = $('.ves-rate-tag');
        
        // Cambiar estilos del botón
        if (currentCurrency === 'usd') {
            $switch.removeClass('switched');
            // Verde para USD
            $switch.css('background-color', '#009933');
            rate_info.css('background-color', 'rgba(0, 153, 51, 0.8)');
            // Cambiar el texto del tag para USD
            rate_info.text('Dólares');
        } else {
            $switch.addClass('switched');
            
            // Colores según tipo de tasa
            if (rateType === 'bcv') {
                // Azul para BCV
                $switch.css('background-color', '#0066cc');
                rate_info.css('background-color', 'rgba(0, 102, 204, 0.8)');
            } else {
                // Naranjo para otros tipos de tasa (average, parallel, custom)
                $switch.css('background-color', '#FF8C00');
                rate_info.css('background-color', 'rgba(255, 140, 0, 0.8)');
            }
            
            // Asegurarse de que rateValue sea un número
            const rateValueNum = parseFloat(rateValue);
            
            // Cambiar el texto del tag para VES mostrando el valor de la tasa
            if (!isNaN(rateValueNum)) {
                rate_info.text('Tasa: ' + rateValueNum.toFixed(2));
            } else {
                rate_info.text('Tasa: 0.00');
            }
        }
    }
    
})(jQuery); 