<?php

/**
 * Part of Fuel Sprockets
 *
 * @package    Fuel Sprockets
 * @version    1.4
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

namespace Sprockets;

use JSMin\JSMin;

/**
 * Sprockets Compiler and Minifier
 *
 * @package     Fuel Sprockets
 */

class Sprockets_Compiler
{
	protected $CssMin,	// CSSMin
		$JsMin, 					// JsMin
		$Coffee, 					// Coffee Compiler
		$Compass, 				// Compass/Sass
		$Less, 						// Less Compiler
		$File;						// File Interface

	protected $config, 	// The Sprockets package config
		$file_path, 			// Current file being processed
		$file_import_dir, // @import fix for Scss
		$minify;

	/**
	 * Initlize the class and create reference to Sprockets_File
	 * @return void
	 */
	public function __construct()
	{
		$this->config = \Config::get("sprockets");

		# When invoked from Cli, DOCROOT doesn't contain public/
		if ( strpos($this->config['asset_compile_dir'], "public/") === FALSE )
		{
			$this->config['asset_compile_dir'] = str_replace(DOCROOT, DOCROOT . "public/", $this->config['asset_compile_dir']);
		}

		# When run from Cli, $config["base_url"] will be NULL
		empty($this->config['base_url']) and $this->config['base_url'] = "/";

		# Minify?
		$this->File = new Sprockets_File();
	}

	/**
	 * Return compiled source from an asset file
	 * @access 	public
	 * @param 	string 	file_path (full)
	 * @param 	bool 		apply minification
	 * @return 	string 	compiled source
	 */
	public function compile($file_path, $minify) {

		$this->minify = $minify;
		$this->file_path = $file_path;
		$this->file_import_dir = dirname($this->file_path);

		# Get the source
		$source = $this->File->read_source($file_path);

		$extension = strtolower( $this->File->get_extension($file_path) );

		# Determine which compiler method to call based on extension
		switch ($extension) {

			case 'js':
				return $this->compile_js( $source );
			break;

			case 'coffee':
				return $this->compile_coffee( $source );
			break;

			case 'scss':
				return $this->compile_scss( $source );
			break;

			case 'less':
				return $this->compile_less( $source );
			break;

			case 'css':
				return $this->compile_css( $source );
			break;
		}
	}

	/**
	 * Return compiled source from a Stylesheet (CSS) file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 */
	protected function compile_css($source) {
		return $this->minify_css($source);
	}

	/**
	 * Return compiled source from a Sass/Compass (SCSS) file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 * @throws 	SprocketsScssCompilerException
	 */
	protected function compile_scss($source) {

		# Initialise the Compass Compiler
		$this->Compass = new \scssc();

		# @import file not found fix
		$this->Compass->addImportPath($this->file_import_dir);

		# Add image-url function
		$this->Compass->registerFunction("image-url", array($this, "image_url"));

		new \scss_compass($this->Compass);

		$sass = "";

		try {

			# Compile the source
			$sass = $this->Compass->compile($source);

		} catch (\Exception $e) {
			throw new SprocketsScssCompilerException($e->getMessage(), 1);
		}

		return $this->minify_css($sass);
	}

	/**
	 * Return compiled source from a LESS file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 * @throws 	SprocketsLessCompilerException
	 */
	protected function compile_less($source) {

		# Init the Less compiler
		$this->Less = new \lessc();

		# Add @import base path
		$this->Less->addImportDir($this->file_import_dir);

		# Add image-url function
		$this->Less->registerFunction("image-url", array($this, "image_url"));

		$less = "";

		try {

			# Compile the source
			$less = $this->Less->compile( $source );

		} catch (\Exception $e) {
			throw new SprocketsLessCompilerException($e->getMessage(), 1);
		}

		return $this->minify_css( $less );
	}

	/**
	 * Return compiled source from a JavaScript file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 */
	protected function compile_js($source) {
		return $this->minify_js( $source );
	}

	/**
	 * Return compiled source from a CoffeeScript file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 * @throws 	SprocketsCoffeeCompilerException
	 */
	protected function compile_coffee($source)
	{
		$options = array_merge(
			array('filename' => $this->file_path),
			$this->config["coffeescript"]
		);

		try {

			$coffee = \CoffeeScript\Compiler::compile(
				$source,
				$options
			);

		} catch (\Exception $e) {
			throw new SprocketsCoffeeCompilerException($e->getMessage());
		}

		return $this->minify_js( $coffee );
	}

	/**
	 * Minifies passed CSS source if $this->minify == true
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	minified source
	 */
	protected function minify_css($source) {

		if ( $this->minify == true )
		{
			$this->CssMin = new \CSSmin();
			return $this->CssMin->run( $source );
		} else {
			return "\n\n/** ". $this->file_path . " **/\n\n" . $source;
		}

	}

	/**
	 * Minifies passed JS source if $this->minify == true
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	minified source
	 */
	protected function minify_js($source) {

		if ( $this->minify == true )
		{
			return JSMin::minify( $source );
		} else {
			return "\n\n/** ". $this->file_path . " **/\n\n;" . $source;
		}

	}

	/**
	 * image-url() function for Sass and Less compilers
	 * Evaluate and copy the references images assets/img/... to public/assets/img/...
	 * and return the correct url
	 *
	 * @access 	public
	 * @param 	array 	arguments
	 * @return 	string 	url("$image_url")
	 * @throws 	SprocketsCssCompilerException
	 */
	public function image_url($args)
	{
		# Scss passes args as array("type", "identifier", "value" => array("img_url") ) but
		# nests another same-structure array into "value" if no identifier is provided --
		# so value must be wrapped in single or double quotes
		if ( count($args) > 1 )
		{
			$params = $args;
		}
		# Less compiler passes args as array("&" => array("type", "identifier", "value"))
		# Will throw Exception if value not in quotes
		else
		{
			$params = $args[0];
		}

		list($type, $identifier, $value) = $params;

		if ( empty($value[0]) )
		{
			throw new SprocketsCssCompilerException("Empty argument for function `image-url()` in " . $this->file_path, 1);
		}

		# The image name
		$image 				= str_replace( array("\"", "'"), "", trim($value[0]) );

		# Map the file to be copied
		$origin 			= $this->config["asset_root_dir"] . $this->config["img_dir"] . $image;
		# map the destination
		$destination 	= $this->config["asset_compile_dir"] . $this->config["img_dir"] . $image;

		$this->File->copy_file($origin, $destination);

		# Should return assets/
		$asset_dir = str_replace( array(DOCROOT . "public/", DOCROOT), "", $this->config["asset_compile_dir"]);

		$image_url = $this->config["base_url"] . $asset_dir . $this->config["img_dir"] . $image;

		return "url(\"$image_url\")";
	}
}

class SprocketsCssCompilerException extends \FuelException {}
class SprocketsJsCompilerException extends \FuelException {}
class SprocketsCoffeeCompilerException extends \FuelException {}
class SprocketsScssCompilerException extends \FuelException {}
class SprocketsLessCompilerException extends \FuelException {}
