<?php
/**
 * Settings component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace Succotash\Portfolio\GitHub;

use Backdrop\Contracts\Bootable;
use ZipArchive;

class Component implements Bootable {

	/**
	 * Settings page name.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var string
	 */
	public $settings_page = '';

	/**
	 * Sets up custom admin menus.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		// Create the settings page.
		$this->settings_page = add_submenu_page(
			'edit.php?post_type=' . 'portfolio',
			esc_html__( 'GitHub Settings', 'backdrop-custom-portfolio' ),
			esc_html__( 'GitHub', 'backdrop-custom-portfolio' ),
			'manage_options',
			'github-settings',
			array( $this, 'settings_page' )
		);

		if ( $this->settings_page ) {

			// Register settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	/**
	 * Registers the plugin settings.
	 *
	 * @return array
	 *@since  1.0.0
	 * @access public
	 */
	public function register_settings() {
		$items = [];

		$type = [
			'post_type' => 'portfolio',
			'numberposts' => -1,
		];

		$posts = get_posts( $type );

		foreach ( $posts  as $post ) {
			$items[] = $post->post_name;
		}
		$items = array_unique( $items );
		asort( $items);

		return $items;


	}

	/**
	 * General section callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function section_general() { ?>
		<p class="description">
			<?php esc_html_e( 'General portfolio settings for your site.', 'backdrop-custom-portfolio' ); ?>
		</p>
	<?php }

	/**
	 * Renders the settings page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function settings_page() {
		if ( isset( $_POST['save_settings'] ) ) {
			$api_token = sanitize_text_field( $_POST['github_api_token'] );

			if ( empty( $api_token ) ) {
				echo '<div class="notice notice-error"><p>Please enter a GitHub API token.</p></div>';
			} else {
				update_option( 'github_api_token', $api_token );
				echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
			}
		}

		$api_token = get_option( 'github_api_token' );



		?>
		<div class="wrap">
			<h1>GitHub API Settings</h1>

			<form method="post" action="">
				<label for="github_api_token">GitHub API Token:</label>
				<input type="text" name="github_api_token" value="<?php echo esc_attr( $api_token ); ?>" placeholder="Enter your GitHub API token">

				<p>
					<input type="submit" name="save_settings" class="button button-primary" value="Save Settings">
				</p>
			</form>
		</div>

		<div class="wrap">
			<h1>Latest Releases</h1>

			<?php

			$slugs = $this->register_settings();

			echo '<ul class="grid-items" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; grid-gap: 1.125rem;">';
			foreach ( $slugs as $repo ) {
				echo '<li class="grid-items">';
					$this->display_latest_release( $repo );
				echo '</li>';
			}
			echo '</ul>';
			?>

		</div>
		<?php
	}

	public function display_latest_release( $repository ) {
		$api_token = get_option( 'github_api_token' );

		if ( empty( $api_token ) ) {
			echo '<div class="notice notice-warning"><p>Please set the GitHub API Token in the settings above before continuing.</p></div>';
			exit;
		}

		$releases_url = "https://api.github.com/repos/luthemes/$repository/releases/latest";

		$response = wp_remote_get( $releases_url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_token,
				'Accept' => 'application/vnd.github.v3+json'
			]
		] );

		if ( is_wp_error( $response ) ) {
			echo '<div class="notice notice-error"><p>Error retrieving latest release from GitHub API for repository: ' . $repository . '</p></div>';
			return;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $release ) ) {
			echo '<div class="notice notice-info"><p>No releases found for repository: ' . $repository . '</p></div>';
		} else {
			$string = $repository;
			$newString = str_replace( "-", " ", $string );
			$name = ucwords( $newString);
			$version = $release['tag_name'] ?? '';
			$published = isset( $release['published_at'] ) ? date('F d, Y', strtotime( $release['published_at'] ) ) : '';
			$download = ! empty($release['assets'][0]) ? '<button class="button button-primary"><a style="color: white;" href="' . $release['assets'][0]['browser_download_url'] . '">Download</a></button>' : '<button class="button button-primary">' . esc_html__( 'No Releases', 'succotash' ) . '</button>';

			// Grab information from Theme's style.css
			$cp       = '';
			$wp       = '';
			$php      = '';
			$repo_url = '';

			if ( ! empty( $release['assets'] ) ) {

				$latest = $release['assets'][0];
				$url = $latest['browser_download_url'];

				$repo_url = "https://github.com/luthemes/$repository";


				# Enable the download_url() and wp_handle_sideload() functions
				require_once( ABSPATH . 'wp-admin/includes/file.php' );

				$temp = download_url( $url, 5 );

				$file = [
					'name'     => basename( $url ),
					'type'     => 'application/zip',
					'tmp_name' => $temp,
					'error'    => 0,
					'size'     => filesize( $temp ),
				];

				$zip = new ZipArchive();
				$zip->open( $file['tmp_name'] );
				$slug = strstr( $zip->getFromIndex( 0 ), '/', true );

				// $sum = round( $file['size'] / 1000000, 2 );
				// echo $sum . 'MB';

				$index = $zip->locateName( 'style.css', ZipArchive::FL_NOCASE | ZipArchive::FL_NODIR );
				$style   = $zip->getFromIndex( $index, 8192, ZipArchive::FL_UNCHANGED );

				preg_match( '/Requires PHP:\s*([\d\.]+)/', $style, $requiresPHP );
				preg_match( '/Requires CP:\s*([\d\.]+)/', $style, $requiresCP );
				preg_match( '/Tested up to:\s*([\d\.]+)/', $style, $requiresWP );

				if ( isset( $requiresPHP[1] ) ) {
					$php = $requiresPHP[1];
				}

				if ( isset( $requiresCP[1] ) ) {
					$cp = $requiresCP[1];
				}

				if ( isset( $requiresWP[1] ) ) {
					$wp = $requiresWP[1];
				}
			}

			echo '<h2 class="github" style="margin: 0.5rem 0; padding: 0;">' . $name . '</h2>';

			if (  ! empty( $release['tag_name'] )  ) {
				echo '<table class="theme-info widefat fixed striped">';
				echo '<tbody>';
				echo '<tr>';
				echo '<th style="text-align: left;">' . esc_html__( 'Version', 'succotash' ) . '</th>';
				echo '<td style="text-align: right;">' . esc_html( $version ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'Last Updated', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;">' . esc_html( $published ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				if ( $cp ) {
					echo '<th style="text-align: left;"><strong>' . esc_html__( 'ClassicPress version', 'succotash' ). '</strong></th>';
					echo '<td style="text-align: right;">' . esc_html( $cp ) . '</td>';
				} else {
					echo '<th style="text-align: left;"><strong>' . esc_html__( 'WordPress version', 'succotash' ). '</strong></th>';
					echo '<td style="text-align: right;">' . esc_html( $wp ) . '</td>';
				}
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'PHP version', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;">' . esc_html( $php ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'Repository', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;"><i class="fab fa-github"></i> <a href="' . esc_url_raw(  $repo_url ) . '">' . esc_html__( 'GitHub', 'succcotash' ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td colspan="2" style="text-align: center">' . $download . '</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
			} else {
				echo '<table class="theme-info widefat fixed striped">';
				echo '<tbody>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strongth><strong>Version</strong></th>';
				echo '<td style="text-align: right;">' . esc_html( $version ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'Last Updated', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;">' . esc_html( $published ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				if ( $cp ) {
					echo '<th style="text-align: left;"><strong>' . esc_html__( 'ClassicPress version', 'succotash' ). '</strong></th>';
					echo '<td style="text-align: right;">' . esc_html( $cp ) . '</td>';
				} else {
					echo '<th style="text-align: left;"><strong>' . esc_html__( 'WordPress version', 'succotash' ). '</strong></th>';
					echo '<td style="text-align: right;">' . esc_html( $wp ) . '</td>';
				}
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'PHP version', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;">' . esc_html( $php ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th style="text-align: left;"><strong>' . esc_html__( 'Repository', 'succotash' ). '</strong></th>';
				echo '<td style="text-align: right;"></td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td colspan="2" style="text-align: center">' . $download . '</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
			}
		}
	}


	/**
	 * Renders the shortcode content.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function shortcode_content( $args ) {
		global $post;

		// Process the shortcode attributes
		$args = shortcode_atts( [
			'repository' => $post->post_name,
		], $args );

		// Extract the username and repository from the attributes
		$repository = $args['repository'];

		// Call the method to display the latest release
		ob_start();
		$this->display_latest_release( $repository );
		return ob_get_clean();
	}

	/**
	 * Registers the shortcode.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'latest_release', array( $this, 'shortcode_content' ) );
	}

	/**
	 * Boot the component.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function boot() {

		// Custom columns on the edit portfolio items screen.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Register settings (if any)
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Register shortcode
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}
}
