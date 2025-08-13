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
                try {
                    // Check if selection exists
                    var selection = mediaFrame.state().get('selection');
                    if (!selection || !selection.first()) {
                        console.error('WP Discourse: No media selection found');
                        return;
                    }

                    // Safely get attachment data
                    var attachment;
                    try {
                        attachment = selection.first().toJSON();
                    } catch (e) {
                        console.error('WP Discourse: Error parsing attachment data:', e);
                        return;
                    }

                    // Verify attachment and attachment ID exist
                    if (!attachment || !attachment.id) {
                        console.error('WP Discourse: Invalid attachment data - missing ID');
                        return;
                    }

                    // Update hidden input with attachment ID
                    targetInput.val(attachment.id);

                    // Safely derive thumbnail URL with fallbacks
                    var thumbnailUrl = '';
                    if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) {
                        thumbnailUrl = attachment.sizes.thumbnail.url;
                    } else if (attachment.url) {
                        thumbnailUrl = attachment.url;
                    } else {
                        // Use a default placeholder or clear the preview
                        console.warn('WP Discourse: No valid image URL found for attachment');
                        thumbnailUrl = ''; // Could also use a default placeholder URL
                    }

                    // Only update preview and show elements when we have a valid URL
                    if (thumbnailUrl) {
                        previewContainer.find('img').attr('src', thumbnailUrl);
                        previewContainer.show();
                        removeButton.show();

                        // Mark as changed for WordPress settings only after successful update
                        targetInput.trigger('change');
                    } else {
                        // Clear the input if no valid URL is available
                        targetInput.val('');
                        previewContainer.hide();
                        removeButton.hide();
                    }

                } catch (error) {
                    console.error('WP Discourse: Error handling media selection:', error);
                    // Clear the input on error to prevent broken state
                    targetInput.val('');
                    previewContainer.hide();
                    removeButton.hide();
                }
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
