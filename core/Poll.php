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
			$name = substr( trim(preg_replace( self::PREG_NAME, '' , $_POST['name'] )), 0, self::MAXL_NAME);
			$mail = empty( $_POST['email'] ) ? 'mail@mail.mail' : substr( trim(preg_replace( self::PREG_MAIL, '' , $_POST['email'] )), 0, self::MAXL_MAIL);;

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

			//save
			$userar = array(
				"name" => $name,
				"mail" => $mail,
				"time" => time()
			);
			foreach( $termine as $id ){
				if( $this->pollsub->isValue( [$id] ) ){
					$this->pollsub->setValue( [$id, null], $userar );
				}
				else{
					$this->pollsub->setValue( [$id], array( $userar ) );
				}
			}

			//load other template, if ok
			$it = new Template( 'pollsaved' );
			$template->includeTemplate($it);

			//message
			$it->setContent('POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )));
			$it->setContent('TERMINE', '<ul><li>' . implode( '</li><li>', $termine_text ) . '</li></ul>' );
			$it->setContent('NAME', Utilities::optimizeOutputString( $name ));
			$it->setContent('EMAIL', Utilities::optimizeOutputString( $mail ));
			$it->setContent('REDOLINK', Utilities::currentLinkGenerator());

			//logfile
			file_put_contents( __DIR__ . '/../data/pollsubmissions.log', json_encode( array( $this->id, $name, $mail, $termine, time() ) ) . "\r\n" , FILE_APPEND | LOCK_EX );

			return true;
		}
		else{
			$alert = new Template( 'alert' );
			$template->includeTemplate($alert);
			$alert->setContent( 'ALERTMESSAGE', $capok ? LanguageManager::getTranslation('EinwillErr') : Captcha::getError() );

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
				if( $this->pollsub->isValue( [$id] ) ){
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
		//notes?
		if( $this->configjson->getValue(['texts', 'enablePoll']) ){
			$template->setContent( 'TEXTSEINWILL',
				Utilities::getRowHtml(
					'<input type="checkbox" class="form-control" name="textseinwill" value="yes">',
					$this->configjson->getValue(['texts', 'textPoll']),
					'col-sm-1'
				)
			);
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