# Fuel Sprockets: Asset Bundling for FuelPHP

Fuel Sprockets is an asset management and asset bundling package
for FuelPHP. Following the idea behind the Asset Pipeline and the ease
of use of the build-in Asset class, you can now manage your javascript
and css files in your application with ease. Fuel Sprockets also comes 
with php ports of Sass/Compass, Less and CoffeeScript compilers.

# Installation #

Installing Fuel Sprockets is as easy as:

    $ git clone https://github.com/vesselinv/fuel-sprockets.git fuel/packages/sprockets

You can also add it as a submodule:

    $ git submodule add https://github.com/vesselinv/fuel-sprockets.git fuel/packages/sprockets

Or simply unzip into `fuel/packages/sprockets`

Don't forget to add it to the Auto Loaded packages block in `config.php`

    'packages'  => array(
      'sprockets',
    ),

# Asset Structure #

Fuel Sprockets will automatically generate your bundle file into your `public/assets` directory.

To use Fuel Sprockets, you will need the following asset structure:

    fuel/
    |-- app/
    |    |-- assets/
    |    |    |-- js/
    |    |    |-- css/
    |    |-- cache/
    |    |    |-- sprockets/
    |    |    |    |-- js/
    |    |    |    |-- css/
    public/
    |-- assets/
    |    |-- js/
    |    |-- css/

You should be placing your asset files inside `app/assets/*` and that's where Fuel Sprockets will
expect to find them. This ensures separation of development files from production bundles. It
also makes them publicly unaccessible.

The package will automatically create its cache folder. You will however need to create
`app/assets/js/` and `app/assets/css/`

# Convention over Configuration #

Fuel Sprockets, by default, is configured to use the afore-mentioned asset
structure and make our lives a little bit easier. You can however, overwrite it 
by copying `config/sprockets.php` into your `fuel/app/config` directory

    <?php
    return array(
      'asset_root_dir'          => APPPATH . 'assets/',
      'asset_compile_dir'       => DOCROOT . 'assets/',
      'cache_dir'               => APPPATH . 'cache/sprockets/',
      'js_dir'                  => 'js/',
      'css_dir'                 => 'css/',
      'base_url'                => \Uri::base(false),
      'force_minify'            => false
    );

# How to Use  #

Inside your views, simply invoke your bundle file just as you would with `Asset`

      <?php echo Sprockets::js('application.js'); ?>
      <?php echo Sprockets::css('application.scss'); ?>

The above will produce:

      <script src="http://localhost:8000/assets/js/application_0004abf4a2950d49d237ecd9112fc233.js" type="text/javascript"></script>
      <link rel="stylesheet" href="http://localhost:8000/assets/css/application_73afabf115045b19bfa32fa25de2861e.css">

## The Directive Parser ##

Fuel Sprockets runs the *directive parser* on each CSS and JavaScript
source file. The directive parser scans for comment lines beginning with 
`=` in comment blocks anywhere in the file. A sample `application.js`:

    //= require jquery-1.9.1.js
    //= require vendor/backbone.js
    //= require_tree .
    //= require_directory vendor/
    (function($){
      $(document).ready(function(){
        alert('It effin works, dude!');
        });
      })(jQuery);

Directives expect relative file paths, which must include the file extension.
File paths can also be wrapped in single `'` or double `"` quotes.

### Supported Comment Types ###

Fuel Sprockets understands comment blocks in three formats:

    /* Multi-line comment blocks (CSS, SCSS, Less, JavaScript)
     *= require 'foo.js'
     */

    // Single-line comment blocks (SCSS, Less, JavaScript)
    //= require "foo.scss"

    # Single-line comment blocks (CoffeeScript)
    #= require foo.coffee

## Fuel Sprockets Directives ##

You can use the following directives to declare dependencies in asset
source files.

### The `require` Directive ###

`require` *path* inserts the contents of the asset source file
specified by *path*. If anywhere in the scanned files *path* is duplicated,
it will only be inserted once at the first `require` spot. Also supports remote
files from CDNs.

### The `require_directory` Directive ###

`require_directory` *path* requires all source files of the same
format - Js/Coffee or Css/Scss/Less - in the directory specified by *path*.
Files are required in alphabetical order.

### The `require_tree` Directive ###

`require_tree` *path* works like `require_directory`, but operates
recursively to require all files in all subdirectories of the
directory specified by *path*.

# Supported Compilers #

Fuel Sprockets comes with php ports of [Sass/Compass](https://github.com/leafo/scssphp-compass) (only the Scss syntax),
[Less](https://github.com/leafo/lessphp) and [CoffeeScript](https://github.com/alxlit/coffeescript-php) compilers that will automatically compile your assets,
without having to have these gems installed - in fact, no Ruby installation is
needed either - all is handled through php.

*Note:* The package will expect to find CoffeeScripts inside your JS Asset Root
(`fuel/app/assets/js/` by default) along with vanilla Js file, and Scss and Less stylesheeets
inside your CSS Root Dir (`fuel/app/assets/css/` by default) along with vanilla Css. Some may 
say why mix up Js with Coffee and Css with Sass and Less and the answer simply is because 
in the end they all get compiled to plain Js and Css respectively. At a future point, if requested,
I may add support for separating them into different folders.

# Minification #

All Sprockets files will be automatically minified if your `Fuel::$env` is set to 
`production`. You can, however, force minification in different environments by
setting `force_minify` to `true` in the Sprockets config file.

# Smart Caching #

Fuel Sprockets is smart about caching. The final compiled source for each file that is 
included in your bundles in cached inside `fuel/app/cache/sprockets`. The Last Modified
timestamp and minification flag (`.min`) are appended to the filename so that we can 
compare when your asset file has changed and whether the generated file is up-to-date.

# Running Tests #

I've prepared a Test Case with a set of files that will test all of the supported directives,
compilers and minifier. To run the tests, simply use oil:

    $ oil t

And watch the cache and compile folders inside tests/ get filled with bundles.

# Precompiling your Bundles #

A Fuel Task is available through `oil` to precompile and minify your bundles.

    $ oil r sprockets:js application.js
    $ oil r sprockets:css application.scss

These tasks come in very handy when deploying with Capistrano. Simply define a
task in your deploy.rb

    namespace :deploy do
        desc "Precompile Assets"
        task :sprockets do
            run [ "cd #{latest_release}", 
                "php oil refine sprockets:js application.coffee",
                "php oil refine sprockets:css application.scss"
              ].join("; ")
        end
    end
    
    after "deploy:migrate", "deploy:sprockets"

# Roadmap #

The following improvements are on my list:

* Support for image processing when referenced in Scss, Less and Css assets.
* Support for fonts referenced in css assets - copy font files from `asset_root_dir` to `compile_dir`
* Config option for image quality
* Additional Config options for the CoffeeScript, Scss/Compass and Less compilers
* Make package installable through Composer after the future release of Fuel that will support this.

# License #

Copyright &copy; 2013 Veselin Vasilev  [@vesselinv](https://twitter.com/vesselinv)

Fuel Sprockets is distributed under an MIT-style license. See LICENSE for
details.