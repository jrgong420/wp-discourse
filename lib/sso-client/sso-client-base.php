<?php
/**
 * SSO Client base class.
 *
 * @package WPDiscourse.
 */

namespace WPDiscourse\SSOClient;

use WPDiscourse\DiscourseBase;

/**
 * Class SSOClientBase
 */
class SSOClientBase extends DiscourseBase {
	/**
	 * Generates the markup for SSO link
	 *
	 * @method get_discourse_sso_link_markup
	 *
	 * @param  array $link_options link, login, redirect.
	 *
	 * @return string
	 */
	protected function get_discourse_sso_link_markup( $link_options = array() ) {
		$options = isset( $this->options ) ? $this->options : $this->get_options();
		$user_id = get_current_user_id();

		if ( ! empty( $user_id ) ) {
			if ( get_user_meta( $user_id, 'discourse_sso_user_id', true ) ) {

				return null;
			}
			$link_account_text = ! empty( self::get_text_options( 'link-to-discourse-text' ) ) ? self::get_text_options( 'link-to-discourse-text' ) : '';
			$anchor            = ! empty( $link_options['link'] ) ? $link_options['link'] : $link_account_text;
		} else {
			$login_text = ! empty( self::get_text_options( 'external-login-text' ) ) ? self::get_text_options( 'external-login-text' ) : '';
			$anchor     = ! empty( $link_options['login'] ) ? $link_options['login'] : $login_text;
		}

		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect_to = wp_validate_redirect(
				esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ),
				null
			);
		} elseif ( ! empty( $link_options['redirect'] ) ) {
			$redirect_to = $link_options['redirect'];
		} else {
			$redirect_to = null;
		}
		$sso_login_url = $this->get_discourse_sso_url( $redirect_to );

		$anchor = apply_filters( 'wpdc_sso_client_login_anchor', $anchor );

		// Build icon HTML if enabled
		$icon_html = '';

		// DEBUG: Log icon processing
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP Discourse SSO Icon Debug - Options: ' . print_r( $options, true ) );
			error_log( 'WP Discourse SSO Icon Debug - Icon enabled check: ' . ( ! empty( $options['sso-client-login-icon-enabled'] ) ? 'YES' : 'NO' ) );
			error_log( 'WP Discourse SSO Icon Debug - Icon ID check: ' . ( ! empty( $options['sso-client-login-icon-id'] ) ? 'YES (' . $options['sso-client-login-icon-id'] . ')' : 'NO' ) );
		}

		if ( ! empty( $options['sso-client-login-icon-enabled'] ) && ! empty( $options['sso-client-login-icon-id'] ) ) {
			$icon_id   = (int) $options['sso-client-login-icon-id'];
			$icon_size = isset( $options['sso-client-login-icon-size'] )
				? max( 8, min( 256, (int) $options['sso-client-login-icon-size'] ) )
				: 24;

			// DEBUG: Log icon generation attempt
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WP Discourse SSO Icon Debug - Attempting to generate icon HTML for ID: ' . $icon_id . ', Size: ' . $icon_size );

				// Test if attachment exists
				$attachment_exists = get_post( $icon_id );
				error_log( 'WP Discourse SSO Icon Debug - Attachment exists: ' . ( $attachment_exists ? 'YES' : 'NO' ) );

				if ( $attachment_exists ) {
					error_log( 'WP Discourse SSO Icon Debug - Attachment type: ' . $attachment_exists->post_mime_type );
					$file_path = get_attached_file( $icon_id );
					error_log( 'WP Discourse SSO Icon Debug - File path: ' . $file_path );
					error_log( 'WP Discourse SSO Icon Debug - File exists: ' . ( file_exists( $file_path ) ? 'YES' : 'NO' ) );
				}
			}

			$icon_html = wp_get_attachment_image(
				$icon_id,
				array( $icon_size, $icon_size ),
				false,
				array(
					'class'       => 'wpdc-sso-client-login-icon',
					'alt'         => '',
					'aria-hidden' => 'true',
					'role'        => 'presentation',
					'decoding'    => 'async',
					// Fallback spacing; themes can override via the class.
					'style'       => 'vertical-align: middle; margin-right: 8px;',
				)
			) ?: '';

			// DEBUG: Log icon generation result
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WP Discourse SSO Icon Debug - Generated icon HTML length: ' . strlen( $icon_html ) );
				error_log( 'WP Discourse SSO Icon Debug - Generated icon HTML: ' . $icon_html );
			}
		}

		// Build the complete anchor content with icon and text
		$anchor_content = $icon_html . sanitize_text_field( $anchor );

		// DEBUG: Log final content
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP Discourse SSO Icon Debug - Final anchor content: ' . $anchor_content );
			error_log( 'WP Discourse SSO Icon Debug - Icon HTML length in final: ' . strlen( $icon_html ) );
		}

		// Create the button with hardcoded wp-discourse-link class
		$button = sprintf(
			'<a class="wpdc-sso-client-login-link wp-discourse-link" href="%s">%s</a>',
			esc_url( $sso_login_url ),
			$anchor_content
		);

		// DEBUG: Log final button HTML
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP Discourse SSO Icon Debug - Final button HTML: ' . $button );
		}

		return apply_filters( 'wpdc_sso_client_login_button', $button, $sso_login_url, $link_options );
	}

	/**
	 * Gets the auth URL for discourse.
	 *
	 * @param string|null $redirect The URL to redirect to.
	 *
	 * @return string
	 */
	protected function get_discourse_sso_url( $redirect = null ) {
		$is_user_logged_in = is_user_logged_in();

		$redirect_to = $redirect ? $redirect : get_permalink();

		if ( empty( $redirect_to ) ) {
			$redirect_to = $is_user_logged_in ? admin_url( 'profile.php' ) : home_url( '/' );
		}

		return add_query_arg(
			array(
				'discourse_sso' => sanitize_key( apply_filters( 'wpdc_sso_client_query', 1 ) ),
				'redirect_to'   => apply_filters(
					'wpdc_sso_client_redirect_url',
					urlencode( esc_url_raw( $redirect_to ) ),
					$redirect_to
				),
			),
			home_url( '/' )
		);
	}
}
