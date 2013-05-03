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

return array(
	'asset_root_dir' 				=> APPPATH . 'assets/',
	'asset_compile_dir' 		=> DOCROOT . 'assets/',
	'cache_dir'							=> APPPATH . 'cache/sprockets/',
	'js_dir' 								=> 'js/',
	'css_dir'								=> 'css/',
	'force_minify'					=> false
);