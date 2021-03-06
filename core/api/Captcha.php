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

class Captcha{

	/**
	 * Use Post param in checkImageData
	 */
	const USE_POST = 1;

	/**
	 * The TTF font file for the image chars
	 */
	const FONT = __DIR__ . '/font/CabinSketch.ttf';

	/**
	 * The name of the session and post param
	 */
	const SESSION_POST_NAME = 'CAPTCHA_STRING';

	/**
	 * The name of the session for already checked users
	 */
	const SESSION_OK_NAME = 'CAPTCHA_LAST_OK';

	/**
	 * How long does a solved captcha live?
	 * (seconds) 
	 */
	const LAST_SOLVED_TIMEOUT = 600;

	/**
	 * When show a new one?
	 * (should be smaller than LAST_SOLVED_TIMEOUT, seconds)
	 */
	const LAST_SOLVED_NEW = 90;

	/**
	 * The string, displayed in the captcha
	 */
	private static $string;

	/**
	 * The error message
	 */
	private static $error = '';

	/**
	 * Creating a Captcha Image
	 * (direct PNG output)
	 */
	public static function showImage(){
		//load image
		$img = imagecreate(130, 40);
		imagecolorallocate($img, 255, 255, 255);

		//generate string
		self::$string = Utilities::randomCode( 6, Utilities::CAPTCHA );
	
		//iterate over all
		$l = 5;
		for($i = 0; $i < strlen(self::$string); $i++){
			//add letter two times, 
			imagettftext(
				$img, 30, random_int(-10, 10),
				$l + (($i+1 != 1?15:5) * ($i+1)), 25,
				imagecolorallocate($img, 200, 200, 200),
				self::FONT, self::$string[$i]
			);
			imagettftext(
				$img, 24, random_int(-15, 15),
				$l + (($i+1 != 1?15:5) * ($i+1)), 25,
				imagecolorallocate($img, 69, 103, 137),
				self::FONT, self::$string[$i]
			);
		}

		//add to session
		$_SESSION[self::SESSION_POST_NAME] = self::$string;

		//Cache disable
		header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', FALSE);
		header('Pragma: no-cache');
		header('X-Robots-Tag: noindex');
	 
		//image output
		header("Content-type: image/png");
		imagepng($img);
		imagedestroy($img);
		die();
	}

	/**
	 * Getting the HTML for the captcha
	 */
	public static function getCaptchaHTML(){
		if( !empty( $_SESSION[self::SESSION_OK_NAME] ) && $_SESSION[self::SESSION_OK_NAME] - self::LAST_SOLVED_NEW >= time() ){
			//user has already solved a captcha, no new one
			return '';
		}
		return Utilities::getRowHtml(
			'<img title="'.LanguageManager::getTranslation('CaptTitle').'" src="'. URL::generateAPILink('captcha', ['time' => time()]) .'" onclick="this.src=\''.URL::generateAPILink('captcha').'&r=\'+Math.random();">',
			'<input type="text" name="'. self::SESSION_POST_NAME .'" placeholder="Captcha" class="form-control nolocalsave">'
		);
	}
	
	/**
	 * Checking the captcha and post data
	 * @param $string the string typed by the user
	 * 	default self::USE_POST, the post param by getCaptchaHTML()
	 */
	public static function checkImageData( $string = self::USE_POST ){
		if( !empty( $_SESSION[self::SESSION_OK_NAME] ) && $_SESSION[self::SESSION_OK_NAME] >= time() ){
			//user has already solved a captcha, no new one
			return true;
		}
		if( $string === self::USE_POST ){
			$string = $_POST[self::SESSION_POST_NAME];
		}
		if( !empty( $_SESSION[self::SESSION_POST_NAME] ) && !empty( $string ) ){
			if( $_SESSION[self::SESSION_POST_NAME] == $string ){
				//now user has solved one, set valid until:
				$_SESSION[self::SESSION_OK_NAME] = time() + self::LAST_SOLVED_TIMEOUT; 
				return true;
			}
			else{
				self::$error = LanguageManager::getTranslation( 'IncorrCap' );
				return false;
			}
		}
		else{
			self::$error = LanguageManager::getTranslation( 'PleFillCap' );
			return false;
		}
	}

	/**
	 * Getting the error Message
	 */
	public static function getError(){
		return self::$error;
	}
}

?>
