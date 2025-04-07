/**
 * VES Converter Public JavaScript
 */
(function($) {
    'use strict';

    // Current rate values and type
    let currentRates = {
        bcv: 0,
        average: 0,
        parallel: 0
    };
    let currentRateType = 'bcv';
    let currentRateValue = 0;
    
    // Current conversion values
    let currentUSD = 0;
    let currentVES = 0;

    // DOM Ready
    $(document).ready(function() {
        // Initialize
        init();
        
        // Handle rate type change
        $('#rate-type').on('change', function() {
            currentRateType = $(this).val();
            updateCurrentRate();
        });
        
        // Handle convert to VES button
        $('#convert-to-ves').on('click', function() {
            const usdAmount = parseFloat($('#usd-amount').val());
            if (isNaN(usdAmount) || usdAmount <= 0) {
                showError('Please enter a valid amount in USD');
                return;
            }
            
            currentUSD = usdAmount;
            currentVES = usdAmount * currentRateValue;
            
            $('#ves-amount').val(currentVES.toFixed(2));
            showResult();
        });
        
        // Handle convert to USD button
        $('#convert-to-usd').on('click', function() {
            const vesAmount = parseFloat($('#ves-amount').val());
            if (isNaN(vesAmount) || vesAmount <= 0) {
                showError('Please enter a valid amount in VES');
                return;
            }
            
            currentVES = vesAmount;
            currentUSD = vesAmount / currentRateValue;
            
            $('#usd-amount').val(currentUSD.toFixed(2));
            showResult();
        });
        
        // Handle save conversion button
        $('#save-conversion').on('click', function() {
            if (!isLoggedIn()) {
                showError('You must be logged in to save conversions');
                return;
            }
            
            saveConversion();
        });
    });
    
    /**
     * Initialize the converter
     */
    function init() {
        // Fetch latest rates
        fetchLatestRates();
        
        // Load user history if logged in
        if (isLoggedIn()) {
            loadUserHistory();
        }
    }
    
    /**
     * Fetch latest rates from the API
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
                    
                    // Update rate display in select options
                    updateRateDisplay();
                    
                    // Set current rate value
                    updateCurrentRate();
                }
            },
            error: function() {
                showError('Error getting exchange rates');
            }
        });
    }
    
    /**
     * Update rate display in select options
     */
    function updateRateDisplay() {
        $('#rate-type option[value="bcv"]').text('BCV (' + currentRates.bcv.toFixed(2) + ' Bs.)');
        $('#rate-type option[value="average"]').text('Average (' + currentRates.average.toFixed(2) + ' Bs.)');
        $('#rate-type option[value="parallel"]').text('Parallel (' + currentRates.parallel.toFixed(2) + ' Bs.)');
    }
    
    /**
     * Update current rate value based on selected rate type
     */
    function updateCurrentRate() {
        currentRateValue = currentRates[currentRateType];
    }
    
    /**
     * Show conversion result
     */
    function showResult() {
        const resultText = currentUSD.toFixed(2) + ' USD = ' + currentVES.toFixed(2) + ' VES (Rate: ' + currentRateValue.toFixed(2) + ')';
        $('#result-text').text(resultText);
        $('#result-container').show();
    }
    
    /**
     * Save conversion to API
     */
    function saveConversion() {
        $.ajax({
            url: '/wp-json/ves-converter/v1/save-conversion',
            type: 'POST',
            data: {
                rate_type: currentRateType,
                rate_value: currentRateValue,
                amount_usd: currentUSD,
                amount_ves: currentVES
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Conversion saved successfully');
                    loadUserHistory(); // Reload history
                } else {
                    showError(response.message || 'Error saving conversion');
                }
            },
            error: function() {
                showError('Error saving conversion');
            }
        });
    }
    
    /**
     * Load user conversion history
     */
    function loadUserHistory() {
        $.ajax({
            url: '/wp-json/ves-converter/v1/user-conversions',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    renderHistory(response.data);
                } else {
                    $('#history-container').html('<p>No conversions in history</p>');
                }
            },
            error: function() {
                $('#history-container').html('<p>Error loading history</p>');
            }
        });
    }
    
    /**
     * Render user conversion history
     * 
     * @param {Array} data History data
     */
    function renderHistory(data) {
        if (!data.length) {
            $('#history-container').html('<p>No conversions in history</p>');
            return;
        }
        
        let html = '<table class="ves-converter-history-table">';
        html += '<thead><tr>';
        html += '<th>Date</th>';
        html += '<th>Rate</th>';
        html += '<th>USD</th>';
        html += '<th>VES</th>';
        html += '</tr></thead>';
        html += '<tbody>';
        
        data.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + formatDate(item.date_created) + '</td>';
            html += '<td>' + getRateTypeName(item.rate_type) + ' (' + parseFloat(item.rate_value).toFixed(2) + ')</td>';
            html += '<td>' + parseFloat(item.amount_usd).toFixed(2) + '</td>';
            html += '<td>' + parseFloat(item.amount_ves).toFixed(2) + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        
        $('#history-container').html(html);
    }
    
    /**
     * Get rate type display name
     * 
     * @param {string} type Rate type
     * @return {string} Display name
     */
    function getRateTypeName(type) {
        const types = {
            bcv: 'BCV',
            average: 'Average',
            parallel: 'Parallel'
        };
        
        return types[type] || type;
    }
    
    /**
     * Format date for display
     * 
     * @param {string} dateString Date string
     * @return {string} Formatted date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    /**
     * Show error message
     * 
     * @param {string} message Error message
     */
    function showError(message) {
        alert(message);
    }
    
    /**
     * Show success message
     * 
     * @param {string} message Success message
     */
    function showSuccess(message) {
        alert(message);
    }
    
    /**
     * Check if user is logged in
     * 
     * @return {boolean} True if logged in
     */
    function isLoggedIn() {
        return !!$('.ves-converter-history').length;
    }
})(jQuery); 