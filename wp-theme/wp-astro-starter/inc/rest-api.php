<?php
/**
 * REST API endpoint that exposes all theme options as one JSON document.
 *
 * The Astro build calls GET /wp-json/wpas/v1/options at build time and
 * uses the response to populate every component (header, footer,
 * WhatsApp button, schema.org, etc.).
 *
 * @package wpas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the public read-only options endpoint.
 */
function wpas_register_rest_routes() {
	register_rest_route(
		WPAS_REST_NAMESPACE,
		'/options',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => 'wpas_rest_get_options',
		)
	);
}
add_action( 'rest_api_init', 'wpas_register_rest_routes' );

/**
 * Build the JSON payload returned to the Astro build.
 *
 * @return WP_REST_Response
 */
function wpas_rest_get_options() {
	$options = wpas_get_theme_options();
	$general = $options['general'];
	$contact = $options['contact'];

	// Resolve attachment IDs to URLs so the consumer doesn't need a
	// second round-trip.
	$logo_url     = wpas_attachment_url( $general['logo']['attachment_id'] ?? 0, $general['logo']['url'] ?? '' );
	$og_image_url = wpas_attachment_url( $general['og_image']['attachment_id'] ?? 0, $general['og_image']['url'] ?? '' );

	$payload = array(
		'general' => array(
			'name'          => $general['name'],
			'legal_name'    => $general['legal_name'],
			'tagline'       => $general['tagline'],
			'description'   => $general['description'],
			'founding_year' => (int) $general['founding_year'],
			'theme_color'   => $general['theme_color'],
			'logo_url'      => $logo_url,
			'og_image_url'  => $og_image_url,
		),
		'contact' => $contact,
		'social'  => $options['social'],
		'seo'     => $options['seo'],
		'site'    => array(
			'url'    => home_url( '/' ),
			'locale' => str_replace( '_', '-', get_locale() ),
		),
	);

	return new WP_REST_Response( $payload, 200 );
}
