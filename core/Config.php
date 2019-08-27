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

class Config {

	private $jreader;

	public function __construct(){
		// load different in docker
		$this->jreader = new JSONReader( 'config', false, isset( $_ENV['DOCKERMODE'] ) && $_ENV['DOCKERMODE'] == 'true' ? '/sysdata' : '' );
	}

	
	//Einen Wert aus der Configuration holen
	//	wie JSON Reader, zieht aber env vars in Docker vor.
	public function getValue( $index, $exception = false ){
		$envkey = 'CONF_' . implode( '_', $index );
		if( isset( $_ENV[$envkey] ) ){
			if( $_ENV[$envkey] == 'true' || $_ENV[$envkey] == 'false' ){
				$_ENV[$envkey] = $_ENV[$envkey] == 'true';
			}
			return $_ENV[$envkey];
		}
		else{
			return $this->jreader->getValue( $index, $exception );
		}
	}

	public function __destruct(){
		$this->jreader->__destruct();
	}
}

?>