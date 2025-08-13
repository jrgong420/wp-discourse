<?php
/**
 * Debug script for SSO Client Login Icon Issue
 * 
 * Add this to your WordPress site temporarily to debug the icon issue.
 * Access via: yoursite.com/debug-sso-icon.php
 * 
 * REMOVE THIS FILE AFTER DEBUGGING!
 */

// Load WordPress
require_once('wp-config.php');

echo "<h1>SSO Client Login Icon Debug Report</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; } .error { color: red; } .success { color: green; } .warning { color: orange; } pre { background: #eee; padding: 10px; overflow-x: auto; }</style>";

// 1. SETTINGS VERIFICATION
echo "<div class='debug-section'>";
echo "<h2>1. Settings Verification</h2>";

$sso_client_options = get_option('discourse_sso_client', array());
echo "<h3>Raw discourse_sso_client options:</h3>";
echo "<pre>" . print_r($sso_client_options, true) . "</pre>";

// Check specific icon settings
$icon_enabled = isset($sso_client_options['sso-client-login-icon-enabled']) ? $sso_client_options['sso-client-login-icon-enabled'] : 'NOT SET';
$icon_id = isset($sso_client_options['sso-client-login-icon-id']) ? $sso_client_options['sso-client-login-icon-id'] : 'NOT SET';
$icon_size = isset($sso_client_options['sso-client-login-icon-size']) ? $sso_client_options['sso-client-login-icon-size'] : 'NOT SET';

echo "<h3>Icon-specific settings:</h3>";
echo "<ul>";
echo "<li><strong>sso-client-login-icon-enabled:</strong> " . ($icon_enabled ? '<span class="success">✓ ' . $icon_enabled . '</span>' : '<span class="error">✗ ' . $icon_enabled . '</span>') . "</li>";
echo "<li><strong>sso-client-login-icon-id:</strong> " . ($icon_id !== 'NOT SET' && $icon_id ? '<span class="success">✓ ' . $icon_id . '</span>' : '<span class="error">✗ ' . $icon_id . '</span>') . "</li>";
echo "<li><strong>sso-client-login-icon-size:</strong> " . ($icon_size !== 'NOT SET' ? '<span class="success">✓ ' . $icon_size . '</span>' : '<span class="warning">⚠ ' . $icon_size . ' (will use default 24)</span>') . "</li>";
echo "</ul>";

// Check if both required settings are present
$icon_should_show = !empty($sso_client_options['sso-client-login-icon-enabled']) && !empty($sso_client_options['sso-client-login-icon-id']);
echo "<p><strong>Icon should show based on settings:</strong> " . ($icon_should_show ? '<span class="success">✓ YES</span>' : '<span class="error">✗ NO</span>') . "</p>";
echo "</div>";

// 2. FILE EXISTENCE CHECKS
echo "<div class='debug-section'>";
echo "<h2>2. File Existence Checks</h2>";

if ($icon_id > 0) {
    $attachment_exists = get_post($icon_id);
    echo "<h3>Attachment Post Data:</h3>";
    if ($attachment_exists) {
        echo "<span class='success'>✓ Attachment post exists</span><br>";
        echo "<strong>Post Type:</strong> " . $attachment_exists->post_type . "<br>";
        echo "<strong>Post Status:</strong> " . $attachment_exists->post_status . "<br>";
        echo "<strong>MIME Type:</strong> " . $attachment_exists->post_mime_type . "<br>";
        echo "<strong>Title:</strong> " . $attachment_exists->post_title . "<br>";
        
        // Check if it's actually an image
        if (strpos($attachment_exists->post_mime_type, 'image/') === 0) {
            echo "<span class='success'>✓ Is an image file</span><br>";
        } else {
            echo "<span class='error'>✗ Not an image file</span><br>";
        }
    } else {
        echo "<span class='error'>✗ Attachment post does not exist</span><br>";
    }
    
    // Check file system
    $file_path = get_attached_file($icon_id);
    echo "<h3>File System Check:</h3>";
    echo "<strong>File Path:</strong> " . ($file_path ? $file_path : 'NOT FOUND') . "<br>";
    if ($file_path && file_exists($file_path)) {
        echo "<span class='success'>✓ File exists on disk</span><br>";
        echo "<strong>File Size:</strong> " . filesize($file_path) . " bytes<br>";
    } else {
        echo "<span class='error'>✗ File does not exist on disk</span><br>";
    }
} else {
    echo "<span class='warning'>⚠ No icon ID to check</span>";
}
echo "</div>";

// 3. WORDPRESS FUNCTION TESTING
echo "<div class='debug-section'>";
echo "<h2>3. WordPress Function Testing</h2>";

