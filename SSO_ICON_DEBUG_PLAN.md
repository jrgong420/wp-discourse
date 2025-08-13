# SSO Client Login Icon Debugging Plan

## Problem Statement
The SSO client login link is rendering without the expected icon, despite having configured an icon in the settings. The HTML output shows only the text link without any icon markup.

**Expected:** `<a href="..."><img class="wpdc-sso-client-login-icon" src="..." />Log in with Discourse</a>`
**Actual:** `<a href="...">Log in with Discourse</a>`

## Debugging Tools Created

### 1. `debug-sso-icon.php` - Standalone Debug Script
- **Usage:** Place in WordPress root, access via `yoursite.com/debug-sso-icon.php`
- **Purpose:** Comprehensive analysis of settings, file existence, and WordPress functions
- **⚠️ SECURITY:** Remove after debugging - contains sensitive information

### 2. `debug-sso-icon-admin.php` - Admin Debug Helper
- **Usage:** Add to theme's `functions.php` or create as mu-plugin
- **Purpose:** Shows debug info in WordPress admin on Discourse settings pages
- **Features:** Admin notices with real-time testing, debug shortcode `[debug_sso_icon]`

### 3. Enhanced Logging in `sso-client-base.php`
- **Purpose:** Detailed error_log output during icon generation
- **Activation:** Requires `WP_DEBUG` to be enabled
- **Location:** Check WordPress debug.log file

## Step-by-Step Debugging Process

### Phase 1: Settings Verification
1. **Check WordPress Admin**
   - Navigate to Discourse → SSO Settings
   - Verify "Enable Login Link Icon" is checked
   - Confirm an icon is selected and shows preview
   - Note the attachment ID from the hidden input

2. **Database Verification**
   - Run `debug-sso-icon.php` to see raw option values
   - Confirm `sso-client-login-icon-enabled` = 1
   - Confirm `sso-client-login-icon-id` has valid attachment ID
   - Check `sso-client-login-icon-size` (optional, defaults to 24)

3. **Expected Results**
   - Both enabled and ID settings should be present and non-empty
   - If missing: Re-save settings in WordPress admin

### Phase 2: File Existence Checks
1. **Attachment Post Verification**
   - Use debug script to check if attachment post exists
   - Verify post_type = 'attachment'
   - Verify post_status = 'inherit'
   - Verify post_mime_type starts with 'image/'

2. **File System Verification**
   - Check if file exists on disk using `get_attached_file()`
   - Verify file permissions are readable
   - Check file size > 0 bytes

3. **Expected Results**
   - Attachment should exist in database and on filesystem
   - If missing: Re-upload icon in WordPress admin

### Phase 3: WordPress Function Testing
1. **URL Generation Test**
   - Test `wp_get_attachment_image_url($icon_id, 'full')`
   - Test `wp_get_attachment_image_url($icon_id, 'thumbnail')`
   - Both should return valid URLs

2. **HTML Generation Test**
   - Test `wp_get_attachment_image()` with same parameters as code
   - Should return complete `<img>` tag with all attributes
   - Check for proper class, size, and accessibility attributes

3. **Expected Results**
   - Functions should return valid URLs and HTML
   - If failing: Check WordPress media settings, file permissions

### Phase 4: Code Path Tracing
1. **Class Availability**
   - Verify `WPDiscourse\SSOClient\SSOClientBase` class exists
   - Check if `get_discourse_sso_link_markup()` method is available

2. **Method Execution**
   - Use debug shortcode `[debug_sso_icon]` on frontend
   - Check error_log for debug messages (requires WP_DEBUG)
   - Trace through icon generation logic

3. **Option Retrieval**
   - Verify `$this->options` contains correct values in SSOClientBase
   - Check if options are being retrieved from correct source

4. **Expected Results**
   - Method should execute without errors
   - Debug logs should show icon HTML being generated
   - If failing: Check plugin activation, class loading

### Phase 5: Error Logging Analysis
1. **Enable WordPress Debug Logging**
   ```php
   // In wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **Check Debug Log**
   - Location: `/wp-content/debug.log`
   - Look for "WP Discourse SSO Icon Debug" messages
   - Analyze each step of icon generation process

3. **Key Debug Messages to Look For**
   - Options array contents
   - Icon enabled/ID check results
   - Attachment existence verification
   - Generated HTML length and content
   - Final button HTML output

### Phase 6: Frontend Inspection
1. **HTML Source Inspection**
   - View page source where SSO link appears
   - Search for `wpdc-sso-client-login-icon` class
   - Check if icon HTML is present but hidden

2. **CSS Conflict Check**
   - Use browser dev tools to inspect SSO link
   - Check for CSS rules hiding the icon
   - Look for `display: none`, `visibility: hidden`, etc.
   - Check for theme-specific CSS overrides

3. **JavaScript Interference**
   - Check browser console for JavaScript errors
   - Look for scripts that might modify the SSO link after page load
   - Test with JavaScript disabled

## Common Issues and Solutions

### Issue 1: Settings Not Saved
**Symptoms:** Debug shows settings as 'NOT SET'
**Solution:** Re-save Discourse SSO settings in WordPress admin

### Issue 2: Attachment Deleted
**Symptoms:** Attachment ID exists but post/file not found
**Solution:** Re-upload icon in WordPress admin

### Issue 3: File Permission Issues
**Symptoms:** Attachment exists but wp_get_attachment_image() fails
**Solution:** Check file permissions, WordPress upload directory settings

### Issue 4: CSS Hiding Icon
**Symptoms:** HTML generated correctly but icon not visible
**Solution:** Check theme CSS, add custom CSS to show icon

### Issue 5: Plugin Conflict
**Symptoms:** Class not found or method errors
**Solution:** Check plugin activation order, deactivate other plugins for testing

### Issue 6: Caching Issues
**Symptoms:** Changes not reflected on frontend
**Solution:** Clear all caches (plugin, server, CDN)

## Cleanup After Debugging

1. **Remove Debug Files**
   - Delete `debug-sso-icon.php`
   - Remove debug code from `functions.php`

2. **Remove Debug Logging**
   - Remove debug code from `sso-client-base.php`
   - Or set WP_DEBUG to false

3. **Document Solution**
   - Record what fixed the issue
   - Update any relevant documentation

## Emergency Fallback

If icon still doesn't work after debugging:

1. **Manual CSS Solution**
   ```css
   .wpdc-sso-client-login-link::before {
       content: '';
       display: inline-block;
       width: 24px;
       height: 24px;
       background-image: url('path-to-your-icon.png');
       background-size: contain;
       margin-right: 8px;
       vertical-align: middle;
   }
   ```

2. **Filter Hook Solution**
   ```php
   add_filter('wpdc_sso_client_login_button', function($button, $url, $options) {
       // Manually inject icon HTML
       $icon_html = '<img src="path-to-icon.png" class="wpdc-sso-client-login-icon" alt="" style="width:24px;height:24px;margin-right:8px;vertical-align:middle;">';
       return str_replace('>', '>' . $icon_html, $button);
   }, 10, 3);
   ```
