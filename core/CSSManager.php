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

/**
 * Class takes care of the css template/ theme type.
 * e.g. light and dark
 */
class CSSManager{

	private static $allCSS = array(
		'light',
		'dark'
	);
	private static $css = 'light';

	/**
	 * Load css theme type.
	 */
	public static function init(){
		if(!empty( $_GET['css'] )){
			self::setCSS( $_GET['css'] );
		}
		else if( !empty( $_SESSION['css'] )){
			self::setCSS( $_SESSION['css'] );
		}
		else{
			self::setCSS( self::$allCSS[0] );
		}
	}

	/**
	 * Change the css type of the site
	 * see $allCSS for list
	 * @param css the type to use
	 */
	public static function setCSS( $css ){
		if( is_string($css) && in_array( $css, self::$allCSS ) ){
			self::$css = $css;
			$_SESSION['css'] = $css;
		}
	}

	/**
	 * Gets the link to change css theme.
	 */
	public static function getCSSChangeLink(){
		return URL::currentLinkGenerator(
			array(
				'css' =>
					self::$css == self::$allCSS[0] ? self::$allCSS[1] : self::$allCSS[0]
			)
		);
	}

	/**
	 * Gets the current css theme.
	 */
	public static function getCurrentCSS(){
		return self::$css;
	}
}

?>
