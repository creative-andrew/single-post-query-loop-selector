<?php
/**
 * Plugin Name:       Single Post Query Loop Selector
 * Description:       A Query Loop block variation that allows to search and select a single post to be displayed.
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Creative Andrew
 * Author URI:        https://creativeandrew.me
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       single-post-query-loop-selector
 */

namespace SinglePostQueryLoopSelector;

class Plugin {

	protected $parsed_block;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return Plugin A single instance of this class.
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initializes the plugin hooks.
	 */
	public function hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_filter( 'pre_render_block', [ $this, 'pre_render_block' ], 10, 2 );
	}

	/**
	 * Enqueues block editor assets for a WordPress plugin.
	 */
	public function enqueue_block_editor_assets() {
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
	public function pre_render_block( $pre_render, $parsed_block ) {
		if (
			! empty( $parsed_block['attrs']['namespace'] )
			&& 'creativeandrew/single-post-query-loop-selector' === $parsed_block['attrs']['namespace']
		) {
			$this->parsed_block = $parsed_block;
			add_filter( 'query_loop_block_query_vars', [ $this, 'modify_query_vars' ], 10, 3 );
		}

		return $pre_render;
	}

	/**
	 * Modifies the query vars for the Query Loop block.
	 *
	 * This function is used to modify the query vars for the Query Loop block. It checks if the block
	 * has a custom query attribute and, if so, modifies the query vars accordingly.
	 *
	 * @param array    $query Array containing parameters for `WP_Query` as parsed by the block context.
	 * @param WP_Block $block Block instance.
	 * @param int      $page  Current query's page.
	 *
	 * @return array The modified query vars.
	 */
	public function modify_query_vars( $query, $block, $page ) {
		if ( isset( $this->parsed_block['attrs']['namespace'] ) && 'creativeandrew/single-post-query-loop-selector' === $this->parsed_block['attrs']['namespace'] ) {

			if ( isset( $this->parsed_block['attrs']['query']['include'] ) ) {
				$query['post__in'] = $this->parsed_block['attrs']['query']['include'];
			}
		}

		// Our work here is done (backs away slowly).
		remove_filter( 'query_loop_block_query_vars', [ $this, 'modify_query_vars' ], 10, 3 );

		return $query;
	}
}

Plugin::get_instance()->hooks();
