<?php
/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'KIMB-FORMS-PROJECT' ) or die('Invalid Endpoint!');

require_once( __DIR__ . '/Utilities.php' );

spl_autoload_register(function ($class) {
	if( Utilities::checkFileName( $class ) ){
		$classfile = __DIR__ . '/' . $class . '.php';
		if( is_file($classfile) ){
			require_once( $classfile );
		}
	}
});

require_once( __DIR__ . '/sysinit.php' );
?>
