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

	const USE_POST = 1;
	const FONT = __DIR__ . '/font/CabinSketch.ttf';
	const SESSION_POST_NAME = 'CAPTCHA_STRING';

	private static $string;

	public static function showImage(){
		//load image
		$img = imagecreate(130, 30);
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
				self::FONT, self::$string{$i}
			);
			imagettftext(
				$img, 24, random_int(-15, 15),
				$l + (($i+1 != 1?15:5) * ($i+1)), 25,
				imagecolorallocate($img, 69, 103, 137),
				self::FONT, self::$string{$i}
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

	public static function getCaptchaHTML(){
		return '<div class="alert" role="alert"><div class="row"><div class="col"><img title="Captcha" src="'. Utilities::generateAPILink('captcha', ['time' => time()]) .'"></div>'
			.'<div class="col"><input type="text" name="'. self::SESSION_POST_NAME .'" placeholder="Captcha" class="form-control"></div></div></div>';
	}
	
	public static function checkImageData( $string = self::USE_POST ){
		/**
		 * ToDo
		 */
		
		return false;
	}
}

?>