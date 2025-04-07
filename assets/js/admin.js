/**
 * VES Converter Admin JavaScript
 */
(function($) {
    'use strict';

    // Ready event
    $(document).ready(function() {
        // Handle endpoint copying
        $('.copy-endpoint').on('click', function() {
            const endpoint = $(this).data('endpoint');
            copyToClipboard(endpoint);
            
            // Show success message
            $(this).text('Copiado!');
            setTimeout(() => {
                $(this).text('Copiar');
            }, 2000);
        });
        
        // Handle form submission
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            const defaultRateType = $('select[name="default_rate_type"]').val();
            
            $.ajax({
                url: vesConverterAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_ves_converter_settings',
                    nonce: vesConverterAdmin.nonce,
                    default_rate_type: defaultRateType
                },
                beforeSend: function() {
                    $('#submit').prop('disabled', true).val('Guardando...');
                },
                success: function(response) {
                    $('#submit').prop('disabled', false).val('Guardar Cambios');
                    
                    if (response.success) {
                        // Show success message
                        $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                            .insertBefore('form')
                            .delay(3000)
                            .fadeOut(function() {
                                $(this).remove();
                            });
                    } else {
                        // Show error message
                        $('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>')
                            .insertBefore('form');
                    }
                },
                error: function() {
                    $('#submit').prop('disabled', false).val('Guardar Cambios');
                    
                    // Show error message
                    $('<div class="notice notice-error is-dismissible"><p>Ocurrió un error al guardar la configuración.</p></div>')
                        .insertBefore('form');
                }
            });
        });
    });
    
    /**
     * Copy text to clipboard
     * 
     * @param {string} text Text to copy
     */
    function copyToClipboard(text) {
        // Create a temporary input
        const input = document.createElement('input');
        input.style.position = 'fixed';
        input.style.opacity = 0;
        input.value = text;
        document.body.appendChild(input);
        
        // Select and copy
        input.select();
        document.execCommand('copy');
        
        // Remove the temporary input
        document.body.removeChild(input);
    }
})(jQuery); 