<?php

/**
 * Fuel Sprockets
 *
 * Adds the Sprockets Twig extension to the parser
 */

return array(

	// TWIG ( http://www.twig-project.org/documentation )
	// ------------------------------------------------------------------------
	'View_Twig' => array(
		'extensions' => array(
			'Sprockets\\Sprockets_Twig_Extension',
		),
	),

);