<?php
/**
 * Shared helpers for Theme Options sections.
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the full theme options array, with section defaults merged in.
 *
 * @return array<string, mixed>
 */
function wpas_get_theme_options() {
	$stored = get_option( WPAS_OPTION_KEY, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$defaults = array(
		'general' => wpas_default_general_settings(),
		'contact' => wpas_default_contact_settings(),
		'social'  => wpas_default_social_settings(),
		'seo'     => wpas_default_seo_settings(),
	);

	foreach ( $defaults as $section => $fallback ) {
		$stored[ $section ] = isset( $stored[ $section ] ) && is_array( $stored[ $section ] )
			? wp_parse_args( $stored[ $section ], $fallback )
			: $fallback;
	}

	return $stored;
}

/**
 * Sanitize a URL/link that may be relative ("/contact", "#contact") or absolute.
 *
 * @param mixed $value Raw input.
 * @return string
 */
function wpas_sanitize_link( $value ) {
	$value = is_string( $value ) ? trim( wp_unslash( $value ) ) : '';

	if ( '' === $value ) {
		return '';
	}

	// Allow anchors and root-relative paths verbatim.
	if ( '#' === $value[0] || '/' === $value[0] ) {
		return sanitize_text_field( $value );
	}

	// Tel and mailto pass through esc_url_raw.
	if ( preg_match( '#^(tel:|mailto:|https?://|wa\.me/)#i', $value ) ) {
		return esc_url_raw( $value );
	}

	return esc_url_raw( $value );
}

/**
 * Render the opening markup for a Theme Options section card.
 *
 * @param array{id:string,title:string,description?:string} $args Section meta.
 */
function wpas_section_start( $args ) {
	$id          = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'section';
	$title       = isset( $args['title'] ) ? $args['title'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	?>
	<section id="wpas-section-<?php echo esc_attr( $id ); ?>" class="wpas-card">
		<header class="wpas-card__header">
			<h2 class="wpas-card__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( '' !== $description ) : ?>
				<p class="wpas-card__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>
		<div class="wpas-card__body">
	<?php
}

/**
 * Render the closing markup for a Theme Options section card.
 */
function wpas_section_end() {
	?>
		</div>
	</section>
	<?php
}

/**
 * Render a labelled text input.
 *
 * @param array{label:string,name:string,value:string,type?:string,placeholder?:string,dir?:string,help?:string} $args Field args.
 */
function wpas_field_text( $args ) {
	$type        = isset( $args['type'] ) ? $args['type'] : 'text';
	$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$dir         = isset( $args['dir'] ) ? $args['dir'] : '';
	$help        = isset( $args['help'] ) ? $args['help'] : '';
	$id          = 'wpas-' . sanitize_html_class( str_replace( array( '[', ']' ), array( '-', '' ), $args['name'] ) );
	?>
	<div class="wpas-field">
		<label for="<?php echo esc_attr( $id ); ?>" class="wpas-field__label">
			<?php echo esc_html( $args['label'] ); ?>
		</label>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			value="<?php echo esc_attr( $args['value'] ); ?>"
			class="wpas-field__input"
			<?php if ( $placeholder ) : ?>placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php endif; ?>
			<?php if ( $dir ) : ?>dir="<?php echo esc_attr( $dir ); ?>"<?php endif; ?>
		>
		<?php if ( $help ) : ?>
			<p class="wpas-field__help"><?php echo esc_html( $help ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render a labelled textarea.
 *
 * @param array{label:string,name:string,value:string,rows?:int,help?:string} $args Field args.
 */
function wpas_field_textarea( $args ) {
	$rows = isset( $args['rows'] ) ? (int) $args['rows'] : 4;
	$help = isset( $args['help'] ) ? $args['help'] : '';
	$id   = 'wpas-' . sanitize_html_class( str_replace( array( '[', ']' ), array( '-', '' ), $args['name'] ) );
	?>
	<div class="wpas-field">
		<label for="<?php echo esc_attr( $id ); ?>" class="wpas-field__label">
			<?php echo esc_html( $args['label'] ); ?>
		</label>
		<textarea
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			rows="<?php echo esc_attr( (string) $rows ); ?>"
			class="wpas-field__input wpas-field__input--textarea"
		><?php echo esc_textarea( $args['value'] ); ?></textarea>
		<?php if ( $help ) : ?>
			<p class="wpas-field__help"><?php echo esc_html( $help ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render a repeater of opening-hours rows ([{days, time}, ...]).
 *
 * @param string                                       $name_prefix Base option name.
 * @param array<int, array{days:string,time:string}>   $rows        Current values.
 */
function wpas_field_hours( $name_prefix, $rows ) {
	if ( empty( $rows ) ) {
		$rows = array( array( 'days' => '', 'time' => '' ) );
	}
	?>
	<div class="wpas-hours" data-wpas-repeater data-wpas-prefix="<?php echo esc_attr( $name_prefix ); ?>">
		<div class="wpas-hours__list" data-wpas-repeater-list>
			<?php foreach ( $rows as $i => $row ) : ?>
				<div class="wpas-hours__row" data-wpas-repeater-row>
					<input
						type="text"
						name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( (string) $i ); ?>][days]"
						value="<?php echo esc_attr( $row['days'] ?? '' ); ?>"
						class="wpas-field__input"
						placeholder="<?php esc_attr_e( 'ימים (למשל: א׳–ה׳)', 'wpas' ); ?>"
					>
					<input
						type="text"
						name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( (string) $i ); ?>][time]"
						value="<?php echo esc_attr( $row['time'] ?? '' ); ?>"
						class="wpas-field__input"
						placeholder="<?php esc_attr_e( 'שעות (למשל: 09:00 – 18:00)', 'wpas' ); ?>"
						dir="ltr"
					>
					<button type="button" class="button button-link-delete" data-wpas-repeater-remove>
						<?php esc_html_e( 'הסרה', 'wpas' ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" data-wpas-repeater-add>
			<?php esc_html_e( 'הוספת שורה', 'wpas' ); ?>
		</button>
	</div>
	<?php
}

/**
 * Resolve attachment URL with manual URL fallback.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $fallback_url  Stored URL.
 * @return string
 */
function wpas_attachment_url( $attachment_id, $fallback_url = '' ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_url( $attachment_id );
		if ( $url ) {
			return $url;
		}
	}
	return is_string( $fallback_url ) ? $fallback_url : '';
}

/**
 * Render a media picker (image or video).
 *
 * @param array{label:string,name_prefix:string,item:array,media_type?:string,id_key?:string,url_key?:string} $args Field args.
 */
function wpas_field_media( $args ) {
	$id_key     = $args['id_key'] ?? 'attachment_id';
	$url_key    = $args['url_key'] ?? 'url';
	$item       = isset( $args['item'] ) && is_array( $args['item'] ) ? $args['item'] : array();
	$media_type = $args['media_type'] ?? 'image';
	$id         = isset( $item[ $id_key ] ) ? (int) $item[ $id_key ] : 0;
	$url        = isset( $item[ $url_key ] ) ? (string) $item[ $url_key ] : '';
	$thumb      = wpas_attachment_url( $id, $url );
	?>
	<div class="wpas-field wpas-media" data-wpas-media data-media-type="<?php echo esc_attr( $media_type ); ?>">
		<label class="wpas-field__label"><?php echo esc_html( $args['label'] ); ?></label>
		<div class="wpas-media__thumb" data-wpas-media-thumb <?php echo '' === $thumb ? 'hidden' : ''; ?>>
			<?php if ( 'image' === $media_type && $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
			<?php elseif ( $thumb ) : ?>
				<code><?php echo esc_html( basename( $thumb ) ); ?></code>
			<?php endif; ?>
		</div>
		<div class="wpas-media__controls">
			<input type="hidden" name="<?php echo esc_attr( $args['name_prefix'] ); ?>[<?php echo esc_attr( $id_key ); ?>]" value="<?php echo esc_attr( (string) $id ); ?>" data-wpas-media-id>
			<input type="text" name="<?php echo esc_attr( $args['name_prefix'] ); ?>[<?php echo esc_attr( $url_key ); ?>]" value="<?php echo esc_attr( $url ); ?>" class="wpas-field__input" placeholder="<?php esc_attr_e( 'כתובת מדיה (אופציונלי)', 'wpas' ); ?>" data-wpas-media-url>
			<button type="button" class="button button-primary" data-wpas-media-select><?php esc_html_e( 'בחירה מהספרייה', 'wpas' ); ?></button>
			<button type="button" class="button" data-wpas-media-clear><?php esc_html_e( 'ניקוי', 'wpas' ); ?></button>
		</div>
	</div>
	<?php
}
