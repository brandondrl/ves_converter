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
            
            // Show success message using SweetAlert2
            const originalText = $(this).text();
            $(this).text('Copied!');
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Endpoint copied to clipboard',
                showConfirmButton: false,
                timer: 1500
            });
            
            setTimeout(() => {
                $(this).text(originalText);
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
                    $('#submit').prop('disabled', true).val('Saving...');
                },
                success: function(response) {
                    $('#submit').prop('disabled', false).val('Save Changes');
                    
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.data.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    $('#submit').prop('disabled', false).val('Save Changes');
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while saving the settings.',
                        confirmButtonText: 'OK'
                    });
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