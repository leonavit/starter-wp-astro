<?php
/**
 * Theme Options — Contact section (phone, email, WhatsApp, address, hours).
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Contact settings.
 *
 * @return array<string, mixed>
 */
function wpas_default_contact_settings() {
	return array(
		'phone'             => '+972500000000',
		'phone_display'     => '050-000-0000',
		'whatsapp'          => '972500000000',
		'whatsapp_message'  => __( 'שלום, הגעתי דרך האתר ואשמח לקבל פרטים', 'wpas' ),
		'email'             => 'hello@example.com',
		'address_street'    => __( 'רחוב הרצל 1', 'wpas' ),
		'address_city'      => __( 'תל אביב', 'wpas' ),
		'address_zip'       => '6100000',
		'address_country'   => 'IL',
		'hours'             => array(
			array( 'days' => 'א׳–ה׳', 'time' => '09:00 – 18:00' ),
			array( 'days' => 'ו׳',     'time' => '09:00 – 13:00' ),
		),
	);
}

/**
 * Merged Contact settings.
 *
 * @return array<string, mixed>
 */
function wpas_get_contact_settings() {
	$options = wpas_get_theme_options();
	return $options['contact'];
}

/**
 * Sanitize Contact settings on save.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function wpas_sanitize_contact_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = wpas_default_contact_settings();

	$clean = array(
		'phone'            => isset( $input['phone'] ) ? sanitize_text_field( wp_unslash( $input['phone'] ) ) : $defaults['phone'],
		'phone_display'    => isset( $input['phone_display'] ) ? sanitize_text_field( wp_unslash( $input['phone_display'] ) ) : $defaults['phone_display'],
		'whatsapp'         => isset( $input['whatsapp'] ) ? preg_replace( '/\D/', '', wp_unslash( $input['whatsapp'] ) ) : $defaults['whatsapp'],
		'whatsapp_message' => isset( $input['whatsapp_message'] ) ? sanitize_text_field( wp_unslash( $input['whatsapp_message'] ) ) : $defaults['whatsapp_message'],
		'email'            => isset( $input['email'] ) ? sanitize_email( wp_unslash( $input['email'] ) ) : $defaults['email'],
		'address_street'   => isset( $input['address_street'] ) ? sanitize_text_field( wp_unslash( $input['address_street'] ) ) : $defaults['address_street'],
		'address_city'     => isset( $input['address_city'] ) ? sanitize_text_field( wp_unslash( $input['address_city'] ) ) : $defaults['address_city'],
		'address_zip'      => isset( $input['address_zip'] ) ? sanitize_text_field( wp_unslash( $input['address_zip'] ) ) : $defaults['address_zip'],
		'address_country'  => isset( $input['address_country'] ) ? sanitize_text_field( wp_unslash( $input['address_country'] ) ) : $defaults['address_country'],
		'hours'            => array(),
	);

	$hours_input = isset( $input['hours'] ) && is_array( $input['hours'] ) ? $input['hours'] : array();
	foreach ( $hours_input as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$days = isset( $row['days'] ) ? sanitize_text_field( wp_unslash( $row['days'] ) ) : '';
		$time = isset( $row['time'] ) ? sanitize_text_field( wp_unslash( $row['time'] ) ) : '';
		if ( '' === $days && '' === $time ) {
			continue;
		}
		$clean['hours'][] = array( 'days' => $days, 'time' => $time );
	}
	if ( empty( $clean['hours'] ) ) {
		$clean['hours'] = $defaults['hours'];
	}

	return $clean;
}

/**
 * Render the Contact section.
 *
 * @param string $key Option key.
 */
function wpas_render_section_contact( $key ) {
	$c = wpas_get_contact_settings();
	wpas_section_start(
		array(
			'id'          => 'contact',
			'title'       => __( 'פרטי יצירת קשר', 'wpas' ),
			'description' => __( 'הערכים האלה מוזרמים ל-Header, ל-Footer, לכפתור הוואטסאפ הצף ול-Schema.org LocalBusiness.', 'wpas' ),
		)
	);
	?>
	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_text(
			array(
				'label'       => __( 'טלפון (פורמט בינלאומי, ל-tel:)', 'wpas' ),
				'name'        => $key . '[contact][phone]',
				'value'       => $c['phone'],
				'placeholder' => '+972500000000',
				'dir'         => 'ltr',
				'help'        => __( 'מתחיל ב-+972 ובלי 0 ראשי.', 'wpas' ),
			)
		);
		wpas_field_text(
			array(
				'label'       => __( 'טלפון לתצוגה לאדם', 'wpas' ),
				'name'        => $key . '[contact][phone_display]',
				'value'       => $c['phone_display'],
				'placeholder' => '050-000-0000',
				'dir'         => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label'       => __( 'מספר וואטסאפ (ללא +, לפורמט wa.me)', 'wpas' ),
				'name'        => $key . '[contact][whatsapp]',
				'value'       => $c['whatsapp'],
				'placeholder' => '972500000000',
				'dir'         => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'הודעה מוכנה לוואטסאפ', 'wpas' ),
				'name'  => $key . '[contact][whatsapp_message]',
				'value' => $c['whatsapp_message'],
				'help'  => __( 'הטקסט שיופיע אוטומטית בפתיחת השיחה.', 'wpas' ),
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'אימייל', 'wpas' ),
				'name'  => $key . '[contact][email]',
				'value' => $c['email'],
				'type'  => 'email',
				'dir'   => 'ltr',
			)
		);
		?>
	</div>

	<h3 class="wpas-subheading"><?php esc_html_e( 'כתובת פיזית', 'wpas' ); ?></h3>
	<div class="wpas-grid wpas-grid--2">
		<?php
		wpas_field_text(
			array(
				'label' => __( 'רחוב ומספר', 'wpas' ),
				'name'  => $key . '[contact][address_street]',
				'value' => $c['address_street'],
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'עיר', 'wpas' ),
				'name'  => $key . '[contact][address_city]',
				'value' => $c['address_city'],
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'מיקוד', 'wpas' ),
				'name'  => $key . '[contact][address_zip]',
				'value' => $c['address_zip'],
				'dir'   => 'ltr',
			)
		);
		wpas_field_text(
			array(
				'label' => __( 'קוד מדינה (ISO 3166-1)', 'wpas' ),
				'name'  => $key . '[contact][address_country]',
				'value' => $c['address_country'],
				'dir'   => 'ltr',
				'help'  => __( 'IL לישראל, US לארה״ב וכו׳.', 'wpas' ),
			)
		);
		?>
	</div>

	<h3 class="wpas-subheading"><?php esc_html_e( 'שעות פתיחה', 'wpas' ); ?></h3>
	<?php wpas_field_hours( $key . '[contact][hours]', $c['hours'] ); ?>
	<?php
	wpas_section_end();
}
