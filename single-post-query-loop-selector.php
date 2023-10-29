<?php
/**
 * Plugin Name:       Single Post Query Loop Selector
 * Description:       A Query Loop block variation that allows to search and select a single post to be displayed.
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Creative Andrew
 * Author URI:        https://www.fiverr.com/creative_andrew
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       single-post-query-loop-selector
 */

namespace SinglePostQueryLoopSelector;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
/**
 * Enqueues block editor assets for a WordPress plugin.
 */
function enqueue_block_editor_assets() {
	$assets_file = plugin_dir_path( __FILE__ ) . '/build/index.asset.php';
	if ( file_exists( $assets_file ) ) {
		$assets = include $assets_file;
		wp_enqueue_script(
			'single-post-query-loop-selector',
			plugin_dir_url( __FILE__ ) . '/build/index.js',
			$assets['dependencies'],
			$assets['version'],
			true
		);
	}
}



add_filter(
	'pre_render_block',
	__NAMESPACE__ . '\pre_reender_block',
	10,
	2
);
/**
 * Updates the query on the front end based on custom query attributes.
 *
 * This function is used to modify the query on the front end based on the custom query attributes
 * of a specific block. It checks if the block belongs to a particular namespace and, if so,
 * applies a filter to adjust the query parameters.
 *
 * @param array $pre_render The pre-render data for the block.
 * @param array $parsed_block The parsed attributes of the block.
 *
 * @return array The modified pre-render data.
 */
function pre_render_block( $pre_render, $parsed_block ) {
	if ( isset( $parsed_block['attrs']['namespace'] ) && 'creativeandrew/single-post-query-loop-selector' === $parsed_block['attrs']['namespace'] ) {
		add_filter(
			'query_loop_block_query_vars',
			function ( $default_query ) use ( $parsed_block ) {
				if ( isset( $parsed_block['attrs']['query']['include'] ) ) {
					$default_query['post__in'] = $parsed_block['attrs']['query']['include'];
				}

				return $default_query;
			},
			10,
			2
		);

	}
	return $pre_render;
}
