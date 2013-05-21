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
 * Sprockets Smart Caching
 *
 * @package     Fuel Sprockets
 */

class Sprockets_Cache
{
	protected $sprockets_filename,				// Only the filename of the final Sprockets file
						$sprockets_file_full_path,	// Full path to the Spockets file
						$sprockets_file_timestamp,	// Sprockets file Last Mod Date in UTC
						$file_asset_dir,						// Asset Dir of the file
						$file_cache_dir,						// Cache Dir of the file
						$include_tag;								// Html include tag

	/**
	 * Process files in passed array of filepaths
	 * @access protected
	 * @param  array filename_list
	 * @param  string requested_file
	 * @return string sprockets_include_tag
	 */
	protected function process_files($filename_list, $requested_file) {

		# Must account for more than one dot in a filename
		$filename 	= substr($requested_file, 0, strrpos($requested_file, "."));
		$ext 				= strtolower( substr($requested_file, strrpos($requested_file, '.') + 1) );

		# Prepare file asset dir, cache dir and include tag
		# based on extension
		switch ($ext) {
			case 'js':
			case 'coffee':
				$ext = "js";
				$this->file_asset_dir = $this->js_dir;
				$this->file_cache_dir = $this->cache_js_dir;
				$this->include_tag 		= $this->JS_TAG;
			break;

			case 'css':
			case 'scss':
			case 'less':
				$ext = "css";
				$this->file_asset_dir = $this->css_dir;
				$this->file_cache_dir = $this->cache_css_dir;
				$this->include_tag 		= $this->CSS_TAG;
			break;
		}

		$mod_dates = array();	# Store only mod_dates
		$file_list = array(); # Store both mod_date and path for each file

		# Get modification times of all files
		foreach ($filename_list as $i => $file) {

			if ( ! $this->match_remote_url($file))
			{
				$mod_date = $this->File->get_filemtime($file); # Local file
			}
			else 
			{
				$mod_date = $this->File->get_remotemtime($file); # Remote file
			}

			$mod_dates[] = $mod_date;
			$file_list[] = array(
				"path" => $file,
				"mod_date" => $mod_date
			);
		}

		$this->sprockets_file_timestamp = md5(implode('', $mod_dates));

		$this->sprockets_filename 			= 
			$filename . "_" . $this->sprockets_file_timestamp . $this->minify_flag . "." . $ext;

		$this->sprockets_file_full_path = 
			$this->asset_compile_dir . $this->file_asset_dir . $this->sprockets_filename;

		if ( file_exists($this->sprockets_file_full_path) ) {
			return $this->generate_include_tag();
		} else {
			return $this->reprocess_files($file_list);
		}

	}

	/**
	 * Iterate over modified files referenced in manifest
	 * @access protected
	 * @param  array file_list
	 * @return string sprockets_include_tag
	 */
	protected function reprocess_files($filename_list) {

		$compiled_source = "";
		$mod_date;

		foreach ($filename_list as $i => $file) {

			$mod_date = $file["mod_date"];

			# For local files
			if ( ! $this->match_remote_url($file["path"]) )
			{
				$file_path 				= $file["path"];
				$file_path_parts 	= explode($this->file_asset_dir, $file_path);
				$relative_path 		= $file_path_parts[1];

				$filename = substr($relative_path, 0, strrpos($relative_path, "."));
				$filename = str_replace("/", "-", $filename);

				$ext = substr($relative_path, strrpos($relative_path, '.') + 1);

				$expected_cached_file = 
					$this->file_cache_dir . $filename . "_" . $mod_date . $this->minify_flag . "." . $ext;
			}
			else
			{
				# For remote files
				$filename = str_replace(array(":", "/"), "-", $file["path"]);

				$expected_cached_file = $this->file_cache_dir . $filename;
			}

			# Pull up the file or recompile it if does not exist
			if ( file_exists($expected_cached_file) ) {
				
				$compiled_source .= $this->File->read_source($expected_cached_file);

			} else {

				$source = $this->Compiler->compile($file["path"], $this->minify);
				$save = $this->File->save_file($expected_cached_file, $source);

				# Call GC
				$this->remove_stale_files(str_replace($mod_date, "*", $expected_cached_file), $expected_cached_file);

				$compiled_source .= $source;
			}
		}

		$this->File->save_file($this->sprockets_file_full_path, $compiled_source);
		
		# Call GC
		$this->remove_stale_files(str_replace($this->sprockets_file_timestamp, "*", $this->sprockets_file_full_path), $this->sprockets_file_full_path);

		return $this->generate_include_tag();
	}

	/**
	 * Returns the include tag pointing to the generated Sprockets file
	 * @access protected
	 * @param  null
	 * @return string sprockets_include_tag
	 */
	protected function generate_include_tag() {
		$asset_dir = str_replace(DOCROOT, "", $this->asset_compile_dir);

		$file_path = $this->base_url . 
			$asset_dir . 
			$this->file_asset_dir . 
			$this->sprockets_filename;

		return str_replace("{FILE}", $file_path, $this->include_tag);
	}

	/**
	 * Garbage Collection for stale files
	 * @access protected
	 * @param  string glob_pattern
	 * @param  string file_to_keep
	 * @return void
	 */
	protected function remove_stale_files($glob_pattern, $file_to_keep) {
		$files = glob($glob_pattern);

		foreach ($files as $i => $path) {
			$path !== $file_to_keep and unlink($path);
		}
	}

	/**
	 * Check if required file is an external source
	 * @access protected
	 * @param  string url
	 * @return bool
	 */
	protected function match_remote_url($url)
	{
		preg_match_all ('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,\!]+)/si', $url, $urls);
		return ! empty($urls[1]);
	}

}