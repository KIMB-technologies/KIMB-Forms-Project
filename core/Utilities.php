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

class Utilities{

	private static $configjson = null;

	public const POLL_ID = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
	public const ADMIN_CODE = 'abcdefghijklmnopqrstuvwxyz01234567890';
	public const CAPTCHA = 'abcdefghjkmnpqrstuvwxyz23456789';

	/**
	 * Checks if a String is a valid file-name (file only, no dirs)
	 * @param $name the filename
	 */
	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}

	/**
	 * Does some optimizing on the give string to output it for html display
	 * 	nl2br and htmlentities
	 * @param $cont the string to optimized
	 */
	public static function optimizeOutputString($cont){
		return nl2br( htmlentities( $cont, ENT_COMPAT | ENT_HTML401, 'UTF-8' ));
	}

	/**
	 * Generates a random code
	 * @param $len the code lenght
	 * @param $chars the chars to choose of (string)
	 * 	e.g. consts POLL_ID, ADMIN_CODE
	 */
	public static function randomCode( $len, $chars ){
		$r = '';
		$charAnz = strlen( $chars );
		for($i = 0; $i < $len; $i++){
			$r .= $chars{random_int(0, $charAnz-1)};
		}
		return $r;
	}

	/**
	 * Generates a System Link/ URL
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
	 * Generates a API Link/ URL
	 * @param $task the task
	 * @param $params params to append array(param => value, ...)
	 */
	public static function generateAPILink($task, $params = array()){
		if( self::$configjson == null ){
			self::$configjson = new JSONReader( 'config' );
		}
		$app = '';
		foreach( $params as $par => $val ){
			$app .= '&' . $par . '=' . $val;
		}
		return self::$configjson->getValue(['site', 'hosturl']) . '/api.php?task=' . $task . $app;
	}

	/**
	 * Generates the current link
	 * 	Additional params should be parsed by urlParser() into $_GET
	 * @param $params parameter to append at the end; array( 'param' => 'value' )
	 */
	public static function currentLinkGenerator($params = array()){
		$append = '';
		foreach( $params as $par => $val ){
			$append .= '&' . $par . '=' . urlencode( $val ); 
		}
		$a = self::urlParser();
		$a['pollid'] = ( $a['pollid'] === false) ? '' : $a['pollid'];
		$a['admincode'] = ( $a['admincode'] === false) ? '' : $a['admincode'];
		return self::generateLink($a['task'], $a['pollid'], $a['admincode']) . $append;
	}

	/**
	 * Parses the current url into an array, addition params into $_GET
	 * @return ['task' => task, 'pollid' => pollid or false, 'admincode' => admincode or false]
	 */
	public static function urlParser(){
		// addition params from currentLinkGenerator( $params )
		//	should be parsed here into $_GET['param'] = value
		//	only necessary if, typical &par=val is not used
		return array(
			'task' => isset($_GET['task']) ? preg_replace( '[^a-z]', '', $_GET['task'] ) : 'start',
			'pollid' => isset($_GET['poll']) ?  preg_replace( '[^a-z0-9]', '', $_GET['poll'] ) : false,
			'admincode' => isset($_GET['admin']) ?  preg_replace( '[^a-z0-9]', '', $_GET['admin'] ) : false
		);
	}
}

?>
