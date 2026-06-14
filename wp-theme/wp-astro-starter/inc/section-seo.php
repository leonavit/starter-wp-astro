<?php
/**
 * Theme Options — SEO & analytics section.
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default SEO settings.
 *
 * @return array<string, string>
 */
function wpas_default_seo_settings() {
	return array(
		'ga4_id'           => '',
		'gtm_id'           => '',
		'facebook_pixel'   => '',
		'verification_google' => '',
	);
}

/**
 * Merged SEO settings.
 *
 * @return array<string, string>
 */
function wpas_get_seo_settings() {
	$options = wpas_get_theme_options();
	return $options['seo'];
}

/**
 * Sanitize SEO settings on save.
 *
 * @param mixed $input Raw input.
 * @return array<string, string>
 */
function wpas_sanitize_seo_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = wpas_default_seo_settings();
	return array(
		'ga4_id'              => isset( $input['ga4_id'] ) ? sanitize_text_field( wp_unslash( $input['ga4_id'] ) ) : $defaults['ga4_id'],
		'gtm_id'              => isset( $input['gtm_id'] ) ? sanitize_text_field( wp_unslash( $input['gtm_id'] ) ) : $defaults['gtm_id'],
		'facebook_pixel'      => isset( $input['facebook_pixel'] ) ? preg_replace( '/\D/', '', wp_unslash( $input['facebook_pixel'] ) ) : $defaults['facebook_pixel'],
		'verification_google' => isset( $input['verification_google'] ) ? sanitize_text_field( wp_unslash( $input['verification_google'] ) ) : $defaults['verification_google'],
	);
}

/**
 * Render the SEO section.
 *
 * @param string $key Option key.
 */
function wpas_render_section_seo( $key ) {
	$seo = wpas_get_seo_settings();
	wpas_section_start(
		array(
			'id'          => 'seo',
			'title'       => __( 'SEO ומדידה', 'wpas' ),
			'description' => __( 'מזהי מדידה ואימותים. השאירו ריק כדי לא להפעיל.', 'wpas' ),
		)
	);
	?>
	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_text(
			array(
				'label'       => __( 'Google Analytics 4 — Measurement ID', 'wpas' ),
				'name'        => $key . '[seo][ga4_id]',
				'value'       => $seo['ga4_id'],
				'placeholder' => 'G-XXXXXXX',
				'dir'         => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label'       => __( 'Google Tag Manager — Container ID', 'wpas' ),
				'name'        => $key . '[seo][gtm_id]',
				'value'       => $seo['gtm_id'],
				'placeholder' => 'GTM-XXXXXX',
				'dir'         => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label'       => __( 'Meta (Facebook) Pixel ID', 'wpas' ),
				'name'        => $key . '[seo][facebook_pixel]',
				'value'       => $seo['facebook_pixel'],
				'placeholder' => '000000000000000',
				'dir'         => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label'       => __( 'Google Search Console — verification token', 'wpas' ),
				'name'        => $key . '[seo][verification_google]',
				'value'       => $seo['verification_google'],
				'placeholder' => 'XXXXXXXXXXXXXXXXXXXXXXXXXX',
				'dir'         => 'ltr',
			)
		);
		?>
	</div>
	<?php
	wpas_section_end();
}
