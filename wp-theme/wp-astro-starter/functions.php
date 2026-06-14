<?php
/**
 * WP × Astro Starter — theme bootstrap.
 *
 * This theme is intentionally minimal. It does not render the front-end
 * (Astro does). Its job is to:
 *   1. Provide a Theme Options admin page (Settings API, no plugins).
 *   2. Expose the options + content via the WP REST API for the Astro
 *      build to consume.
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPAS_VERSION', '0.1.0' );
define( 'WPAS_THEME_DIR', get_template_directory() );
define( 'WPAS_THEME_URL', get_template_directory_uri() );

/** Single wp_options row that stores everything for this theme. */
define( 'WPAS_OPTION_KEY', 'wpas_theme_options' );

/** REST API namespace. Astro reads /wp-json/wpas/v1/options. */
define( 'WPAS_REST_NAMESPACE', 'wpas/v1' );

require_once WPAS_THEME_DIR . '/inc/helpers.php';
require_once WPAS_THEME_DIR . '/inc/theme-options.php';
require_once WPAS_THEME_DIR . '/inc/section-general.php';
require_once WPAS_THEME_DIR . '/inc/section-contact.php';
require_once WPAS_THEME_DIR . '/inc/section-social.php';
require_once WPAS_THEME_DIR . '/inc/section-seo.php';
require_once WPAS_THEME_DIR . '/inc/rest-api.php';

/**
 * Theme setup — declares feature support and translation domain.
 */
function wpas_setup_theme() {
	load_theme_textdomain( 'wpas', WPAS_THEME_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form', 'script', 'style' ) );
	add_theme_support( 'automatic-feed-links' );

	// Even though we don't render menus, registering them lets WP show
	// the Menus admin UI and exposes them via the REST API to Astro.
	register_nav_menus(
		array(
			'primary' => __( 'תפריט ראשי', 'wpas' ),
			'footer'  => __( 'תפריט תחתון', 'wpas' ),
		)
	);
}
add_action( 'after_setup_theme', 'wpas_setup_theme' );

/**
 * Enqueue admin styles for the Theme Options page only.
 *
 * @param string $hook Current admin page hook.
 */
function wpas_enqueue_admin_assets( $hook ) {
	if ( false === strpos( $hook, 'wpas-theme-options' ) ) {
		return;
	}

	wp_enqueue_style( 'wpas-admin', WPAS_THEME_URL . '/assets/admin.css', array(), WPAS_VERSION );
	wp_enqueue_media();
	wp_enqueue_script( 'wpas-admin', WPAS_THEME_URL . '/assets/admin.js', array( 'jquery' ), WPAS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'wpas_enqueue_admin_assets' );
