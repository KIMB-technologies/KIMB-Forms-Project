<?php

class Utilities{

	private static $configjson = null;

	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}

	public static function generateLink($task = 'start', $pollid = ''){
		if( self::$configjson == null ){
			self::$configjson = new JSONReader( 'config' );
		}
		return self::$configjson->getValue(['site', 'host']) . '/?task=' . $task . ( $pollid != '' ? '&poll=' . $pollid : '' );
	}

	public static function urlParser(){
		return array(
			'task' => isset($_GET['task']) ? $_GET['task'] : 'start',
			'pollid' => isset($_GET['pollif']) ? $_GET['pollid'] : false
		);
	}
}

?>
