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

namespace Fuel\Tasks;

/**
 * Precompile Oil Task for Fuel Sprockets
 *
 * @package		Fuel Sprockets
 * @version		1.0
 * @author		Veselin Vasilev
 */

class Sprockets
{
	protected static $sprockets;

	/**
	 * Initialize class and forge a Sprockets Instance
	 */
	public function __construct()
	{
		! \Package::loaded('sprockets') and \Package::load('sprockets');

		// When in console mode, DOCROOT is the path to the project root, not the public/
		$config = array(
			'asset_compile_dir' => DOCROOT . 'public/assets/',
			'force_minify'			=> true
			);

		static::$sprockets = \Sprockets::forge('default', $config);
	}

	/**
	 * Show Help
	 * Usage: $ php oil r sprockets
	 * @access 	public
	 * @param 	none
	 * @return 	string
	 */
	public static function run()
	{
		return static::help();
	}

	/**
	 * Show Help
	 * Usage: $ php oil r sprockets
	 * @access 	public
	 * @param 	none
	 * @return 	string
	 */
	public static function help()
	{
		$output = <<<EOL
Usage:
	oil r sprockets:compile app.coffee app.scss
  oil r sprockets:js application.js/coffee
  oil r sprockets:css application.css/scss/less

Filepaths must reside within the Asset Root defined in your config,
which by default is fuel/app/assets/js and fuel/app/assets/css respectively
EOL;
		\Cli::write($output);
	}

	/**
	 * Precompile Assets
	 * Usage: $ php oil r sprockets:compile app.js app.css
	 * @access 	public
	 * @param 	string filepaths
	 * @return 	string
	 */
	public static function compile($file = null)
	{
		$files = func_get_args();

		if ( count($files) < 1 )
		{
			return "No filepath(s) specified.";
		}

		$result = array();

		foreach ($files as $i => $file) {
			$ext = strtolower( substr($file, strrpos($file, '.') + 1) );

			switch ($ext) {
				case 'js':
				case 'coffee':
					$result[] = static::$sprockets->js($file, true);
					break;

				case 'css':
				case 'scss':
				case 'less':
					$result[] = static::$sprockets->css($file, true);
					break;
			}
		}

		$generated = array_map(function($item){
			return basename(DOCROOT . $item);
		}, $result);

		\Cli::write("\nThe following bundles were generated:\n\n" . implode("\n", $result) . "\n");
	}

	/**
	 * Invoke Js Bundle through CLI
	 * Usage: $ php oil r sprockets:js application.js/coffee
	 *
	 * TO BE DEPRECATED
	 *
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string
	 */
	public static function js($file = null)
	{
		if ( ! empty($file) )
		{
			$result = static::$sprockets->js($file, true);

			\Cli::write( "Bundle file " . basename(DOCROOT . $result) . " was created for " . $file );
			exit();

		} else {
			return "No filepath specified.";
		}
	}

	/**
	 * Invoke Css Bundle through CLI
	 * Usage: $ php oil r sprockets:css application.css/scss/less
	 *
	 * TO BE DEPRECATED
	 *
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string
	 */
	public static function css($file = null)
	{
		if ( ! empty($file) )
		{
			$result = static::$sprockets->css($file, true);

			\Cli::write( "Bundle file " . basename(DOCROOT . $result) . " was created for " . $file );
			exit();

		} else {
			return "No filepath specified.";
		}
	}
}

/* End of file tasks/sprockets.php */
