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

class Poll{

	/**
	 * Regex for Name and E-Mail address
	 */
	const PREG_NAME = '/[^A-Za-z0-9 \-ÄÜÖäüöß]/';
	const PREG_MAIL = '/[^A-Za-z0-9 \@\.\_\-\+]/';

	/**
	 * Max string length for Name and Mail
	 */
	const MAXL_NAME = 100;
	const MAXL_MAIL = 100;

	/**
	 * Max number of submissions per poll
	 */
	const MAX_SUBMISSIONS = 1000;


	private $polldata,
		$pollsub,
		$configjson,
		$id,
		$error = '';

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->id = $pollid;
		$this->configjson = new Config();
		$this->polldata = new JSONReader( 'poll_' . $pollid );
		$this->pollsub = false;
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
	 * @param $template the **main** template
	 */
	public function saveSendData( $template ){
		//captcha?
		$capok = ( $this->configjson->getValue(['captcha', 'poll']) && Captcha::checkImageData() ) || !$this->configjson->getValue(['captcha', 'poll']);
		$einwill = ( ( $this->configjson->getValue(['texts', 'enablePoll']) && !empty( $_POST['textseinwill'] )) || !$this->configjson->getValue(['texts', 'enablePoll']) );
		if( $capok && $einwill ){
			if( empty($_POST['name']) ){ // force name
				$this->error = LanguageManager::getTranslation('FillTermName');
				return false;
			}
			//parse name and mail
			$name = Utilities::validateInput($_POST['name'], self::PREG_NAME, self::MAXL_NAME);
			$mail = empty( $_POST['email'] ) ? 'mail@mail.mail' : Utilities::validateInput($_POST['email'], self::PREG_MAIL, self::MAXL_MAIL);
			$showuser = !empty( $_POST['showuser'] ) && $_POST['showuser'] == 'show';
			
			if( $this->pollsub === false ){
				$this->pollsub = new JSONReader( 'pollsub_' . $this->id, true); //directly exclusive
			}

			if(
				array_reduce( $this->pollsub->getArray() , function ($carry, $item){ //calc number of submissions
					return $carry + count($item);
					}, 0 ) > self::MAX_SUBMISSIONS
			){
				$this->error = LanguageManager::getTranslation('TooManySubmiss');
				return false;
			}

			//parse termine
			$type = $this->polldata->getValue( ['formtype'] );
			$termine = array();
			$termine_text = array();
			foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
				if( !empty( $_POST['termin_' . $id] ) ){ //termin choosen
					if( $type === 'person' ){
						$schon = $this->pollsub->isValue( [$id] ) ? count( $this->pollsub->getValue( [$id] ) ) : 0; 
						if( $schon < $values["anz"] ){
							$termine[] = $id;
							$termine_text[] = Utilities::optimizeOutputString( $this->polldata->getValue( ['termine', $id, 'bez'] ) );
						}
					}
					else{
						$termine[] = $id;
						$termine_text[] = Utilities::optimizeOutputString( $this->polldata->getValue( ['termine', $id, 'bez'] ) );
					}
				}
			}

			if( empty($name) || empty( $mail ) || empty( $termine )){
				$this->error = LanguageManager::getTranslation('FillTermName');
				return false;
			}

			//parse additionals
			$additionals = array();
			if( $this->polldata->isValue(['additionals']) ){
				foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
					if( !empty( $_POST['additional_' . $key] ) ){
						$addhere = ($add['type'] === 'text' ? Utilities::validateInput($_POST['additional_' . $key], PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT) : true); 
						if( !empty($addhere) ){
							$additionals[] = $addhere;
						}
						else{
							$this->error = LanguageManager::getTranslation('FillAdditionals');
							return false;
						}
					}
					else if ( empty( $_POST['additional_' . $key] ) && $add['require'] ){
						$this->error = LanguageManager::getTranslation('FillAdditionals');
						return false;
					}
					else{
						$additionals[] = ($add['type'] === 'text' ? '' : false); 
					}
				}
			}

			//save
			$editcode = sha1($name . $mail) . Utilities::randomCode(10,Utilities::POLL_ID);
			$addedids = array();
			$userar = array(
				"name" => $name,
				"mail" => $mail,
				"showuser" => $showuser,
				"time" => time(),
				"editcode" => $editcode,
				"additionals" => $additionals
			);
			foreach( $termine as $id ){
				if( $this->pollsub->isValue( [$id] ) ){
					$ok = $this->pollsub->setValue( [$id, null], $userar );
				}
				else{
					$ok = $this->pollsub->setValue( [$id], array( $userar ) );
				}
				$addedids[] = $id;
			}
			
			if( $ok === false ){
				$this->error = LanguageManager::getTranslation('PollSaveErr');
				return false;
			}

			//load other template, if ok
			$it = new Template( 'pollsaved' );
			$template->includeTemplate($it);

