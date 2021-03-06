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

class PollCreator{

	/**
	 * Input check constants
	 */
	const PREG_TEXTINPUT = '/[^A-Za-z0-9 :\.\-_ÄÜÖäüöß]/';
	const PREG_TEXTAREA = '/[^A-Za-z0-9 :\/\,\*\#\.\-_ÄÜÖäüöß\r\n]/';
	const PREG_NUMBER = '/[^0-9]/';
	/**
	 * Max inputs lenghts
	 */
	const MAXL_NUMBER = 5;
	const MAXL_TEXTINPUT = 500;
	const MAXL_TEXTAREA = 3000;
	const MAX_TERMINE = 50;

	/**
	 * Max number of polls
	 */
	const MAX_POLLS = 1000;

	/**
	 * Form data storage
	 */
	private $data = array(
		'code' => array(),
		'pollname' => '',
		'formtype' => '',
		'description' => '',
		'notifymails' => array(),
		'termine' => array()
	);
	private $errormsg = '';

	/**
	 * Checks if there was data from the new poll form send.
	 */
	public static function checkForPostData(){
		return isset( $_POST['formtype'] );
	}

	/**
	 * Creates PollCreator
	 */
	public function __construct(){	
	}

	/**
	 * Creates a new poll by POST data
	 * @return (bool) able to create?
	 * 	if no, see ->errorMessage()
	 * 	else, see ->getAdminLink()
	 */
	public function createPollByPostData(){
		if( !$this->validateInput() ){
			return false;
		}

		$pollslist = new JSONReader( 'polls' );
		$polladmins = new JSONReader( 'admincodes' );

		if( count( $pollslist->getArray() ) > self::MAX_POLLS ){
			$this->errormsg = LanguageManager::getTranslation( 'TooManyPolls' );
			return false;
		}

		//Poll ID finden
		do{
			$pollid = Utilities::randomCode( 10, Utilities::POLL_ID );
		}while( in_array( $pollid, $pollslist->getArray() ) );
		$pollslist->setValue( [null], $pollid  );

		//Admin Code finden
		do{
			$admcode = Utilities::randomCode( 20, Utilities::ADMIN_CODE );
		}while( $polladmins->isValue( [ $admcode ] ) );
		$polladmins->setValue( [$admcode], $pollid  );

		//save polldata
		$poll = new JSONReader( 'poll_' . $pollid );
		$this->data['code']['admin'] = $admcode;
		$this->data['code']['poll'] = $pollid;
		$poll->setArray( $this->data );

		$this->sendNewPollMail();

		return true;
	}

	/**
	 * Checks POST data, moves to this->data 
	 * and does basic cleanup
	 */
	private function validateInput(){
		$error = false;
		if( !empty( $_POST['pollname'] )
			&& !empty( $_POST['formtype'] )
			&& !empty( $_POST['description'] )
		){
			$this->data['pollname'] = Utilities::validateInput($_POST['pollname'], self::PREG_TEXTINPUT, self::MAXL_TEXTINPUT);
			$this->data['formtype'] = $_POST['formtype'] == 'person' ? 'person' : 'meeting';
			$this->data['description'] = Utilities::validateInput($_POST['description'], self::PREG_TEXTAREA, self::MAXL_TEXTAREA);

			if( empty( $this->data['pollname'] )
				|| empty( $this->data['formtype'] )
				|| empty( $this->data['description'] )
			){
				$error = true;
				$this->errormsg = LanguageManager::getTranslation( 'NameTypBeschAngeb' );
			}
		}
		else{
			$error = true;
			$this->errormsg = LanguageManager::getTranslation( 'NameTypBeschAngeb' );
		}

		if( !$error && is_array($_POST['bezeichnungen']) && count($_POST['bezeichnungen']) > 0 ){
			if( count($_POST['bezeichnungen']) > self::MAX_TERMINE ){
				$error = true;
				$this->errormsg = LanguageManager::getTranslation( 'ZuVielTerm' );
			}
			else{
				$counter = 0;
				foreach( $_POST['bezeichnungen'] as $key => $value ){
					$b = $value; // = $_POST['bezeichnungen'][$key];
					$a = (empty( $_POST['anzahlen'][$key]) || $this->data['formtype'] === 'meeting' ) ? false : $_POST['anzahlen'][$key];
					$h = $_POST['hinweise'][$key] ?? '';

					$b = Utilities::validateInput($b, self::PREG_TEXTINPUT, self::MAXL_TEXTINPUT);
					if($a !== false){
						$a = Utilities::validateInput($a, self::PREG_NUMBER, self::MAXL_NUMBER);
					}
					if( $this->data['formtype'] == 'person' && empty($a) && !empty($b) ){
						$error = true;
						$this->errormsg = LanguageManager::getTranslation( 'TermBenMaxAanz' );
					}

					if( !empty( $b ) ){ // dieses gefuellt?
						if( $h !== '' ){
							$h = Utilities::validateInput($h, self::PREG_TEXTAREA, self::MAXL_TEXTAREA);
						}
						$this->data['termine'][] = array(
							'bez' => $b,
							'anz' => $a,
							'des' => $h
						);
						$counter++;
					}
				}
				if( $counter < 1 ){
					$error = true;
					$this->errormsg = LanguageManager::getTranslation( 'MinEinTermNoet' );
				}
			}
		}
		else if(!$error){
			$error = true;
			$this->errormsg = LanguageManager::getTranslation( 'MinEinTermNoet' );
		}
		return !$error;
	}

	/**
	 * Gets the link to the admin interface for the new generated poll
	 * @return the link, only if poll created
	 */
	public function getAdminLink(){
		//link to new poll admin page
		return URL::generateLink('admin', '', $this->data['code']['admin'] );
	}
	
	/**
	 * Returns a error message, if no poll was created
	 */
	public function errorMessage(){
		return $this->errormsg;
	}

	/**
	 * Checks if a mail should be send, when a new poll was created
	 * And sends the mail.
	 */
	private function sendNewPollMail() {
		$c = new Config();
		$to = $c->getValue(['newpollmailto']);
		if( empty( $to ) || $to == 'test@example.com' ){
			return;
		}
	
		$m = new Mail( 'NewPollNotif' );

		$m->setContent('POLLNAME', Utilities::optimizeOutputString( $this->data['pollname'] ));
		$m->setContent('POLLDESCRIP', Utilities::optimizeOutputString( $this->data['description'] ));
		$m->setContent('ADMINLINK', $this->getAdminLink() );
		$m->setContent('POLLLINK', URL::generateLink('poll', $this->data['code']['poll'], '' ));
		$m->setContent('POLLID', $this->data['code']['poll'] );
	
		$m->sendMail( $to, true );
	}
}

?>