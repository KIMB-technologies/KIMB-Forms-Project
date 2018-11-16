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

class EditPoll{

	private $pollid,
		$pollsub,
		$polldata;

	/**
	 * Does the change in the polls json file
	 */
	public function __construct(){
		header('Content-Type: text/plain; charset=utf-8');
		$this->auth(); //dies, if not ok

		if( !empty( $_POST['name'] ) && !empty( $_POST['termin'] ) && isset( $_POST['hinw'] ) && isset( $_POST['anz'] ) ){
			//termin aendern
			$this->changeDate( $_POST['name'], $_POST['hinw'], $_POST['anz'], $_POST['termin'] );
		}
		else if( !empty( $_POST['name'] ) && !empty( $_POST['desc'] ) ){
			//poll meta aendern
			$this->changePoll( $_POST['name'], $_POST['desc'] );
		}
		die( 'nok' );
	}

	/**
	 * Checks the admin code, and dies if not valid
	 * 	if ok, opens polldata and pollsubmissions
	 */
	private function auth(){
		$polladmins = new JSONReader( 'admincodes' );
		$admincode = $_GET['admin'];

		if( Utilities::checkFileName($admincode) && $polladmins->isValue( [ $admincode ] ) ){
			$this->pollid = $polladmins->getValue( [ $admincode ] );
			$this->polldata = new JSONReader( 'poll_' . $this->pollid );

			//ok
		}
		else{
			http_response_code(403);
			die( 'Unknown admin code.' );
		}

	}

	private function changePoll( $n, $d ){
		$n = Utilities::validateInput($n, PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT);
		$d = Utilities::validateInput($d, PollCreator::PREG_TEXTAREA, PollCreator::MAXL_TEXTAREA);

		if( !empty( $n ) && !empty( $d ) ){
			$this->polldata->setValue(['pollname'], $n);
			$this->polldata->setValue(['description'], $d);
			die( 'ok' );
		}
	}

	private function changeDate( $n, $h, $a, $t ){
		$n = Utilities::validateInput($n, PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT);
		$a = $this->polldata->getValue(['formtype']) === 'meeting' ? false : Utilities::validateInput($a, PollCreator::PREG_NUMBER, PollCreator::MAXL_NUMBER);
		$h = Utilities::validateInput($h, PollCreator::PREG_TEXTAREA, PollCreator::MAXL_TEXTAREA);

		if( $this->polldata->getValue(['formtype']) !== 'meeting' && !( $a === 0 || !empty( $a ) )  ){
			die('nok');
		}

		if( !empty( $n ) && is_string( $t )){
			$id = intval( preg_replace( '/[^0-9]/', '', $t ) );

			if( $this->polldata->isValue(['termine', $id] ) ){
				$this->polldata->setValue(['termine', $id],
					array(
						'bez' => $n,
						'anz' => $a,
						'des' => $h
					)
				);
				die('ok');
			}
		}
	}
}

?>