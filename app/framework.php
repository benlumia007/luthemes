<?php
/**
 * Boot the Framework
 *
 * @package   Succotash
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/luthemes.com
 */

/** ------------------------------------------------------------------------------------------
 * Create a new application.
 * -------------------------------------------------------------------------------------------
 *
 * This code creates the one true instance of the Backdrop Core Application, which can be access
 * via the `Backdrop\app()`function or the `Backdrop\App` static class after the application has
 * been booted.
 */

$succotash = Backdrop\booted() ? Backdrop\app() : new Backdrop\Core\Application();

/** ------------------------------------------------------------------------------------------
 * Register default service providers with the application.
 * -------------------------------------------------------------------------------------------
 *
 * Here are the default service providers that are essential for the theme to function before
 * booting the application. These service providers form the foundation for the theme.
 */
$succotash->provider( Backdrop\Mix\Provider::class );
$succotash->provider( Succotash\Portfolio\test\Provider::class );
// $succotash->provider( Succotash\Portfolio\Meta\Provider::class );
$succotash->provider( Succotash\Portfolio\Taxonomy\Provider::class );
// $succotash->provider( Succotash\Portfolio\Widget\Provider::class );

/** ------------------------------------------------------------------------------------------
 * Register additional service providers for the theme.
 * -------------------------------------------------------------------------------------------
 *
 * These are the additional providers that are crucial for the theme to operate before booting
 * the application. These service providers offer supplementary features to the theme.
 */

/** ------------------------------------------------------------------------------------------
 * Boot the application.
 * -------------------------------------------------------------------------------------------
 *
 * The code invokes the `boot()` method of the application, which initiates the launch of the
 * application. Congratulations on a job well done!
 */

$succotash->boot();
