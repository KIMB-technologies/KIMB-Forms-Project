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

class Captcha{

	const USE_POST = '';

	public static function showImage(){
		echo 'Captcha Image';
	}

	public static function getCaptchaHTML(){
		echo '<p>Captcha HTML</p>';
	}
	
	public static function checkImageData( $string = self::USE_POST ){
		return false;
	}
}

?>