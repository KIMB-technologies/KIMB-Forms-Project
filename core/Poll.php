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

class Poll{

	private $polldata,
		$configjson;

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->configjson = new JSOnReader( 'config' );
		$this->polldata = new JSONReader( 'poll_' . $pollid );
	}

	/**
	 * Check if poll participation form send.
	 * @return boolean send?
	 */
	public function checkSend(){
		return !empty( $_POST['pollsend'] ) && $_POST['pollsend'] == 'yes';
	}

	/**
	 * Saves the send data from poll part form.
	 * @param $template the poll template
	 */
	public function saveSendData( $template ){
		//captcha?
		if( ( $this->configjson->getValue(['captcha', 'poll']) && Captcha::checkImageData() ) || !$this->configjson->getValue(['captcha', 'poll']) ){ 
			
			/**** ToDo **********************/
			// Participation LOG
			
			$template->setContent( 'INNERCONTAINER', 'Saved!' );
			/********************************/

			return true;
		}
		else{
			$alert = new Template( 'alert' );
			$template->includeTemplate($alert);
			$alert->setContent( 'ALERTMESSAGE', Captcha::getError() );

			return false;
		}
	}

	/**
	 * Show the form to fill out the poll
	 * @param $template the poll template
	 */
	public function showPollForm( $template ){

		/**** ToDo **********************/
		$template->setContent( 'FORMDEST', '' );
		$template->setContent( 'POLLNAME', 'Name Poll' );
		$template->setContent( 'POLLDESCRIPT', '*Desc* **Ha**' );
		
		$template->setMultipleContent('Termin', array(
			array(
				"NAME" => "o",
				"ANZAHL" => "i",
				"HINWEISE" => "z",
				"TERMINID" => "termin_a"
			),
			array(
				"TERMINID" => "termin_b"
			)
		));
		/********************************/

		//captcha?
		if( $this->configjson->getValue(['captcha', 'poll']) ){
			$template->setContent( 'CAPTCHA', Captcha::getCaptchaHTML() );
		}
	}		
}

?>