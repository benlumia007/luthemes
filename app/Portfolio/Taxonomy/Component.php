<?php
/**
 *
 */

namespace Succotash\Portfolio\Taxonomy;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	/**
	 * Get taxonomy labels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param string $singular Singular name.
	 * @param string $plural   Plural name.
	 * @return array Taxonomy labels.
	 */
	public function get_taxonomy_labels( string $singular, string $plural ): array {
		$labels = [
			'name' 							=> esc_html_x( $plural, 'taxonomy general name', 				'succotash' ),
			'singular_name' 				=> esc_html_x( $singular, 'taxonomy singular name', 				'succotash' ),
			'menu_name' 					=> esc_html_x( $plural, 'taxonomy menu name', 					'succotash' ),
			'name_admin_bar'				=> esc_html_x( $singular, 'taxonomy singular name admin bar',	'succotash' ),
			'search_items'					=> esc_html__('Search ' . $plural, 								'succotash' ),
			'popular_items' 				=> esc_html__('Popular ' . $plural, 								'succotash' ),
			'all_items' 					=> esc_html__('All ' . $plural, 									'succotash' ),
			'edit_item' 					=> esc_html__('Edit ' . $singular, 								'succotash' ),
			'view_item' 					=> esc_html__('View ' . $singular, 								'succotash' ),
			'update_item'					=> esc_html__( 'Update ' . $singular, 								'succotash' ),
			'add_new_item'					=> esc_html__( 'Add New ' . $singular, 							'succotash' ),
			'new_item_name'					=> esc_html__( 'New ' . $singular . ' Name', 						'succotash' ),
			'not_found' 					=> esc_html__( 'No ' . $plural . ' found.', 						'succotash' ),
			'no_terms'						=> esc_html__( 'No ' . $plural, 									'succotash' ),
			'pagination'					=> esc_html__( $plural . ' list navigation', 						'succotash' ),
			'list'							=> esc_html__( $plural . ' list', 									'succotash' ),
			'separate_items_with_commas'	=> esc_html__( 'Separate ' . $plural . ' with commas', 			'succotash' ),
			'add_or_remove_items'			=> esc_html__( 'Add or remove ' . $plural,							'succotash' ),
			'choose_from_most_used'			=> esc_html__( 'Choose from the most used ' . $plural, 			'succotash' ),
		];

		if ( is_taxonomy_hierarchical( $plural ) ) {

			$labels['select_name']       = esc_html__( 'Select ' . $singular, 			'succotash' );
			$labels['parent_item']       = esc_html__( 'Parent ' . $singular, 			'succotash' );
			$labels['parent_item_colon'] = esc_html__( 'Parent ' . $singular . ':', 	'succotash' );
		}

		return apply_filters( 'succotash/portfolio/taxonomy/label', $labels, $singular, $plural );
	}

	/**
	 * Feature Taxonomy Labels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array Feature Taxonomy Label
	 */
	public function feature_labels(): array {

		return $this->get_taxonomy_labels( 'Feature', 'Features' );
	}

	/**
	 * Layout Taxonomy Labels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array Layout Taxonomy Label
	 */
	public function layout_labels(): array {

		return $this->get_taxonomy_labels( 'Layout', 'Layouts' );
	}

	/**
	 * Subject Taxonomy Labels.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array Subject Taxonomy Label
	 */
	public function subject_labels(): array {

		return $this->get_taxonomy_labels( 'Subject', 'Subjects' );
	}

	/**
	 * Registers post types needed by the plugin.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function create_taxonomies() {

		// Make sure that we disabled default taxonomies
		if ( taxonomy_exists( 'portfolio-category' ) && taxonomy_exists( 'portfolio-tag' ) ) {
			unregister_taxonomy( 'portfolio-category' );
			unregister_taxonomy( 'portfolio-tag' );
		}

		// Set up the arguments for the portfolio project post type.
		$feature_args = [
			'labels'            => $this->feature_labels(),
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
		];

		$layout_args = [
			'labels'            => $this->layout_labels(),
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
		];

		$subject_args = [
			'labels'            => $this->subject_labels(),
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
		];


		// Register the post types.
		register_taxonomy( 'portfolio-feature', 'portfolio', $feature_args );
		register_taxonomy( 'portfolio-layout',  'portfolio', $layout_args );
		register_taxonomy( 'portfolio-subject', 'portfolio', $subject_args );
	}

	public function boot() {
		add_action( 'init', [ $this, 'create_taxonomies' ], 9 );
	}
}
