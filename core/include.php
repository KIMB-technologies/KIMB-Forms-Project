<?php
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
