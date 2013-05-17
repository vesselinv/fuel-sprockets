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
 * The Sprockets Parser is responsible for parsing and discovery of required files
 *
 * @package     Fuel Sprockets
 */

class Sprockets_Parser extends Sprockets_Cache
{
	protected $cache_dir,  	// The Sprockets Cache Directory
		$cache_js_dir,				// Javascripts folder inside Sprockets Cache Directory
		$cache_css_dir,				// Stylesheets folder inside Sprockets Cache Directory
		$asset_root_dir,			// The Asset Directory
		$asset_compile_dir,		// Asset Compile Directory
		$js_dir,							// Javascripts subdirectory
		$css_dir,							// Stylesheets subdirectory
		$force_minify,				// Force Minify if Fuel::$env !== "production"
		$minify = true,				// To minify or not to minify?
		$minify_flag = "";		// Flag appended to minified files - .min

	protected $File, $Compiler;

	protected $JS_TAG 	= '<script src="{FILE}" type="text/javascript"></script>';
	protected $CSS_TAG  = '<link rel="stylesheet" href="{FILE}">';

	/**
	 * Parse the config and initialize object instance and referenced classes
	 *
	 * @return  void
	 */
	public function __construct($config)
	{
		$this->cache_dir 					= $config['cache_dir'];

		$this->asset_root_dir 		= $config['asset_root_dir'];
		$this->asset_compile_dir 	= $config['asset_compile_dir'];
		$this->js_dir							= $config['js_dir'];
		$this->css_dir						= $config['css_dir'];
		$this->force_minify				= $config['force_minify'];

		$this->cache_js_dir 			= $this->cache_dir . $this->js_dir;
		$this->cache_css_dir 			= $this->cache_dir . $this->css_dir;

		// Check if cache dirs exist, Create them if they don't
		is_dir($this->cache_dir) or mkdir($this->cache_dir);
		is_dir($this->cache_js_dir) or mkdir($this->cache_js_dir);
		is_dir($this->cache_css_dir) or mkdir($this->cache_css_dir);

		// Minify or not?
		strtolower(\Fuel::$env) !== "production" and $this->minify = false;
		$this->force_minify == true and $this->minify = true;
		$this->minify == true and $this->minify_flag = ".min";

		$this->File 							= new Sprockets_File();
		$this->Compiler 					= new Sprockets_Compiler();
	}

	/**
	 * Parse the requested file
	 * @access protected
	 * @param string filename
	 * @param string asset_dir
	 * @return array sprockets_include_tag
	 */
	public function parse($file, $asset_dir) {

		$file_path 	= $this->asset_root_dir . $asset_dir . $file;

		$source 		= $this->File->read_source($file_path);
		$subfiles 	= $this->parse_directives($source, $asset_dir);

		# Clean up and flatten
		$subfiles 	= \Arr::flatten($subfiles, '_');
		$subfiles		= array_filter($subfiles, function($item){
			return ! empty($item);
		});
		$file_list 	= array_merge(array_unique($subfiles), array($file_path));

		return $this->process_files($file_list, $file);

	}

	/**
	 * Parse all directives inside the requested file and all files to be included
	 * @access protected
	 * @param string source
	 * @param string directory
	 * @return array filepaths_to_include
	 */
	protected function parse_directives($source, $asset_dir) {

		$directory = $this->asset_root_dir . $asset_dir;

		$filepaths_to_include = array();

		/**
		 * Match the following directives
		 * 	//=
		 *	#=
		 *	*=
		 */
		preg_match_all('/(\/\/=|#=|\*=) ([a-z_]+) ([^\n]+)/', $source, $matches);

		foreach($matches[0] as $key => $match) {
			$directive = $matches[2][$key];
			$parameter = str_replace(array('\'', '"'), '', $matches[3][$key]); # Strip off single and double quotes

			switch ($directive) {

				case 'require':

					# Are we dealing with a local or remote file?
					if ( ! $this->match_remote_url($parameter) )
					{
						// Recursively add subfiles
						$source = $this->File->read_source($directory . $parameter);
						$subfiles = $this->parse_directives($source, $asset_dir);

						foreach ($subfiles as $key => $value) {
							$filepaths_to_include[] = $this->d_require($value);
						}

						$filepaths_to_include[] = $this->d_require($directory . $parameter);
					}
					else
					{
						# The file is remote
						$filepaths_to_include[] = $this->d_require($parameter);
					}
					
				break;

				case 'require_directory':
					$filepaths_to_include[] = $this->d_require_directory($directory . $parameter, $asset_dir, false);
				break;

				case 'require_tree':
					$filepaths_to_include[] = $this->d_require_directory($directory . $parameter, $asset_dir, true);
				break;
			}
			
		}

		return $filepaths_to_include;

	}

	/**
	 * Return filename for the "require" directive
	 * @access 	protected
	 * @param 	string file_path
	 * @return 	string file_path
	 */
	protected function d_require($file_path) {
		return $file_path;
	}

	/**
	 * Return array of filepaths based on "require_directory" and "require_tree" directives
	 * @access 	protected
	 * @param 	string 	dir_path
	 * @param 	string 	asset_dir
	 * @param 	bool 		recursively add files
	 * @return 	string 	processed_source
	 */
	protected function d_require_directory($dir_path, $asset_dir, $recursive = false) {
		# Get initial scan of the directory
		$files = $this->File->get_files_in_dir($dir_path, $recursive);

		# Files may be in a subdirectory of a subdirectory of the current asset_dir
		$dir_diff = substr($dir_path, strrpos($dir_path, $asset_dir) + strlen($asset_dir));

		$subfiles = array();

		foreach ($files as $key => $file) {
			$source = $this->File->read_source($file);
			$subfiles[] = $this->parse_directives($source, $asset_dir . $dir_diff);
		}

		return array_merge($files, $subfiles);
	}
}