			//message
			$it->setContent('POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )));
			$it->setContent('TERMINE', '<ul><li>' . implode( '</li><li>', $termine_text ) . '</li></ul>' );
			$it->setContent('NAME', Utilities::optimizeOutputString( $name ));
			$it->setContent('EMAIL', Utilities::optimizeOutputString( $mail ));
			$it->setContent('REDOLINK', URL::currentLinkGenerator());

			$it->setContent('POLLID', $this->id); 
			$it->setContent('VALUES', json_encode($addedids)); 
			$it->setContent('CODE', $editcode); 

			//logfile [PollID, Name, Mail, [Option IDs], Timestamp, [Additional Inputs]]
			file_put_contents(
				__DIR__ . '/../data/pollsubmissions.log',
				json_encode( array( $this->id, 'submission', $name, $mail, $termine, time(), $additionals ) ) . "\r\n",
				FILE_APPEND | LOCK_EX );

			if( $this->polldata->isValue(['notifymails']) ){
				$tos = $this->polldata->getValue(['notifymails']);
				if( !empty($tos)){
					$m = new Mail( 'AdminNotif' );

					$m->setContent('POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )));
					$m->setContent('TERMINE', '<ul><li>' . implode( '</li><li>', $termine_text ) . '</li></ul>');
					$m->setContent('NAME', Utilities::optimizeOutputString( $name ));
					$m->setContent('EMAIL', Utilities::optimizeOutputString( $mail ));
					$m->setContent('ADMINLINK', URL::generateLink('admin', '', $this->polldata->getValue(['code', 'admin'])));
					
					foreach( $tos as $to ){
						$m->sendMail( $to );
					}
				}
			}

			return true;
		}
		else{
			$this->error = $capok ? LanguageManager::getTranslation('EinwillErr') : Captcha::getError();
			return false;
		}
	}

	/**
	 * Show the form to fill out the poll
	 * @param $template the poll template
	 */
	public function showPollForm( $template ){
		if( $this->pollsub === false ){
			$this->pollsub = new JSONReader( 'pollsub_' . $this->id ); //not exclusive
		}

		$template->setContent( 'FORMDEST', URL::currentLinkGenerator() );
		$template->setContent( 'POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )) );
		$template->setContent( 'POLLDESCRIPT', Utilities::optimizeOutputString($this->polldata->getValue( ['description'] )) );
		$template->setContent( 'POLLID', $this->id );
		$template->setContent( 'DELSUBAPI', URL::generateAPILink( 'delsubmission', array( 'poll' =>  $this->id ) ) );
		$template->setContent( 'SUBMQUERYLINK', SubmissionQuery::getLink($this->id) );
		
		$type = $this->polldata->getValue( ['formtype'] );

		if( $type === 'meeting' ){ // meetings will always list the names!
			$template->setContent( 'ATTRSHOWNAME',  'checked="checked" disabled="disabled"');
		}

		$termine = array();
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			$schon = $this->pollsub->isValue( [$id] ) ? count( $this->pollsub->getValue( [$id] ) ) : 0; 

			if( $type === 'person' ){
				$anzval = $schon . '/' . $values["anz"];
				$disable = $schon >= $values["anz"] ? ' disabled="disabled"' : '';
			}
			else{
				$disable = '';
				$anzval = $schon;
			}
			if( $schon > 0 ){
				$names = array();
				foreach( $this->pollsub->getValue( [$id] ) as $user){
					if( $type === 'meeting' || ( !empty($user['showuser']) && $user['showuser'] ) ){
						$names[] = Utilities::optimizeOutputString( $user['name'] );
					}
				}
				if( !empty($names) ){
					$anzval = Utilities::getCollapseHtml(
						LanguageManager::getTranslation("Teilnehm") . ' &ndash; ' . $anzval,
						'<ul class="list-group"><li class="list-group-item">' .  implode( '</li><li class="list-group-item">',  $names ). '</li></ul>'
					);
				}
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
		//notes?
		if( $this->configjson->getValue(['texts', 'enablePoll']) ){
			$template->setContent( 'TEXTSEINWILL',
				Utilities::getRowHtml(
					'<div class="form-group text-center"><input type="checkbox" name="textseinwill" class="form-check-input" value="yes"></div>',
					$this->configjson->getValue(['texts', 'textPoll']),
					'col-sm-1'
				)
			);
		}

		//additional inputs
		if( $this->polldata->isValue(['additionals']) ){
			$texts = array();
			$checks = array();
			foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
				if( $add['type'] === 'text' ){
					$texts[] = array(
						"ADDNAME" => "additional_" . $key,
						"ADDPLACEHOLDER" => $add['text'] . ( $add['require'] ? '' : ' (optional)' )
					);
				}
				else {
					$checks[] = array(
						"ADDNAME" => "additional_" . $key,
						"ADDTEXT" => $add['text'] . ( $add['require'] ? '' : ' (optional)' )
					);
				}
			}
			if( !empty($texts) ){
				$template->setMultipleContent('AdditionalsText', $texts);
			}
			if( !empty($checks) ){
				$template->setMultipleContent('AdditionalsCheck', $checks);
			}
		}
	}		

	/**
	 * Getting the error, if saveSendData returned false
	 */
	public function getError(){
		return $this->error;
	}
}

?>