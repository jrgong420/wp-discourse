<?php
/**
 * Debug Admin Notice for SSO Icon Issue
 * 
 * Add this to your theme's functions.php temporarily or create as a mu-plugin
 * to get debugging info in WordPress admin.
 * 
 * REMOVE AFTER DEBUGGING!
 */

// Add admin notice with debug info
add_action('admin_notices', function() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only show on Discourse settings pages
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'discourse') === false) {
        return;
    }
    
    $sso_client_options = get_option('discourse_sso_client', array());
    
    echo '<div class="notice notice-info">';
    echo '<h3>SSO Icon Debug Info</h3>';
    
    // Check settings
    $icon_enabled = !empty($sso_client_options['sso-client-login-icon-enabled']);
    $icon_id = !empty($sso_client_options['sso-client-login-icon-id']) ? (int)$sso_client_options['sso-client-login-icon-id'] : 0;
    $icon_size = isset($sso_client_options['sso-client-login-icon-size']) ? (int)$sso_client_options['sso-client-login-icon-size'] : 24;
    
    echo '<p><strong>Settings Status:</strong></p>';
    echo '<ul>';
    echo '<li>Icon Enabled: ' . ($icon_enabled ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>Icon ID: ' . ($icon_id ? '✅ ' . $icon_id : '❌ Not set') . '</li>';
    echo '<li>Icon Size: ' . $icon_size . 'px</li>';
    echo '</ul>';
    
    if ($icon_id) {
        // Test attachment
        $attachment = get_post($icon_id);
        echo '<p><strong>Attachment Status:</strong></p>';
        echo '<ul>';
        echo '<li>Attachment exists: ' . ($attachment ? '✅ YES' : '❌ NO') . '</li>';
        
        if ($attachment) {
            echo '<li>Type: ' . $attachment->post_mime_type . '</li>';
            echo '<li>Status: ' . $attachment->post_status . '</li>';
            
            $file_path = get_attached_file($icon_id);
            echo '<li>File exists: ' . (file_exists($file_path) ? '✅ YES' : '❌ NO') . '</li>';
            
            // Test wp_get_attachment_image
            $test_html = wp_get_attachment_image($icon_id, array(24, 24));
            echo '<li>wp_get_attachment_image works: ' . ($test_html ? '✅ YES' : '❌ NO') . '</li>';
            
            if ($test_html) {
                echo '<li>Preview: ' . $test_html . '</li>';
            }
        }
        echo '</ul>';
    }
    
    // Test the actual SSO link generation
    if (class_exists('WPDiscourse\SSOClient\SSOClientBase')) {
        echo '<p><strong>SSO Link Test:</strong></p>';
        try {
            $sso_client = new WPDiscourse\SSOClient\SSOClientBase();
            $test_link = $sso_client->get_discourse_sso_link_markup(array());
            echo '<p>Generated Link HTML:</p>';
            echo '<pre style="background: #f0f0f0; padding: 10px; overflow-x: auto;">' . htmlspecialchars($test_link) . '</pre>';
            echo '<p>Rendered Preview:</p>';
            echo '<div style="border: 1px solid #ccc; padding: 10px; background: white;">' . $test_link . '</div>';
        } catch (Exception $e) {
            echo '<p>❌ Error generating SSO link: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p>❌ SSOClientBase class not found</p>';
    }
    
    echo '</div>';
});

// Add a test shortcode for easy testing
add_shortcode('debug_sso_icon', function($atts) {
    if (!current_user_can('manage_options')) {
        return 'Debug shortcode only available to administrators.';
    }
    
    $output = '<div style="border: 2px solid #0073aa; padding: 15px; margin: 10px 0; background: #f7f7f7;">';
    $output .= '<h4>SSO Icon Debug Test</h4>';
    
    if (class_exists('WPDiscourse\SSOClient\SSOClientBase')) {
        try {
            $sso_client = new WPDiscourse\SSOClient\SSOClientBase();
            $test_link = $sso_client->get_discourse_sso_link_markup(array());
            $output .= '<p><strong>Generated SSO Link:</strong></p>';
            $output .= '<div style="background: white; padding: 10px; border: 1px solid #ddd;">' . $test_link . '</div>';
            $output .= '<p><strong>Raw HTML:</strong></p>';
            $output .= '<pre style="background: #eee; padding: 10px; font-size: 12px; overflow-x: auto;">' . htmlspecialchars($test_link) . '</pre>';
        } catch (Exception $e) {
            $output .= '<p style="color: red;">Error: ' . $e->getMessage() . '</p>';
        }
    } else {
        $output .= '<p style="color: red;">SSOClientBase class not available</p>';
    }
    
    $output .= '</div>';
    return $output;
});
?>
