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
 * This class provides a Cookie Banner
 * (The Object is given to output by calling toString, there should be HTML returned)
 */
class CookieBanner{

	/**
	 * Creates a new CookieBanner, this object has to represent it.
	 */
	public function __construct(){
	}

	/**
	 * Returns the HTML-Code which displays the banner
	 */
	public function __toString(){
		return '<div style="color:red;">Cookie-Banner</div>';
	}
}


?>