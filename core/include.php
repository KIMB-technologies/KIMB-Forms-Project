<?php
/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 - 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'KIMB-FORMS-PROJECT' ) or die('Invalid Endpoint!');

// Autoloader uses Utilities
require_once( __DIR__ . '/Utilities.php' );

// external libs loader
require_once( __DIR__ . '/external/loader.php' );

// Autoloader
spl_autoload_register(function ($class) {
	if( Utilities::checkFileName( $class ) ){
		$classfile = __DIR__ . '/' . $class . '.php';
		$apiclassfile = __DIR__ . '/api/' . $class . '.php';
		if( is_file($classfile) ){ //Page class
			require_once( $classfile );
		}
		else if( is_file($apiclassfile) ){ // API class
			require_once( $apiclassfile );
		}
	}
});

require_once( __DIR__ . '/sysinit.php' );
?>
