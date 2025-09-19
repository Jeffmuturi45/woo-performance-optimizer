jQuery(document).ready(function($) {
    // Clear cache button
    $('#wpo-clear-cache').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        
        button.text('Clearing...').prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'wpo_clear_cache',
            nonce: wpo_admin.nonce
        }, function(response) {
            if (response.success) {
                alert('Cache cleared successfully.');
            } else {
                alert('Error clearing cache: ' + response.data);
            }
        }).fail(function() {
            alert('Error clearing cache. Please try again.');
        }).always(function() {
            button.text(originalText).prop('disabled', false);
        });
    });
    
    // Optimize now button
    $('#wpo-optimize-now').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        
        button.text('Optimizing...').prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'wpo_optimize_now',
            nonce: wpo_admin.nonce
        }, function(response) {
            if (response.success) {
                alert('Database optimized successfully.');
            } else {
                alert('Error optimizing database: ' + response.data);
            }
        }).fail(function() {
            alert('Error optimizing database. Please try again.');
        }).always(function() {
            button.text(originalText).prop('disabled', false);
        });
    });
});