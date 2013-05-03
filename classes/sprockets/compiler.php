<?php

/**
 * Part of Fuel Sprockets
 *
 * @package    Fuel Sprockets
 * @version    1.0
 * @author     Veselin Vasilev @vesselinv
 * @license    MIT License
 * @copyright  2013 Veselin Vasilev
 * @link       http://vesselinv.com/fuel-sprockets
 */

namespace Sprockets;

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

	protected $file_path, $minify;

	/**
	 * Initlize the class and create reference to Sprockets_File
	 * @return void
	 */
	public function __construct()
	{
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
	 */
	protected function compile_scss($source) {

		# Initialise the Compass Compiler
		$this->Compass = new \scssc();
		new \scss_compass($this->Compass);

		# Compile the source
		$sass = $this->Compass->compile($source);

		return $this->minify_css($sass);
	}

	/**
	 * Return compiled source from a LESS file
	 * @access 	public
	 * @param 	string 	source
	 * @return 	string 	compiled source
	 */
	protected function compile_less($source) {
		$this->Less = new \lessc();

		$less = $this->Less->compile( $source );
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
	 */
	protected function compile_coffee($source)
	{
		$coffee = \CoffeeScript\Compiler::compile( $source , array('filename' => $this->file_path));
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
			return \JSMin::minify( $source );
		} else {
			return "\n\n/** ". $this->file_path . " **/\n\n" . $source;
		}

	}
}