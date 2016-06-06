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

/**
 * The File class allows for working with files and directories
 *
 * @package     Fuel Sprockets
 */

class Sprockets_File
{
	// What file types should be attepted to be parsed
	protected	$accepted_file_types = array(
		'js', 'coffee', 'css', 'scss', 'less'
	);

	function __construct() {}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string file source
	 */
	public function read_source($file)
	{
		$path = trim($file);

		$source = file_get_contents($path);

		if ( $source !== false )
		{
			return $source;
		} else {
			throw new SprocketsFileNotFoundException("File $file could not be found.", 1);
		}
	}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @param 	string source
	 * @return 	void
	 * @throws	SprocketsFileException
	 */
	public function save_file($file_path, $source)
	{
		$path = trim($file_path);

		if (!is_dir(dirname($path))) {
			// dir doesn't exist, make it
			mkdir(dirname($path), 0755, true);
		}

		$successful = (file_put_contents($path, $source) !== false);

		if ( !$successful ) {
			throw new SprocketsFileException("$file_path could not be saved. Do you have write permissions?", 1);
		}
	}

	/**
	 * Copy a file to a new destination
	 * @access  public
	 * @param 	string 	origin
	 * @param 	string 	destination
	 * @return 	void
	 * @throws 	SprocketsFileNotFoundException|SprocketsFileException
	 */
	public function copy_file($path, $new_path)
	{
		$origin 			= trim($path);
		$destination 	= trim($new_path);

		$replace 			= false;
		$skip 				= false;

		if ( ! is_file($origin) )
		{
			throw new SprocketsFileNotFoundException("File $origin does not exist.", 1);
		}

		# What if the destination already exist?
		if ( is_file($destination) )
		{
			# Compare the mod times for both origin and destination files
			$origin_mtime 			= $this->get_filemtime($origin);
			$destination_mtime 	= $this->get_filemtime($destination);

			# Replace existing destination file
			if ( $origin_mtime > $destination_mtime )
			{
				$replace 	= true;
			}
			else
			{
				$skip 		= true;
			}
		}

		# Destination could be in a subdirectory that doesn't exist yet
		$destination_basedir = dirname($destination);

		! is_dir($destination_basedir)
			and mkdir($destination_basedir);

		# Carry out the file operations
		try {

			if ( $replace == true )
			{
				\File::delete($destination);
			}

			if ( $skip == false )
			{
				\File::copy($origin, $destination);
			}

		} catch (\Exception $e) {
			throw new SprocketsFileException($e->getMessage(), 1);
		}

	}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	string extension
	 */
	public function get_extension($file_path)
	{
		$path = trim($file_path);

		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * @access 	public
	 * @param 	string $file_path
	 * @return 	string md5 of the file contents
	 */
	public function get_file_md5($file_path)
	{
		$path = trim($file_path);

		if ( ! is_file($path) ) {
			throw new SprocketsFileNotFoundException("Could not get File MD5 for $path", 1);
		}
		return md5_file($path);
	}

	/**
	 * @access 	public
	 * @param 	string filepath
	 * @return 	int last modified time in UTC
	 */
	public function get_filemtime($file_path)
	{
		$path = trim($file_path);

		if ( ! is_file($path) ) {
			throw new SprocketsFileNotFoundException("Could not get Last Modified Date for $path", 1);
		}
		return filemtime($path);
	}

	/**
	 * @access 	public
	 * @param 	string url
	 * @return 	int last modified time in UTC
	 */
	public function get_remotemtime($url)
	{
		$h = get_headers(trim($url), 1);

		if ( ! empty($h) && strstr($h[0], '200') !== FALSE ) {
		    return strtotime($h['Last-Modified']);
		} else {
			throw new SprocketsFileException("Could not get Last Modified Date for $url", 1);

		}
	}

	/**
	 * @access 	public
	 * @param 	string directory path
	 * @return 	array  list of full file paths
	 */
	public function get_files_in_dir($dir_path, $recursive = false) {

		# Add traling slash
		$folder = $this->fix_trailing_slash($dir_path);

		$types = array();
		foreach ($this->accepted_file_types as $key => $value) {
			$type["\.". $value. "$"] = "file";
		}

		$scan = array();
		try {

			# Scan entire directory
			$scan = \File::read_dir(
				$folder,
				( $recursive == false ? 1 : 0 ),
				# Exclude files/dirs that start with . and allow accepted filetypes only
				array_merge( array('!^\.', '!^_'), $types)
			);

		} catch (\Exception $e) {
			throw new SprocketsFileException($e->getMessage(), 1);
		}

		$file_list 	= \Arr::flatten( $this->dir_scan_iterator($scan, $folder) );

		# Remove duplicates
		return array_unique($file_list);
	}

	/**
	 * @access 	public
	 * @param 	array 	scan
	 * @param 	string 	base directory path
	 * @return 	array  	list of full file paths
	 */
	public function dir_scan_iterator($scan, $base_dir)
	{
		$list = array();

		foreach ($scan as $key => $value) {
			if ( ! is_array($value) )
			{
				$file = $base_dir . $value;
				is_file($file)
				 and $list[] = $file;
			}
			else
			{
				$list[] = $this->dir_scan_iterator($value, $base_dir . $key);
			}
		}

		return \Arr::flatten( $list );
	}

	/**
	 * Add traling slash to end a folderpath if not present
	 * @access 	public
	 * @param 	string directory path
	 * @return 	string directory path
	 */
	public function fix_trailing_slash($input) {

		$input = rtrim($input, ".");
		substr($input, -1) !== "/" and $input = $input . "/";

		return $input;
	}
}

class SprocketsFileNotFoundException extends \FuelException {}
class SprocketsFileException extends \FuelException {}
