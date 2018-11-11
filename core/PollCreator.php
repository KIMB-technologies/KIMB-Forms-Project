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

class PollCreator{

	/**
	 * Input check constants
	 */
	const PREG_TEXTINPUT = '/[^A-Za-z0-9 \.\-_ÄÜÖäüöß]/';
	const PREG_TEXTAREA = '/[^A-Za-z0-9 :\/\*\#\.\-_ÄÜÖäüöß\r\n]/';
	const PREG_NUMBER = '/[^0-9]/';
	/**
	 * Max inputs lenghts
	 */
	const MAXL_NUMBER = 5;
	const MAXL_TEXTINPUT = 500;
	const MAXL_TEXTAREA = 3000;
	const MAX_TERMINE = 50;

	/**
	 * Form data storage
	 */
	private $data = array(
		'code' => array(),
		'pollname' => '',
		'formtype' => '',
		'description' => '',
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
			$this->data['pollname'] = substr( trim(preg_replace( self::PREG_TEXTINPUT, '' , $_POST['pollname'] )), 0, self::MAXL_TEXTINPUT);
			$this->data['formtype'] = $_POST['formtype'] == 'person' ? 'person' : 'meeting';
			$this->data['description'] = substr( trim(preg_replace( self::PREG_TEXTAREA, '' , $_POST['description'])), 0, self::MAXL_TEXTAREA);

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
					$h = isset( $_POST['hinweise'][$key] ) ? $_POST['hinweise'][$key] : '';

					$b = substr( trim(preg_replace( self::PREG_TEXTINPUT, '' , $b)), 0, self::MAXL_TEXTINPUT);
					if($a !== false){
						$a = intval( substr( trim(preg_replace( self::PREG_NUMBER, '' , $a)), 0, self::MAXL_NUMBER) );
					}
					if( $this->data['formtype'] == 'person' && empty($a) ){
						$error = true;
						$this->errormsg = LanguageManager::getTranslation( 'TermBenMaxAanz' );
					}

					if( !empty( $b ) ){ // dieses gefuellt?
						if( $h !== '' ){
							$h = substr( trim(preg_replace( self::PREG_TEXTAREA, '' , $h)), 0, self::MAXL_TEXTAREA);
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
		return Utilities::generateLink('admin', '', $this->data['code']['admin'] );
	}
	
	/**
	 * Returns a error message, if no poll was created
	 */
	public function errorMessage(){
		return $this->errormsg;
	}
}

?>