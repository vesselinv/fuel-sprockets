<?php

/**
 * Fuel Sprockets
 *
 * A smarter asset manager for FuelPHP closely following Sprockets/Asset Pipeline.
 * Provide directives inside your Js, Css, Less, Sass/Compass, CoffeeScript files
 * and Fuel-Sprockets will combine, compile and minify all, in order of importance, 
 * and generate an include tag to the compiled sprockets file. Uses smart caching in
 * order to avoid unnecessary recompilation
 *
 * @package    Fuel Sprockets
 * @version    1.0
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade Fuel Sprockets without losing your custom config.
 */

return array(
	/**
	 * asset_root_dir - Home for your development assets
	 * MUST contain a trailing slash (/)
	 *
	 * The folder where you'll write and keep your assets under development
	 * Recommended is under fuel/assets/js and fuel/assets/css so that they're not
	 * publicly accessible.
	 */
	'asset_root_dir' 				=> APPPATH . 'assets/',

	/**
	 * asset_compile_dir - Stores your compiled bundle files
	 * MUST contain a trailing slash (/)
	 *
	 * The directory where your bundle files will be compiled. Must be writable and
	 * publicly accessible. Must also start with DOCROOT
	 */
	'asset_compile_dir' 		=> DOCROOT . 'assets/',

	/**
	 * cache_dir - Directory dedicated to cached files
	 * MUST contain a trailing slash (/)
	 *
	 * Where should Sprockets store cached files?
	 */
	'cache_dir'							=> APPPATH . 'cache/sprockets/',

	/**
	 * js_dir - Subdirectory name for javascripts and coffeescripts
	 * MUST contain a trailing slash (/)
	 *
	 * The subdirectory for JavaScript and CoffeeScript files inside `asset_root`,
	 * `asset_compile` and `cache_dir`
	 */
	'js_dir' 								=> 'js/',

	/**
	 * css_dir - Subdirectory name for css, scss and less
	 * MUST contain a trailing slash (/)
	 *
	 * The subdirectory for CSS, SCSS and Less files inside `asset_root`,
	 * `asset_compile` and `cache_dir`
	 */
	'css_dir'								=> 'css/',

	/**
	 * force_minify - Always minify bundles
	 * MUST contain a trailing slash (/)
	 *
	 * Minification is applied automatically in production mode. Shall we force it in
	 * any other environment as well?
	 */
	'force_minify'					=> false
);