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

use Twig_Extension,
	Twig_Function_Function;

/**
 * Provides Twig support for sprockets functions
 */
class Sprockets_Twig_Extension extends Twig_Extension
{
	/**
	 * Gets the name of the extension.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'sprockets';
	}

	/**
	 * Sets up all of the functions this extension makes available.
	 *
	 * @return  array
	 */
	public function getFunctions()
	{
		return array(
			'sprockets_js' => new Twig_Function_Function('Sprockets::js'),
			'sprockets_css' => new Twig_Function_Function('Sprockets::css')
		);
	}
}
