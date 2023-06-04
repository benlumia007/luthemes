/**
 * Primary front-end script.
 *
 * Primary JavaScript file. Any includes or anything imported should be filtered through this file
 * and eventually saved back into the `/assets/js/app.js` file.
 *
 * @package   Succotash
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2014-2022 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/luthemes.com
 */

/**
 * A simple immediately-invoked function expression to kick-start
 * things and encapsulate our code.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
( function( $ ) {
	$( '.data-item' ).click( function() {
		const value = $( this ).attr( 'data-filter' );

		if ( value == 'all' ) {
			$( '.portfolio-item' ).show();
		} else {
			$( '.portfolio-item' ).not( '.' + value ).hide();
			$( '.portfolio-item' ).filter( '.' + value ).show();
		}
	} )

	$( '.data-item' ).click( function() {
		$( this ).addClass( 'active' ).siblings().removeClass( 'active' );
	} );
} )( jQuery );
