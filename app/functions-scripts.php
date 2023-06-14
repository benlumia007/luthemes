<?php
/**
 * Default scripts functions
 *
 * @package   Creativity
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://luthemes.com/portfolio/creativity
 */

namespace Creativity;
use function Backdrop\Mix\Manifest\childAsset;

/**
 * Enqueue Scripts and Styles
 *
 * @since  1.0.0
 * @access public
 * @return void
 *
 * @link   https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @link   https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 */

add_action( 'wp_enqueue_scripts', function() {

	// Rather than enqueue the main style.css stylesheet, we are going to enqueue screen.css.
	wp_enqueue_style( 'succotash-screen', childAsset( 'assets/css/screen.css' ), null, null );

	// Enqueue theme scripts
	wp_enqueue_script( 'succotash-app', childAsset( 'assets/js/app.js' ), [ 'jquery' ], null, true );
} );

add_filter( 'http_request_args', 'xxx_git_auth', 10, 2 );
function xxx_git_auth( $args, $url ) {
	// Check if the URL matches the expected pattern
	if ( ! str_starts_with( $url, 'https://github.com/benlumia007/luthemes' ) ) {
		return $args;
	}

	// Set the authentication token
	$token = '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';
	$args['headers']['Authorization'] = 'Token ' . $token;

	// Set the user agent header
	$args['headers']['User-Agent'] = classicpress_user_agent();

	return $args;
}
