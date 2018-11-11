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

/**
 * Class takes care of the translation.
 */
class LanguageManager{

	private static $allLangs = array(
		'de',
		'en'
	);
	private static $trans;
	private static $lang = 'de';

	/**
	 * Load Translation and set on other classes
	 */
	public static function init(){
		if(!empty( $_GET['language'] )){
			self::setLanguage( $_GET['language'] );
		}
		else if( !empty( $_SESSION['language'] )){
			self::setLanguage( $_SESSION['language'] );
		}
		else{
			self::setLanguage( $lang );
		}
		$json = new JSONReader( 'translation_' . self::$lang );
		self::$trans = $json->getArray();
	}

	/**
	 * Change the language of the site
	 * see $allLangs for list
	 * @param lang the lang to use
	 */
	public static function setLanguage( $lang ){
		if( in_array( $lang, self::$allLangs ) ){
			self::$lang = $lang;
			$_SESSION['language'] = $lang;
		}
		self::updateClasses();
	}

	/**
	 * Sets the current language to system classes
	 */
	private static function updateClasses(){
		Template::setLanguage( self::$lang );
	}

	/**
	 * Gets all languages and their names
	 */
	public static function getAllLanguages(){
		$data = array();
		foreach( self::$allLangs as $key ){
			$data[$key] = self::getTranslation( $key );
		}
		return $data;
	}

	/**
	 * Gets the current language Key.
	 */
	public static function getCurrentLanguage(){
		return self::$lang;
	}

	/**
	 * Gets the current translation for a key
	 */
	public static function getTranslation( $key ){
		if( !isset( self::$trans[$key] ) ){
			//logging
			$cont = 'Missing Translation:'. time() ."\r\n";
			$cont .= "\t".'Key:'.$key."\r\n";
			$cont .= "\t".'Lang:'.self::$lang."\r\n\r\n";
			file_put_contents( __DIR__ . '/../data/translation.log', $cont, FILE_APPEND );

			return 'no value';
		}
		return self::$trans[$key];
	}
}

?>