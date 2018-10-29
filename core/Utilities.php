<?php

class Utilities{

	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}
}

?>
