<?php
/**
 * Plugin Name: Hinnerks Map Tool for Wordpress
 * Description: Adds a Gutenberg block for placing Maps based on an uploaded GeoJSON anywhere in posts and pages.
 * Version: 1.1.3
 * Author: Hinnerk Weiler
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HWMAPTOOL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Allow GeoJSON uploads.
 */
add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['geojson'] = 'application/geo+json';
	$mimes['json']    = 'application/json';
	return $mimes;
} );

/**
 * Improve file type detection for .geojson files.
 */
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );

	if ( strtolower( $ext ) === 'geojson' ) {
		$data['ext']  = 'geojson';
		$data['type'] = 'application/geo+json';
	}

	return $data;
}, 10, 4 );

/**
 * Register the block.
 */
add_action( 'init', function () {
	$block_dir = __DIR__ . '/blocks/map-geojson';

	wp_register_script(
		'hw-map-geojson-editor',
		plugins_url( 'blocks/map-geojson/index.js', __FILE__ ),
		[
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-components',
			'wp-block-editor',
		],
		filemtime( $block_dir . '/index.js' )
	);

	register_block_type(
		$block_dir,
		[
			'editor_script'   => 'hw-map-geojson-editor',
			'render_callback' => function ( $attributes, $content, $block ) use ( $block_dir ) {
				ob_start();
				$attributes = is_array( $attributes ) ? $attributes : [];
				include $block_dir . '/render.php';
				return ob_get_clean();
			},
		]
	);
} );
