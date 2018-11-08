<?php

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

		//create

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
				$this->errormsg = 'Bitte Namen, Typ und Beschreibung angeben!';
			}
		}
		else{
			$error = true;
			$this->errormsg = 'Bitte Namen, Typ und Beschreibung angeben!';
		}

		if( !$error && is_array($_POST['bezeichnungen']) && count($_POST['bezeichnungen']) > 0 ){
			if( count($_POST['bezeichnungen']) > self::MAX_TERMINE ){
				$error = true;
				$this->errormsg = 'Zu viele Termine!';
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
						$this->errormsg = 'Termine benötigen maximale Anzahl Personen!';
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
					$this->errormsg = 'Mindestens ein Termin benötigt!';
				}
			}
		}
		else if(!$error){
			$error = true;
			$this->errormsg = 'Mindestens ein Termin benötigt!';
		}
		return $error;
	}

	/**
	 * Gets the link to the admin interface for the new generated poll
	 * @return the link, only if poll created
	 */
	public function getAdminLink(){
		//link to new poll admin page
		return Utilities::generateLink('admin');
	}
	
	/**
	 * Returns a error message, if no poll was created
	 */
	public function errorMessage(){
		return $this->errormsg;
	}
}

?>