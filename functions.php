<?php
/**
 * Default functions template
 *
 * This file is used to bootstrap the theme.
 *
 * @package   Succotash
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/luthemes.com
 */

/** ------------------------------------------------------------------------------------------
 * Load composer files.
 * -------------------------------------------------------------------------------------------
 * Please load the composer files first to ensure that any classes or functions that we may
 * require are available through autoload.
 */

if ( file_exists( get_theme_file_path( '/vendor/autoload.php' ) ) ) {
	require_once get_theme_file_path( '/vendor/autoload.php' );
}

# ------------------------------------------------------------------------------
# Autoload functions files.
# ------------------------------------------------------------------------------
#
# Load any functions-files from the `/app` folder that are needed. Add additional
# files to the array without the `.php` extension.

array_map( function( $file ) {
	require_once get_theme_file_path( "/app/{$file}.php" );
}, [
	'CodePotent/UpdateClient',
	'framework',
	'functions-helpers',
	'functions-scripts'
] );

// delete_transient( 'succotash_portfolio_theme_creativity' );
// delete_transient( 'succotash_portfolio_theme_silver-quantum' );

