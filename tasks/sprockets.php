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
  oil r sprockets:js application.js/coffee
  oil r sprockets:css application.css/scss/less

Filepaths must reside within the Asset Root defined in your config,
which by default is fuel/app/assets/js and fuel/app/assets/css respectively
EOL;
		\Cli::write($output);
	}

	/**
	 * Invoke Js Bundle through CLI
	 * Usage: $ php oil r sprockets:js application.js/coffee
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string
	 */
	public static function js($file = null)
	{
		if ( ! empty($file) )
		{
			$result = static::$sprockets->js($file);

			$doc = new \DOMDocument();
			$doc->loadHTML($result);
			$node = $doc->getElementsByTagName( "script" ); 
			foreach ($node as $node) {
				$bundle_file = $node->getAttribute("src");
			}

			\Cli::write( "Bundle file " . basename(DOCROOT . $bundle_file) . " was created for " . $file );
			exit();

		} else {
			return "No filepath specified.";
		}
	}

	/**
	 * Invoke Css Bundle through CLI
	 * Usage: $ php oil r sprockets:css application.css/scss/less
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string
	 */
	public static function css($file = null)
	{
		if ( ! empty($file) )
		{
			$result = static::$sprockets->css($file);

			$doc = new \DOMDocument();
			$doc->loadHTML($result);
			$node = $doc->getElementsByTagName( "link" ); 
			foreach ($node as $node) {
				$bundle_file = $node->getAttribute("href");
			}

			\Cli::write( "Bundle file " . basename(DOCROOT . $bundle_file) . " was created for " . $file );
			exit();

		} else {
			return "No filepath specified.";
		}
	}
}

/* End of file tasks/sprockets.php */