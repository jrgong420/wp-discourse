/**
 * WordPress Media Library integration for Discourse login link customization
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize media library functionality
        initMediaLibrary();
    });

    /**
     * Initialize media library functionality
     */
    function initMediaLibrary() {
        var mediaFrame;

        // Handle media selection button click
        $(document).on('click', '.wpdc-select-media', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetId = button.data('target');
            var targetInput = $('#' + targetId);
            var previewContainer = button.siblings('.wpdc-media-preview');
            var removeButton = button.siblings('.wpdc-remove-media');

            // Create media frame if it doesn't exist
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Select Login Link Icon',
                button: {
                    text: 'Use This Icon'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // Handle media selection
            mediaFrame.on('select', function() {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                
                // Update hidden input with attachment ID
                targetInput.val(attachment.id);
                
                // Update preview image
                var thumbnailUrl = attachment.sizes && attachment.sizes.thumbnail 
                    ? attachment.sizes.thumbnail.url 
                    : attachment.url;
                    
                previewContainer.find('img').attr('src', thumbnailUrl);
                previewContainer.show();
                removeButton.show();
                
                // Mark as changed for WordPress settings
                targetInput.trigger('change');
            });

            mediaFrame.open();
        });

        // Handle media removal button click
        $(document).on('click', '.wpdc-remove-media', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var targetId = button.data('target');
            var targetInput = $('#' + targetId);
            var previewContainer = button.siblings('.wpdc-media-preview');

            // Clear the input value
            targetInput.val('');
            
            // Hide preview and remove button
            previewContainer.hide();
            button.hide();
            
            // Mark as changed for WordPress settings
            targetInput.trigger('change');
        });

        // Show/hide icon settings based on icon enabled checkbox
        $(document).on('change', '#discourse-sso-client-login-icon-enabled', function() {
            var isChecked = $(this).is(':checked');
            var iconSettings = $('.wpdc-media-selector, #discourse-sso-client-login-icon-size').closest('tr');
            
            if (isChecked) {
                iconSettings.show();
            } else {
                iconSettings.hide();
            }
        });

        // Initialize visibility on page load
        var iconEnabledCheckbox = $('#discourse-sso-client-login-icon-enabled');
        if (iconEnabledCheckbox.length) {
            iconEnabledCheckbox.trigger('change');
        }
    }

})(jQuery);
