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

class URL{

	/**
	 * Config
	 */
	private static $configjson = null;

	/**
	 * Parsed URL data array
	 */
	private static $parsedurl = null;

	/**
	 * URL Rewrite status and data store
	 */
	private static $urlrew = false;
	private static $querydata;

	/**
	 * Setting up the class, in a static context
	 */
	public static function setup(){
		if( self::$configjson === null ){
			self::$configjson = new Config();

			//check for url rewrite
			if( isset($_SERVER['REQUEST_URI']) || isset( $_GET['uri'] ) ){
				//activated
				if( self::$configjson->getValue( ['urlrewrite'] ) ){
					if( isset( $_GET['uri'] ) ){ // use uri get (via server conf)
						$qstring = $_GET['uri'];
					}
					else{ // else uses server var
						$qstring = $_SERVER['REQUEST_URI'];
					}
					//only allowed chars
					$qstring = Utilities::validateInput($qstring, '/[^a-z0-9A-Z\/\-\.\%]/', 500);
					//to array and remove empty ones
					$qd = array_values(array_filter(explode('/', $qstring)));

					//remove paths to system folder
					//	(example.com/forms/start, only /start ist needed)
					//		split host url by / (e.g. [http:, example.com, forms])
					$hosts = array_values(array_filter(explode('/', self::$configjson->getValue(['site', 'hosturl']))));
					if( isset($hosts[2]) && strcasecmp( $hosts[2], $qd[0] ) == 0 ){ // means there is at least one path folder
						$newqd = array();
						foreach( $qd as $i => $value ){
							if( !isset( $hosts[$i+2] ) && strcasecmp( $hosts[$i+2], $value ) != 0 ){
								$newqd[] = $value;
							}
						}
						$qd = $newqd;
					}

					//init array
					self::$querydata = array();
					self::$querydata['task'] = !empty($qd[0]) ? $qd[0] : 'start'; // task
					
					if( self::$querydata['task'] === 'poll' ){ // poll id and adminid
						self::$querydata['pollid'] = $qd[1];
						self::$querydata['admincode'] = false;
						$i = 2;
					} 
					else if( self::$querydata['task'] === 'admin' ){
						self::$querydata['pollid'] = false;
						self::$querydata['admincode'] = $qd[1];
						$i = 2;
					}
					else{
						self::$querydata['pollid'] = false;
						self::$querydata['admincode'] = false;
						$i = 1;
					}
					self::$querydata['other'] = array(); //other values .../par/value/par/value/...
					while( isset( $qd[$i] ) ){
						self::$querydata['other'][$qd[$i]] = isset( $qd[$i+1] ) ? $qd[$i+1] : '';
						$i += 2; 
					}
					//parse them into GET
					foreach( self::$querydata['other'] as $p => $v){
						$_GET[$p] = $v;
					}

					self::$urlrew = true;
				}
			}
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
		if( self::$urlrew ){
			return self::$configjson->getValue(['site', 'hosturl']) . '/'
				. ( $task != 'poll' && $task != 'admin' ? $task . '/'  : '' )
				. ( $pollid != '' && $task == 'poll' ? 'poll/' . $pollid . '/' : '' )
				. ( $admincode != '' && $task == 'admin' ? 'admin/' . $admincode . '/' : '' );
		}
		else{
			return self::$configjson->getValue(['site', 'hosturl']) . '/?task=' . $task
				. ( $pollid != '' ? '&poll=' . $pollid : '' )
				. ( $admincode != '' ? '&admin=' . $admincode : '' );
		}
	}

	/**
	 * Generates a API Link/ URL
	 * @param $task the task
	 * @param $params params to append array(param => value, ...)
	 * @return the url as string
	 */
	public static function generateAPILink($task, $params = array()){
		self::setup();
		//here no url rew!
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
		self::setup();
		$append = '';
		if( self::$urlrew ){
			foreach( $params as $par => $val ){
				$append .= $par . '/' . urlencode( $val ) . '/'; 
			}		
		}
		else{
			foreach( $params as $par => $val ){
				$append .= '&' . $par . '=' . urlencode( $val ); 
			}
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
		self::setup();
		// addition params from currentLinkGenerator( $params )
		//	should be parsed here into $_GET['param'] = value
		//	only necessary if, typical &par=val is not used

		if( self::$parsedurl === null ){ //cache parsed
			if( self::$urlrew ){
				self::$parsedurl = self::$querydata;
				unset( self::$parsedurl['other'] );
			}
			else{
				self::$parsedurl = array(
					'task' => isset($_GET['task']) && is_string($_GET['task']) ? preg_replace( '[^a-z]', '', $_GET['task'] ) : 'start',
					'pollid' => isset($_GET['poll']) && is_string($_GET['poll']) ?  preg_replace( '[^a-z0-9]', '', $_GET['poll'] ) : false,
					'admincode' => isset($_GET['admin']) && is_string($_GET['admin']) ?  preg_replace( '[^a-z0-9]', '', $_GET['admin'] ) : false
				);
			}
		}

		return self::$parsedurl;
	}
}

?>