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

class SubmissionQuery {

	private $polldata,
		$pollsub,
		$configjson,
		$id,
		$template,
		$mailsend = false;

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->id = $pollid;
		$this->configjson = new Config();
		$this->polldata = new JSONReader( 'poll_' . $pollid );
		$this->pollsub = new JSONReader( 'pollsub_' . $pollid );
		$this->template = new Template( 'submissionquery' );
	}

	/**
	 * Check if user tries to query his submissons
	 * @return boolean if user does
	 */
	public static function queried() : bool {
		return !empty($_GET['submission']) && $_GET['submission'] === 'query';
	}

	/**
	 * Generates the poll submission query Link 
	 * (Uses the current Link and adds parameter!)
	 * @return The link
	 */
	public static function getLink() : string {
		return URL::currentLinkGenerator(array('submission' => 'query'));
	}

	/**
	 * Check for E-Mail submission and send mail (if data found)
	 */
	private function checkForPost(){
		if( !empty( $_POST['email'] ) ){
			//captcha?
			if( $this->configjson->getValue(['submissions', 'captcha']) ){
				if( !Captcha::checkImageData() ){
					$alert = new Template( 'alert' );
					$this->template->includeTemplate($alert);
					$alert->setContent( 'ALERTMESSAGE', Captcha::getError() );
					return;
				}
			}

			$email = Utilities::validateInput( $_POST['email'], Poll::PREG_MAIL, Poll::MAXL_MAIL );
			if(!empty( $email ) && $email !== 'mail@mail.mail' ){
				if( preg_match( '/' . $this->configjson->getValue(['submissions', 'mailValidate']) . '/' , $email ) === 1 ){

					$mailsubs = array();
					foreach( $this->pollsub->getArray() as $tid => $option ){
						foreach( $option as $entry ){
							if( $entry['mail'] === $email ){
								unset($entry['showuser'], $entry['additionals'], $entry['mail']);
								if( !isset( $mailsubs[$tid] ) ) {
									$mailsubs[$tid] = array();
								}
								$mailsubs[$tid][] = $entry;
							}
						}
					}
					$this->mailsend = true;
					if( !empty($mailsubs) ){ // found entry?
						if( !$this->doMail( $mailsubs, $email ) ){
							$this->mailsend = false;

							$alert = new Template( 'alert' );
							$this->template->includeTemplate($alert);
							$alert->setContent( 'ALERTMESSAGE', LanguageManager::getTranslation('MailTimeout') );
							
							return;
						}
					}
					usleep(random_int(200000,800000)); // prevent timing attacks
					
					return;
				}
			}
			$alert = new Template( 'alert' );
			$this->template->includeTemplate($alert);
			$alert->setContent( 'ALERTMESSAGE', LanguageManager::getTranslation('InvalMail') );
		}
	}

	private function doMail( array $d, string $email ){
		$m = new Mail('PollSubm');
		$m->setContent( "POLLNAME", $this->polldata->getValue(['pollname']));
		$m->setContent( "POLLLINK", URL::currentLinkGenerator());

		$items = array();
		foreach( $d as $tid => $subs ){
			foreach( $subs as $v ){
				$items[] = array(
					"OPTION" => Utilities::optimizeOutputString( $this->polldata->getValue(["termine", $tid, "bez"]) ),
					"DESC" => Utilities::optimizeOutputString( $this->polldata->getValue(["termine", $tid, "des"]) ),
					"NAME" => Utilities::optimizeOutputString( $v['name'] ),
					"MAIL" => Utilities::optimizeOutputString( $email ),
					"TIME" => date( 'H:i:s d.m.Y', $v['time'] ),
					"DELTELINK" => URL::generateAPILink( 'delsubmission', array(
							'poll' => $this->id,
							'terminid' => $tid,
							'code' => $v['editcode']
						))
				);
			}
		}
		$m->setMultipleContent( "Items", $items );

		return $m->sendMail($email);
	}

	/**
	 * Show the Submission Query Page and Form
	 */
	public function showForm(){
		if( $this->configjson->getValue(['submissions', 'enabled']) ){
			$this->checkForPost();

			if( $this->mailsend ){
				$this->template->setContent( 'MAILFORM', 'd-none' );	
				$this->template->setContent( 'MAILSUCCESS', '' );	
			}
		}

		$this->template->setContent( 'FORMDEST', self::getLink() );
		$this->template->setContent( 'POLLNAME', $this->polldata->getValue(['pollname']) );
		$this->template->setContent( 'BACKTOPOLLLINK', URL::currentLinkGenerator() );

		if( $this->configjson->getValue(['submissions', 'enabled']) ){ //query via mail enabled?
			$this->template->setContent( 'PATTERN', $this->configjson->getValue(['submissions', 'mailValidate']) );
			//captcha?
			if( $this->configjson->getValue(['submissions', 'captcha']) ){
				$this->template->setContent( 'CAPTCHA', Captcha::getCaptchaHTML() );
			}
		}
		else {
			$this->template->setContent( 'MAILDISABLED', 'd-none' );
		}

		$this->template->setContent( 'JSONDATA', json_encode(
			array(
				'pollid' => $this->id,
				'termine' => array_map( function ($a) {
							return array(
								Utilities::optimizeOutputString( $a['bez'] ),
								Utilities::optimizeOutputString( mb_substr(
									Utilities::validateInput( $a['des'], PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT  ),
									0, 100 ) )
							);
						},
						$this->polldata->getValue(['termine'])
					),
				"polllink" => URL::generateLink('poll','<poll>','')
			), JSON_FORCE_OBJECT
		));

	}

	/**
	 * Get the Page Template Object
	 */
	public function getTemplate(): Template {
		return $this->template;
	}

}

?>