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

/**
 * Test_Sprockets
 *
 * @group Core
 */
class Test_Sprockets extends \PHPUnit_Framework_TestCase
{
	protected $config = array();

	/**
	 * Pass in test-friendly configs
	 */
	public function __construct()
	{
		$this->config = array(
      # Test without minification
      array(
  			'asset_root_dir' 				=> __DIR__ . '/assets/root/',
  			'asset_compile_dir' 		=> __DIR__ . '/assets/compiled/',
  			'cache_dir'							=> __DIR__ . '/assets/cache/',
  			'js_dir' 								=> 'js/',
  			'css_dir'								=> 'css/',
  			'force_minify'					=> false
  		),
      # Test with minification
      array(
        'asset_root_dir'        => __DIR__ . '/assets/root/',
        'asset_compile_dir'     => __DIR__ . '/assets/compiled/',
        'cache_dir'             => __DIR__ . '/assets/cache/',
        'js_dir'                => 'js/',
        'css_dir'               => 'css/',
        'force_minify'          => true
      )
    );

		! Package::loaded('sprockets') and Package::load('sprockets');
	}

  /**
   * Test the Sprockets package with a separate file for each compiler and directive, non-minified and minified
   */
  public function test_sprockets()
  {
    for ($i=0; $i < count($this->config); $i++) { 
      $sprockets  = Sprockets::forge('Test_Sprockets' . $i, $this->config[$i]);

      $js   = array();
      $css  = array();

      # require directive for Sprockets::js
      $js[] = $sprockets->js('test-require-js.js');
      $js[] = $sprockets->js('test-require-coffee.coffee');

      # require_directory for Sprockets::js
      $js[] = $sprockets->js('test-require_directory-js.js');
      $js[] = $sprockets->js('test-require_directory-coffee.coffee');

      # require_tree for Sprockets::js
      $js[] = $sprockets->js('test-require_tree-js.js');
      $js[] = $sprockets->js('test-require_tree-coffee.coffee');

      # require directive for Sprockets::css
      $css[] = $sprockets->css('test-require-css.css');
      $css[] = $sprockets->css('test-require-scss.scss');
      $css[] = $sprockets->css('test-require-less.less');

      # require_directory directive for Sprockets::css
      $css[] = $sprockets->css('test-require_directory-css.css');
      $css[] = $sprockets->css('test-require_directory-scss.scss');
      $css[] = $sprockets->css('test-require_directory-less.less');

      # require_tree directive for Sprockets::css
      $css[] = $sprockets->css('test-require_tree-css.css');
      $css[] = $sprockets->css('test-require_tree-scss.scss');
      $css[] = $sprockets->css('test-require_tree-less.less');

      foreach ($js as $js) {

        $doc = new \DOMDocument();
        $doc->loadHTML($js);
        $node = $doc->getElementsByTagName( "script" ); 
        foreach ($node as $node) {
          $file = $node->getAttribute("src");
        }
        $file = str_replace(\Uri::base(false), "", $file);
        $this->assertFileExists(DOCROOT . $file);
      }

      foreach ($css as $css) {
        
        $doc = new \DOMDocument();
        $doc->loadHTML($css);
        $node = $doc->getElementsByTagName( "link" ); 
        foreach ($node as $node) {
          $file = $node->getAttribute("href");
        }
        $file = str_replace(\Uri::base(false), "", $file);
        $this->assertFileExists(DOCROOT . $file);
      }
    }    
  }
}