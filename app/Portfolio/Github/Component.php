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
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function register_settings() {
		// Register your plugin settings here (if any)
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
			update_option( 'github_api_token', $api_token );
			echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
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
			<h1>Latest GitHub Releases</h1>

			<?php
			$repositories = array(
				array(
					'username' => 'luthemes',
					'repository' => 'creativity'
				),
				array(
					'username' => 'luthemes',
					'repository' => 'silver-quantum'
				),
				// Add more repositories here
			);

			foreach ( $repositories as $repo ) {
				$this->display_latest_release( $repo['username'], $repo['repository'] );
			}
			?>

		</div>
		<?php
	}

	public function display_latest_release( $username, $repository ) {
		$api_token = get_option( 'github_api_token' );

		if ( empty( $api_token ) ) {
			echo '<div class="notice notice-error"><p>Please set the GitHub API token in the settings.</p></div>';
			return;
		}

		$releases_url = "https://api.github.com/repos/$username/$repository/releases/latest";

		$response = wp_remote_get( $releases_url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_token,
				'Accept' => 'application/vnd.github.v3+json'
			)
		) );

		if ( is_wp_error( $response ) ) {
			echo '<div class="notice notice-error"><p>Error retrieving latest release from GitHub API for repository: ' . $repository . '</p></div>';
			return;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $release ) ) {
			echo '<div class="notice notice-info"><p>No releases found for repository: ' . $repository . '</p></div>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<tbody>';
			echo '<tr>';
			echo '<td><strong>Release:</strong></td>';
			echo '<td>' . $release->tag_name . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><strong>Published Date:</strong></td>';
			echo '<td>' . date( 'Y-m-d', strtotime( $release->published_at ) ) . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><strong>Download:</strong></td>';
			echo '<td>';
			if ( ! empty( $release->assets ) ) {
				$latest_asset = $release->assets[0];
				echo '<a href="' . $latest_asset->browser_download_url . '">Download</a>';
			} else {
				echo 'N/A';
			}
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
		}
	}

	/**
	 * Renders the shortcode content.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string
	 */
	public function shortcode_content( $atts ) {
		global $post;

		// Process the shortcode attributes
		$atts = shortcode_atts( array(
			'username' => 'luthemes',
			'repository' => $post->post_name,
		), $atts );

		// Extract the username and repository from the attributes
		$username = $atts['username'];
		$repository = $atts['repository'];



		// Call the method to display the latest release
		ob_start();
		$this->display_latest_release( $username, $repository );
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
