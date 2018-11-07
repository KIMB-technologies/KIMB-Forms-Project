<?php

class Utilities{

	private static $configjson = null;

	/**
	 * Checks if a String is a valid file-name (file only, no dirs)
	 * @param $name the filename
	 */
	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}

	/**
	 * Generates a System Link
	 * @param $task the task (start is default)
	 * @param $pollid the poll (empty string is default)
	 */
	public static function generateLink($task = 'start', $pollid = '', $admincode = ''){
		if( self::$configjson == null ){
			self::$configjson = new JSONReader( 'config' );
		}
		return self::$configjson->getValue(['site', 'hosturl']) . '/?task=' . $task
			. ( $pollid != '' ? '&poll=' . $pollid : '' )
			. ( $admincode != '' ? '&admin=' . $admincode : '' );
	}

	/**
	 * Generates the current link
	 * @param $append a string to append at the end
	 */
	public static function currentLinkGenerator($append = ''){
		$a = self::urlParser();
		$a['pollid'] = ( $a['pollid'] === false) ? '' : $a['pollid'];
		$a['admincode'] = ( $a['admincode'] === false) ? '' : $a['admincode'];
		return self::generateLink($a['task'], $a['pollid'], $a['admincode']) . $append;
	}

	/**
	 * Parses the current url into an array
	 * @return ['task' => task, 'pollid' => pollid or false, 'admincode' => admincode or false]
	 */
	public static function urlParser(){
		return array(
			'task' => isset($_GET['task']) ? preg_replace( '[^a-z]', '', $_GET['task'] ) : 'start',
			'pollid' => isset($_GET['pollid']) ?  preg_replace( '[^a-z0-9]', '', $_GET['pollid'] ) : false,
			'admincode' => isset($_GET['admin']) ?  preg_replace( '[^a-z0-9]', '', $_GET['admin'] ) : false
		);
	}
}

?>
