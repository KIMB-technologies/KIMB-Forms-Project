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

class Utilities{

	/**
	 * The system's Version
	 */
	const SYS_VERSION = 'v1.1.6';

	/**
	 * Possible chars for:
	 */
	const POLL_ID = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
	const ADMIN_CODE = 'abcdefghijklmnopqrstuvwxyz01234567890';
	const CAPTCHA = 'abcdefghjkmnpqrstuvwxyz23456789';

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
	 * Validates a string by the given rex and cuts lenght
	 * 	**no boolean return**
	 * @param $s the string to check
	 * @param $reg the regular expressions (/[^a-z]/ to allow only small latin letters)
	 * @param $len the maximum length
	 * @return the clean string (empty, if other input than string or only dirty characters)
	 */
	public static function validateInput($s, $reg, $len){
		if( !is_string($s) ){
			return '';
		}
		return substr(trim(preg_replace( $reg, '' , $s )), 0, $len);
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
			$r .= $chars[random_int(0, $charAnz-1)];
		}
		return $r;
	}

	/**
	 * Returns a typical div colum having two rows.
	 * @param $first the data in the first cell
	 * @param $second the data in the second cell
	 * @param $clF classes to add to first cell	
	 * @param $clS classes to add to second cell
	 */
	public static function getRowHtml($first, $second, $clF = '', $clS = ''){
		return '<div class="alert" role="alert"><div class="row"><div class="col-sm '. $clF .'">' . $first .'</div><div class="col-sm '. $clS .'">'. $second .'</div></div></div>';
	}

	/**
	 * Generates a collapsable HTML-Element
	 * @param $name the name of the element (click on this to open)
	 * @param $content The content to collapse
	 * @return HTML String
	 */
	public static function getCollapseHtml(string $name, string $content) : string {
		$html = '<div class="card collapseContent">';
		$html .= '<div class="card-header">&#x25bc; ' . $name . '</div>';		
		$html .= '<div class="card-text">'. $content .'</div>';
		$html .= '</div>';
		return $html;
	}
}

?>
