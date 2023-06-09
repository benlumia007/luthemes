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
	 * @return string
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
			esc_html__( 'GitHub',           'backdrop-custom-portfolio' ),
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
	function register_settings() {

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
		if ( isset( $_POST['save_github_repos'] ) && wp_verify_nonce( $_POST['save_github_repos_nonce'], 'save_github_repos' ) ) {
			// Handle saving logic here
			$repos_to_save = $_POST['selected_repos'];
			update_option( 'github_repos', $repos_to_save );
			echo '<div class="notice notice-success"><p>GitHub repositories saved successfully!</p></div>';
		}

		// Retrieve saved repositories from the options
		$saved_repos = get_option( 'github_repos', array() );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'GitHub Repositories', 'backdrop-custom-portfolio' ); ?></h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'save_github_repos', 'save_github_repos_nonce' ); ?>

				<?php
				// Retrieve repositories using the GitHub API
				$github_repos = $this->get_github_repos();

				foreach ( $github_repos as $theme ) {

				}

				if ( $github_repos ) {

				} else {
					echo '<p>No repositories found.</p>';
				}
				?>

				<p>
					<button type="submit" name="save_github_repos" class="button button-primary">Save Repositories</button>
				</p>
			</form>
		</div><!-- wrap -->
		<?php }

	/**
	 * Retrieves repositories using the GitHub API.
	 *
	 * @return array|bool Array of repositories or false on failure.
	 */
	public function get_github_repos() {
		$posts = [
			'post_type' => 'portfolio',
		];

		$portfolio = get_posts( $posts );

		foreach ( $portfolio as $item ) {
			$slug = $item->post_name;

			$args = [
				'headers' => [
					'Authorization' => 'Bearer ' . GITHUB_API_TOKEN,
				]
			];

			$github_api_url = 'https://api.github.com/repos/luthemes/' . $slug . '/releases';

			// Retrieve repositories using the GitHub API (example code)
			$response = wp_remote_get( $github_api_url, $args );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$repos = wp_remote_retrieve_body( $response );
			return json_decode( $repos, true );
		}
	}

	public function boot() {

		// Custom columns on the edit portfolio items screen.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
}
