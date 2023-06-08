<?php
/**
 *
 */

namespace Succotash\Portfolio\Widget\Themes;

use Exception;
use WP_Widget;
use ZipArchive;

class Component extends WP_Widget {

	public function __construct() {
		$widget_options = [
			'classname' => 'theme_widget',
			'description' => __('A widget to output theme information if a slug is set through the meta box.', 'succotash'),
		];

		parent::__construct('portfolio_theme_info', 'Theme Info', $widget_options);
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id('title' ) ); ?>"><?php esc_html_e('Title: ', 'succotash' ); ?></label><br />
			<input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php
	}

	/**
	 * @throws Exception
	 */
	public function widget( $args, $instance ) {

		// Set global $post
		global $post;

		// set the slug to post type.
		$slug = $post->post_name;

		// Set transient base.
		$transient_base = 'succotash_portfolio_theme_';

		// Combine transient base and slug
		$transient = $transient_base . $slug;

		// Get the transient for them.
		$theme = get_transient( $transient );

		if ( ! $theme ) {

			$args = [
				'headers' => [
					'Authorization' => 'Bearer ' . MY_TOKEN,
				]
			];

			$uri = wp_remote_get( 'https://api.github.com/repos/luthemes/' . $slug . '/releases', $args );

			// Retrieve the body of the $uri.
			$theme = wp_remote_retrieve_body( $uri );
			$theme = json_decode( $theme, true );

			// set the transient for 24 hours.
			set_transient( $transient, $theme, HOUR_IN_SECONDS );
		}

		# Enable the download_url() and wp_handle_sideload() functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$temp_file = download_url( $theme[0]['assets'][0]['browser_download_url'], 5 );

		$file = array(
			'name'     => basename( $theme[0]['assets'][0]['browser_download_url'] ),
			'type'     => 'application/zip',
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize( $temp_file ),
		);

		$zip = new ZipArchive();
		$zip->open( $file['tmp_name'] );
		$slug = strstr( $zip->getNameIndex(0), '/', true );

		$php = '';
		$version = '';
		$cp = '';

		// $sum = round( $file['size'] / 1000000, 2 );
		// echo $sum . 'MB';

		$style_index = $zip->locateName( 'style.css', ZipArchive::FL_NOCASE|ZipArchive::FL_NODIR );
		$style_txt = $zip->getFromIndex( $style_index, 8192, ZipArchive::FL_UNCHANGED );

		preg_match('/Requires PHP:\s*([\d\.]+)/', $style_txt, $requiresPHP );
		preg_match('/Version:\s*([\d\.]+)/', $style_txt, $requiresVersion );
		preg_match('/Requires CP:\s*([\d\.]+)/', $style_txt, $requiresCP );

		if ( isset( $requiresPHP[1] ) ) {
			$php = $requiresPHP[1];
		}

		if ( isset( $requiresVersion[1] ) ) {
			$version = $requiresVersion[1];
		}

		if ( isset( $requiresCP[1] ) ) {
			$cp = $requiresCP[1];
		}

		ob_start();
		echo $args['before_widget'] . $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		?>
			<table class="theme-info">
				<tbody>
					<tr>
						<th><label for="theme_version"><?php esc_html_e( 'Version: ', 'succotash'); ?></label></th>
						<td><?php echo esc_html( $version ); ?></td>
					</tr>
					<tr>
						<th><label for="theme_updated"><?php esc_html_e( 'Last Updated: ', 'succotash'); ?></label></th>
						<td><?php echo date( 'F d, Y', strtotime( $theme[0]['published_at'] ) ); ?></td>
					</tr>
					<tr>
						<th><label for="php_version"><?php esc_html_e( 'ClassicPress Version: ', 'succotash'); ?></label></th>
						<td><?php echo esc_html( $cp .'.0' ); ?></td>
					</tr>
					<tr>
						<th><label for="php_version"><?php esc_html_e( 'PHP Version: ', 'succotash'); ?></label></th>
						<td><?php echo esc_html( $php ); ?></td>
					</tr>

				<tr>
					<th><label for="theme_version"><?php esc_html_e( 'Type: ', 'succotash'); ?></label></th>
					<?php
					$words = wptexturize( wp_strip_all_tags( get_post( get_post_thumbnail_id() )->post_content ) );
					$type = explode( " ", $words );
					array_splice( $type, -1 );
					?>
					<td><span><?php echo implode( $type ); ?></span></td>
				</tr>
				<tr>
					<th><label for="github_repo"><?php esc_html_e( 'Repository: ', 'succotash'); ?></label></th>
					<td><i class="fab fa-github"></i> <a href="<?php echo esc_url_raw( 'https://github.com/benlumia007/' . $slug ); ?>"><?php esc_html_e( 'Github', 'succotash'); ?></a></td>
				</tr>
				<tr>
					<td colspan="2" class="download-button" style="text-align: center;"><button><i class="fas fa-download"></i> <a href="<?php echo esc_url( $theme[0]['assets'][0]['browser_download_url'] ); ?>"><?php esc_html_e( 'Download', 'succotash' ); ?></a></button></td>
				</tr>
				</tbody>
			</table>
		<?php
		echo $args['after_widget'];
	}
}
