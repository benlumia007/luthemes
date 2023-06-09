<?php
/**
 *
 */

namespace Succotash\Portfolio\Meta;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	public function post_meta() {

		add_meta_box( 'portfolio-id', 'Theme Information', [$this, 'backdrop_portfolio'], 'portfolio', 'normal', 'high' );

	}

	public function backdrop_portfolio( $post_id ) {
		global $post;

		$slug = $post->post_name;

		// Set transient base.
		$transient_base = 'succotash_portfolio_theme_';

		// Combine transient base and slug
		$transient = $transient_base . $slug;

		// Get the transient for them.
		$theme = get_transient( $transient );

		if ( ! $theme ) {

			$uri = wp_remote_get( 'https://api.github.com/repos/benlumia007/' . $slug . '/releases' );

				// Retrieve the body of the $uri.
				$theme = wp_remote_retrieve_body( $uri );
				$theme = json_decode( $theme );

			// set the transient for 24 hours.
			set_transient( $transient, $theme, 5 );
		}

		wp_nonce_field( basename( __FILE__ ), "backdrop_portfolio_nonce" );

		$name    = get_post_meta( get_the_ID(), 'theme_name',    true );
		$version = get_post_meta( get_the_ID(), 'theme_version', true );
		$updated = get_post_meta( get_the_ID(), 'theme_updated', true );
		$repo    = get_post_meta( get_the_ID(), 'github_repo',   true );
		$php     = get_post_meta( get_the_ID(), 'php_version',   true );
		?>

		<table>
			<tbody>
			<tr>
				<th style="width: 25%; text-align: left"><label for="theme_name"><?php esc_html_e( 'Name: ', 'succotash'); ?></label></th>
				<td><input style="padding: 0.4rem" class="widefat" size="45" type="text" name="theme_name" id="theme_name" value="<?php the_post_thumbnail_caption(); ?>" /></td>
			</tr>
			<tr>
				<th style="text-align: left"><label for="theme_version"><?php esc_html_e( 'Version: ', 'succotash'); ?></label></th>
				<td><input style="padding: 0.4rem" class="widefat" type="text" name="theme_version" id="theme_version" value="<?php echo $theme[0]->tag_name; ?>" /></td>
			</tr>
			<tr>
				<th style="text-align: left"><label for="theme_updated"><?php esc_html_e( 'Updated: ', 'succotash'); ?></label></th>
				<td><input style="padding: 0.4rem" class="widefat" type="text" name="theme_updated" id="theme_updated" value="<?php echo date( 'F d, Y', strtotime( $theme[0]->published_at ) ); ?>" /></td>
			</tr>
			<tr>
				<th style="text-align: left"><label for="php_version"><?php esc_html_e( 'PHP: ', 'succotash'); ?></label></th>
				<td><input style="padding: 0.4rem" class="widefat" type="text" name="php_version" id="php_version" value="<?php echo $php; ?>" /></td>
			</tr>
			<tr>
				<th style="text-align: left"><label for="github_repo"><?php esc_html_e( 'Github: ', 'succotash'); ?></label></th>
				<td><input style="padding: 0.4rem" class="widefat"" type="text" name="github_repo" id="github_repo" value="<?php echo $theme[0]->assets[0]->uploader->html_url .'/' . $slug ?>" /></td>
			</tr>
			</tbody>
		</table>

		<?php
	}

	public function save_data( $post_id, $post ) {

		if ( ! isset( $_POST[ 'backdrop_portfolio_nonce' ] ) || ! wp_verify_nonce( $_POST['backdrop_portfolio_nonce'], basename(__FILE__ ) ) ) {
			return $post_id;
		}

		$slug = 'portfolio';

		if ( $slug != $post->post_type ) {
			return;
		}

		$name = '';

		if ( isset( $_POST[ 'theme_name'] ) ) {

			$name = sanitize_text_field( $_POST['theme_name'] );
			update_post_meta( $post_id, "theme_name", $name );
		}

		if ( isset( $_POST[ 'theme_version'] ) ) {

			$version = sanitize_text_field( $_POST['theme_version'] );
			update_post_meta( $post_id, "theme_version", $version );
		}

		if ( isset( $_POST[ 'theme_updated'] ) ) {

			$version = sanitize_text_field( $_POST['theme_updated'] );
			update_post_meta( $post_id, "theme_updated", $version );
		}

		if ( isset( $_POST[ 'github_repo'] ) ) {

			$version = sanitize_text_field( $_POST['github_repo'] );
			update_post_meta( $post_id, "github_repo", $version );
		}

		if ( isset( $_POST[ 'php_version'] ) ) {

			$version = sanitize_text_field( $_POST['php_version'] );
			update_post_meta( $post_id, "php_version", $version );
		}




	}

	public function boot() {

		add_action( 'add_meta_boxes_portfolio', [ $this, 'post_meta' ] );
		add_action( 'save_post', [ $this, 'save_data' ], 10, 2 );
	}
}
