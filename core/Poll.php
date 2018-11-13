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
		$pollsub,
		$configjson;

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->configjson = new JSOnReader( 'config' );
		$this->polldata = new JSONReader( 'poll_' . $pollid );
		$this->pollsub = new JSONReader( 'pollsub_' . $pollid );
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

		$template->setContent( 'FORMDEST', Utilities::currentLinkGenerator() );
		$template->setContent( 'POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )) );
		$template->setContent( 'POLLDESCRIPT', Utilities::optimizeOutputString($this->polldata->getValue( ['description'] )) );

		$type = $this->polldata->getValue( ['formtype'] );
		$termine = array();
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			if( $type === 'person' ){
				$schon = $this->pollsub->isValue( [$id] ) ? count( $this->pollsub->getValue( [$id] ) ) : 0; 
				$anzval = $schon . '/' . $values["anz"];

				$disable = $schon >= $values["anz"] ? ' disabled="disabled"' : '';
			}
			else{
				if( $schon = $this->pollsub->isValue( [$id] ) ){
					$names = array();
					foreach( $this->pollsub->getValue( [$id] ) as $user){
						$names[] = Utilities::optimizeOutputString( $user['name'] );
					}
					$anzval = '<ul class="list-group"><li class="list-group-item">' .  implode( '</li><li class="list-group-item">',  $names ). '</li></ul>';
				}
				else{
					$anzval = '';
				}
				$disable = '';
			}
			$termine[] = array(
				"NAME" => Utilities::optimizeOutputString( $values["bez"] ),
				"ANZAHL" => $anzval,
				"HINWEISE" => Utilities::optimizeOutputString( $values["des"] ),
				"TERMINID" => "termin_" . $id,
				"DISABLE" => $disable
			);
		}
		
		$template->setMultipleContent('Termin', $termine);

		//captcha?
		if( $this->configjson->getValue(['captcha', 'poll']) ){
			$template->setContent( 'CAPTCHA', Captcha::getCaptchaHTML() );
		}
	}		
}

?>