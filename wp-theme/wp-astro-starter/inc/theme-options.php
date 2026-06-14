<?php
/**
 * Theme Options — admin page registration, tab navigation, save handler.
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available tabs on the Theme Options page.
 *
 * @return array<string, array{label:string,render:callable}>
 */
function wpas_theme_options_tabs() {
	return array(
		'general' => array(
			'label'  => __( 'כללי', 'wpas' ),
			'render' => 'wpas_render_section_general',
		),
		'contact' => array(
			'label'  => __( 'יצירת קשר', 'wpas' ),
			'render' => 'wpas_render_section_contact',
		),
		'social'  => array(
			'label'  => __( 'רשתות חברתיות', 'wpas' ),
			'render' => 'wpas_render_section_social',
		),
		'seo'     => array(
			'label'  => __( 'SEO ומדידה', 'wpas' ),
			'render' => 'wpas_render_section_seo',
		),
	);
}

/**
 * Register the top-level admin menu.
 */
function wpas_register_theme_options_page() {
	add_menu_page(
		__( 'הגדרות תבנית', 'wpas' ),
		__( 'הגדרות תבנית', 'wpas' ),
		'manage_options',
		'wpas-theme-options',
		'wpas_render_theme_options_page',
		'dashicons-admin-customizer',
		61
	);
}
add_action( 'admin_menu', 'wpas_register_theme_options_page' );

/**
 * Register the option with the Settings API.
 */
function wpas_register_theme_options() {
	register_setting(
		'wpas_theme_options_group',
		WPAS_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wpas_sanitize_theme_options',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'wpas_register_theme_options' );

/**
 * Sanitize the entire options array on save.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function wpas_sanitize_theme_options( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$existing = get_option( WPAS_OPTION_KEY, array() );
	$existing = is_array( $existing ) ? $existing : array();

	$clean              = $existing;
	$clean['general']   = wpas_sanitize_general_settings( $input['general'] ?? array() );
	$clean['contact']   = wpas_sanitize_contact_settings( $input['contact'] ?? array() );
	$clean['social']    = wpas_sanitize_social_settings( $input['social'] ?? array() );
	$clean['seo']       = wpas_sanitize_seo_settings( $input['seo'] ?? array() );

	return $clean;
}

/**
 * Render the Theme Options page shell — header, tabs, form, footer.
 */
function wpas_render_theme_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'אין לך הרשאה לצפות בעמוד זה.', 'wpas' ) );
	}

	$tabs       = wpas_theme_options_tabs();
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $active_tab ] ) ) {
		$active_tab = 'general';
	}
	?>
	<div class="wrap wpas-wrap">
		<header class="wpas-header">
			<h1 class="wpas-header__title">
				<span class="dashicons dashicons-admin-customizer"></span>
				<?php esc_html_e( 'הגדרות תבנית', 'wpas' ); ?>
			</h1>
			<p class="wpas-header__lead">
				<?php esc_html_e( 'ההגדרות בעמוד זה נשלפות אוטומטית על-ידי האתר הסטטי (Astro) בכל build.', 'wpas' ); ?>
			</p>
		</header>

		<nav class="wpas-tabs" aria-label="<?php esc_attr_e( 'ניווט קטגוריות הגדרה', 'wpas' ); ?>">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<a
					href="<?php echo esc_url( admin_url( 'admin.php?page=wpas-theme-options&tab=' . $slug ) ); ?>"
					class="wpas-tabs__link <?php echo $active_tab === $slug ? 'is-active' : ''; ?>"
				>
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php" class="wpas-form">
			<?php settings_fields( 'wpas_theme_options_group' ); ?>
			<?php
			$render = $tabs[ $active_tab ]['render'];
			if ( is_callable( $render ) ) {
				call_user_func( $render, WPAS_OPTION_KEY );
			}
			?>
			<div class="wpas-actions">
				<?php submit_button( __( 'שמור הגדרות', 'wpas' ), 'primary large', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Add an admin notice after successful save (Settings API).
 */
function wpas_settings_saved_notice() {
	if ( ! isset( $_GET['page'] ) || 'wpas-theme-options' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_settings_error( 'wpas_messages', 'wpas_saved', __( 'ההגדרות נשמרו.', 'wpas' ), 'success' );
	}
	settings_errors( 'wpas_messages' );
}
add_action( 'admin_notices', 'wpas_settings_saved_notice' );
