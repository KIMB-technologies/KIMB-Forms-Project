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

class URL{

	private static $configjson = null;
	private static $parsedurl = null;

	/**
	 * Setting up the class, in a static context
	 */
	private static function setup(){
		if( self::$configjson == null ){
			self::$configjson = new JSONReader( 'config' );
		}
	}

	/**
	 * Generates a System Link/ URL
	 * @param $task the task (start is default)
	 * @param $pollid the poll (empty string is default)
	 * @return the url as string
	 */
	public static function generateLink($task = 'start', $pollid = '', $admincode = ''){
		self::setup();
		return self::$configjson->getValue(['site', 'hosturl']) . '/?task=' . $task
			. ( $pollid != '' ? '&poll=' . $pollid : '' )
			. ( $admincode != '' ? '&admin=' . $admincode : '' );
	}

	/**
	 * Generates a API Link/ URL
	 * @param $task the task
	 * @param $params params to append array(param => value, ...)
	 * @return the url as string
	 */
	public static function generateAPILink($task, $params = array()){
		self::setup();
		$app = '';
		foreach( $params as $par => $val ){
			$app .= '&' . $par . '=' . urlencode( $val );
		}
		return self::$configjson->getValue(['site', 'hosturl']) . '/api.php?task=' . $task . $app;
	}

	/**
	 * Generates the current link
	 * 	Additional params should be parsed by urlParser() into $_GET
	 * @param $params parameter to append at the end; array( 'param' => 'value' )
	 * @return the url as string
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
	 * Parses the current url into an array and additional params into $_GET
	 * @return ['task' => task, 'pollid' => pollid or false, 'admincode' => admincode or false]
	 */
	public static function urlParser(){
		// addition params from currentLinkGenerator( $params )
		//	should be parsed here into $_GET['param'] = value
		//	only necessary if, typical &par=val is not used

		if( self::$parsedurl === null ){ //cache parsed
			self::$parsedurl = array(
				'task' => isset($_GET['task']) && is_string($_GET['task']) ? preg_replace( '[^a-z]', '', $_GET['task'] ) : 'start',
				'pollid' => isset($_GET['poll']) && is_string($_GET['poll']) ?  preg_replace( '[^a-z0-9]', '', $_GET['poll'] ) : false,
				'admincode' => isset($_GET['admin']) && is_string($_GET['admin']) ?  preg_replace( '[^a-z0-9]', '', $_GET['admin'] ) : false
			);
		}

		return self::$parsedurl;
	}
}

?>