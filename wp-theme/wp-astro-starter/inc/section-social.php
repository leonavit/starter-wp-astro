<?php
/**
 * Theme Options — Social section.
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Social settings.
 *
 * @return array<string, string>
 */
function wpas_default_social_settings() {
	return array(
		'facebook'  => '',
		'instagram' => '',
		'linkedin'  => '',
		'youtube'   => '',
		'tiktok'    => '',
		'x'         => '',
	);
}

/**
 * Merged Social settings.
 *
 * @return array<string, string>
 */
function wpas_get_social_settings() {
	$options = wpas_get_theme_options();
	return $options['social'];
}

/**
 * Sanitize Social settings on save.
 *
 * @param mixed $input Raw input.
 * @return array<string, string>
 */
function wpas_sanitize_social_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = wpas_default_social_settings();
	$clean    = array();
	foreach ( $defaults as $key => $fallback ) {
		$clean[ $key ] = isset( $input[ $key ] )
			? esc_url_raw( wp_unslash( $input[ $key ] ) )
			: $fallback;
	}
	return $clean;
}

/**
 * Render the Social section.
 *
 * @param string $key Option key.
 */
function wpas_render_section_social( $key ) {
	$s = wpas_get_social_settings();
	wpas_section_start(
		array(
			'id'          => 'social',
			'title'       => __( 'רשתות חברתיות', 'wpas' ),
			'description' => __( 'הזינו URL מלא לפרופיל. שדה ריק יוסתר אוטומטית מה-Footer.', 'wpas' ),
		)
	);
	?>
	<div class="wpas-grid wpas-grid--2">
		<?php
		$labels = array(
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'linkedin'  => 'LinkedIn',
			'youtube'   => 'YouTube',
			'tiktok'    => 'TikTok',
			'x'         => 'X (Twitter)',
		);
		foreach ( $labels as $field => $label ) {
			wpas_field_text(
				array(
					'label'       => $label,
					'name'        => $key . '[social][' . $field . ']',
					'value'       => $s[ $field ] ?? '',
					'type'        => 'url',
					'dir'         => 'ltr',
					'placeholder' => 'https://...',
				)
			);
		}
		?>
	</div>
	<?php
	wpas_section_end();
}
