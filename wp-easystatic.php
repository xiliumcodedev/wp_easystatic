<?php
/*
* Plugin Name: WP Easystatic
* Plugin URI: https://github.com/xiliumcodedev/wp_easystatic
* Description: Generate and Optimize your website to a static HTML file
* Author: XiliumCodeDev
* Author URI: https://www.linkedin.com/in/ian-paul-jocsing-7bb24717b/
* Version: 1.6.0
* Text Domain: easystatic
* Domain Path: /languages
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/
/*  Copyright 2019 XiliumCodeDev

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined( 'ABSPATH' ) ) {
   exit;
}

DEFINE('EASYSTATIC_PLUGIN_VERSION', '1.6.0' );

DEFINE('EASYSTASTIC_FILE', __FILE__);

DEFINE('EASYSTASTIC_SLUG', 'easystatic');

class WP_Easystatic{
	/*
	* construct autoload classes and deactivate plugin if PHP lower than required
	*/
 	function __construct(){
		spl_autoload_register(array($this, 'wp_easystatic_autoload'));
		if(version_compare( PHP_VERSION, '5.3', '<' )) {
			add_action( 'admin_init', 'deactivate_plugin_self' );
		}
	}

	/*
	* autoload component classes and abstract
	*/
	function wp_easystatic_autoload( $class ){

		if('Function' == substr($class, 14, strlen($class))){
			$file = strtolower( $class );
			$file = str_replace( '_', '-', $file );
			$path = $file . '.php';
		}
		else if(in_array(substr($class, 14, strlen($class)), 
			array('Utils', 'Components', 'Controller'))){
			$file = strtolower( $class );
			$file = str_replace( '_', '-', $file );
			$path = dirname( EASYSTASTIC_FILE ) . '/includes/' . $file . '.php';
		}
		else if(in_array(substr($class, 14, strlen($class)),
		array('Impl', 'Template', 'Generate', 'Optimize', 'Request'))){
			$file = strtolower( $class );
			$file = str_replace( '_', '-', $file );
			$path = dirname( EASYSTASTIC_FILE ) . '/includes/lib/' . $file . '.php';
		}
		
		if ( ! isset( $path ) ) {
       	 	return;
    	}

    	require_once $path;
	}

	/*
	* Plugin deactivate method	
	*/
	function deactivate_plugin_self(){
		deactivate_plugins( plugin_basename( EASYSTASTIC_FILE ) );
		add_action( 'admin_notices', function(){
			 echo '<p class="version_error">' . __( 'WP EasyStatic requires PHP 5.3 (or higher) to function properly. Please upgrade PHP. The Plugin has been auto-deactivated.', 'easystatic' ) . '</p>';
		});
	}

	/*
	* Run main function
	*/
	static function run(){
		return new WP_Easystatic_Function();
	}

}

$WP_Easystatic = new WP_Easystatic();
$WP_Easystatic->run();