if ($icon_id !== 'NOT SET' && $icon_id) {
    echo "<h3>wp_get_attachment_image_url() Test:</h3>";
    $image_url_full = wp_get_attachment_image_url($icon_id, 'full');
    $image_url_thumb = wp_get_attachment_image_url($icon_id, 'thumbnail');
    
    echo "<strong>Full size URL:</strong> " . ($image_url_full ? '<span class="success">✓ ' . $image_url_full . '</span>' : '<span class="error">✗ Failed</span>') . "<br>";
    echo "<strong>Thumbnail URL:</strong> " . ($image_url_thumb ? '<span class="success">✓ ' . $image_url_thumb . '</span>' : '<span class="error">✗ Failed</span>') . "<br>";
    
    echo "<h3>wp_get_attachment_image() Test:</h3>";
    $test_size = $icon_size !== 'NOT SET' ? max(8, min(256, (int)$icon_size)) : 24;
    $image_html = wp_get_attachment_image(
        $icon_id,
        array($test_size, $test_size),
        false,
        array(
            'class' => 'wpdc-sso-client-login-icon',
            'alt' => '',
            'aria-hidden' => 'true',
            'role' => 'presentation',
            'decoding' => 'async',
            'style' => 'vertical-align: middle; margin-right: 8px;',
        )
    );
    
    echo "<strong>Generated HTML:</strong><br>";
    if ($image_html) {
        echo "<span class='success'>✓ HTML generated successfully</span><br>";
        echo "<pre>" . htmlspecialchars($image_html) . "</pre>";
        echo "<strong>Rendered preview:</strong><br>";
        echo $image_html . " Sample text";
    } else {
        echo "<span class='error'>✗ No HTML generated</span><br>";
    }
} else {
    echo "<span class='warning'>⚠ No icon ID to test</span>";
}
echo "</div>";

// 4. CODE PATH TRACING
echo "<div class='debug-section'>";
echo "<h2>4. Code Path Tracing</h2>";

echo "<h3>Simulating SSOClientBase::get_discourse_sso_link_markup():</h3>";

// Load the SSO Client class if available
if (class_exists('WPDiscourse\SSOClient\SSOClientBase')) {
    echo "<span class='success'>✓ SSOClientBase class is available</span><br>";
    
    // Try to create an instance and test the method
    try {
        $reflection = new ReflectionClass('WPDiscourse\SSOClient\SSOClientBase');
        if ($reflection->hasMethod('get_discourse_sso_link_markup')) {
            echo "<span class='success'>✓ get_discourse_sso_link_markup method exists</span><br>";
            
            // Test the actual method call
            $sso_client = new WPDiscourse\SSOClient\SSOClientBase();
            $test_markup = $sso_client->get_discourse_sso_link_markup(array());
            
            echo "<strong>Generated markup:</strong><br>";
            echo "<pre>" . htmlspecialchars($test_markup) . "</pre>";
            echo "<strong>Rendered preview:</strong><br>";
            echo $test_markup;
            
        } else {
            echo "<span class='error'>✗ get_discourse_sso_link_markup method not found</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error testing method: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span class='error'>✗ SSOClientBase class not available</span><br>";
}
echo "</div>";

// 5. ADDITIONAL DIAGNOSTICS
echo "<div class='debug-section'>";
echo "<h2>5. Additional Diagnostics</h2>";

echo "<h3>WordPress Environment:</h3>";
echo "<strong>WordPress Version:</strong> " . get_bloginfo('version') . "<br>";
echo "<strong>Active Theme:</strong> " . wp_get_theme()->get('Name') . "<br>";
echo "<strong>WP_DEBUG:</strong> " . (defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled') . "<br>";

echo "<h3>Plugin Status:</h3>";
if (function_exists('is_plugin_active')) {
    $wp_discourse_active = is_plugin_active('wp-discourse/wp-discourse.php');
    echo "<strong>WP Discourse Plugin:</strong> " . ($wp_discourse_active ? '<span class="success">✓ Active</span>' : '<span class="error">✗ Inactive</span>') . "<br>";
}

echo "<h3>Current User Context:</h3>";
$current_user = wp_get_current_user();
echo "<strong>User ID:</strong> " . $current_user->ID . "<br>";
echo "<strong>User Role:</strong> " . implode(', ', $current_user->roles) . "<br>";

echo "</div>";

echo "<div class='debug-section'>";
echo "<h2>Next Steps</h2>";
echo "<p>Based on the results above:</p>";
echo "<ol>";
echo "<li>If settings show as 'NOT SET' or incorrect, check the admin settings page</li>";
echo "<li>If attachment doesn't exist, re-upload the icon in WordPress admin</li>";
echo "<li>If wp_get_attachment_image() fails, check file permissions and WordPress media settings</li>";
echo "<li>If the method generates correct HTML but it's not showing on frontend, check CSS or theme conflicts</li>";
echo "<li>If SSOClientBase class is not available, check plugin activation and file paths</li>";
echo "</ol>";
echo "<p><strong>Remember to delete this debug file after use!</strong></p>";
echo "</div>";
?>
