<?php
/**
 * Theme Options — General section (brand identity).
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default General settings.
 *
 * @return array<string, mixed>
 */
function wpas_default_general_settings() {
	return array(
		'name'         => get_bloginfo( 'name' ) ?: __( 'החברה שלכם', 'wpas' ),
		'legal_name'   => __( 'החברה שלכם בע״מ', 'wpas' ),
		'tagline'      => get_bloginfo( 'description' ) ?: __( 'הסבר קצר על העסק', 'wpas' ),
		'description'  => __( 'תיאור מורחב המופיע ב-meta description של דפים שאין להם תיאור משלהם.', 'wpas' ),
		'founding_year' => (int) gmdate( 'Y' ),
		'theme_color'  => '#1d4ed8',
		'logo'         => array( 'attachment_id' => 0, 'url' => '' ),
		'og_image'     => array( 'attachment_id' => 0, 'url' => '' ),
	);
}

/**
 * Merged settings for the General section.
 *
 * @return array<string, mixed>
 */
function wpas_get_general_settings() {
	$options = wpas_get_theme_options();
	return $options['general'];
}

/**
 * Sanitize General settings on save.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function wpas_sanitize_general_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = wpas_default_general_settings();

	return array(
		'name'          => isset( $input['name'] ) ? sanitize_text_field( wp_unslash( $input['name'] ) ) : $defaults['name'],
		'legal_name'    => isset( $input['legal_name'] ) ? sanitize_text_field( wp_unslash( $input['legal_name'] ) ) : $defaults['legal_name'],
		'tagline'       => isset( $input['tagline'] ) ? sanitize_text_field( wp_unslash( $input['tagline'] ) ) : $defaults['tagline'],
		'description'   => isset( $input['description'] ) ? sanitize_textarea_field( wp_unslash( $input['description'] ) ) : $defaults['description'],
		'founding_year' => isset( $input['founding_year'] ) ? absint( $input['founding_year'] ) : $defaults['founding_year'],
		'theme_color'   => isset( $input['theme_color'] ) ? sanitize_hex_color( $input['theme_color'] ) : $defaults['theme_color'],
		'logo'          => wpas_sanitize_media_row( $input['logo'] ?? array() ),
		'og_image'      => wpas_sanitize_media_row( $input['og_image'] ?? array() ),
	);
}

/**
 * Shared sanitizer for {attachment_id, url} rows.
 *
 * @param mixed $row Raw row.
 * @return array{attachment_id:int,url:string}
 */
function wpas_sanitize_media_row( $row ) {
	$row = is_array( $row ) ? $row : array();
	return array(
		'attachment_id' => isset( $row['attachment_id'] ) ? absint( $row['attachment_id'] ) : 0,
		'url'           => isset( $row['url'] ) ? esc_url_raw( wp_unslash( $row['url'] ) ) : '',
	);
}

/**
 * Render the General section.
 *
 * @param string $key Option key.
 */
function wpas_render_section_general( $key ) {
	$g = wpas_get_general_settings();
	wpas_section_start(
		array(
			'id'          => 'general',
			'title'       => __( 'זהות העסק', 'wpas' ),
			'description' => __( 'שם, תיאור, לוגו וצבע מותג. הערכים האלה משמשים גם ב-Schema.org וב-OpenGraph של כל הדפים.', 'wpas' ),
		)
	);
	?>
	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_text(
			array(
				'label' => __( 'שם תצוגה (Header / Footer)', 'wpas' ),
				'name'  => $key . '[general][name]',
				'value' => $g['name'],
				'help'  => __( 'כפי שיוצג בלוגו ובכותרות הדפים.', 'wpas' ),
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'שם משפטי (לתחתית הדף ול-Schema)', 'wpas' ),
				'name'  => $key . '[general][legal_name]',
				'value' => $g['legal_name'],
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'סלוגן קצר', 'wpas' ),
				'name'  => $key . '[general][tagline]',
				'value' => $g['tagline'],
				'help'  => __( 'מופיע מתחת ללוגו ובחלק מהמטא תגיות.', 'wpas' ),
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'שנת ייסוד', 'wpas' ),
				'name'  => $key . '[general][founding_year]',
				'value' => (string) $g['founding_year'],
				'type'  => 'number',
				'dir'   => 'ltr',
			)
		);
		?>
	</div>

	<?php
	wpas_field_textarea(
		array(
			'label' => __( 'תיאור מורחב לברירת מחדל של SEO', 'wpas' ),
			'name'  => $key . '[general][description]',
			'value' => $g['description'],
			'rows'  => 3,
			'help'  => __( 'משמש כ-meta description עבור דפים שלא הוגדר להם תיאור משלהם.', 'wpas' ),
		)
	);
	?>

	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_text(
			array(
				'label' => __( 'צבע מותג (Theme color)', 'wpas' ),
				'name'  => $key . '[general][theme_color]',
				'value' => $g['theme_color'],
				'type'  => 'color',
				'dir'   => 'ltr',
				'help'  => __( 'משמש לכפתורים, ל-meta theme-color ולרכיבי UI מרכזיים.', 'wpas' ),
			)
		);
		?>
	</div>

	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_media(
			array(
				'label'       => __( 'לוגו', 'wpas' ),
				'name_prefix' => $key . '[general][logo]',
				'item'        => $g['logo'],
				'media_type'  => 'image',
			)
		);
		wpas_field_media(
			array(
				'label'       => __( 'תמונת OpenGraph ברירת מחדל (1200×630)', 'wpas' ),
				'name_prefix' => $key . '[general][og_image]',
				'item'        => $g['og_image'],
				'media_type'  => 'image',
			)
		);
		?>
	</div>
	<?php
	wpas_section_end();
}
