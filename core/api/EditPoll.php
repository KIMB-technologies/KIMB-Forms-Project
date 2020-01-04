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

class EditPoll{

	private $polldata;

	/**
	 * Does the change in the polls json file
	 */
	public function __construct(){
		header('Content-Type: text/plain; charset=utf-8');

		$this->polldata = PollAdmin::authByAdmincode(); //dies, if not ok

		if( !empty( $_POST['name'] ) && !empty( $_POST['termin'] ) && isset( $_POST['hinw'] ) && isset( $_POST['anz'] ) ){
			//termin aendern
			$this->changeDate( $_POST['name'], $_POST['hinw'], $_POST['anz'], $_POST['termin'] );
		}
		else if( !empty( $_POST['name'] ) && !empty( $_POST['desc'] ) ){
			//poll meta aendern
			$this->changePoll( $_POST['name'], $_POST['desc'] );
		}
		else if( isset( $_POST['maillist'] ) ) {
			//set poll notifications mails 
			$this->setMailList( $_POST['maillist'] );
		}
		else if( !empty( $_POST['additionals'] ) ) {
			//save additional fields
			$this->setAdditionals( $_POST['additionals'] );
		}
		die( 'nok' );
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
			if( $t === 'addadate' ){
				$id = null; // means append
				$searcharr = ['termine'];
			}
			else{
				$id = intval( preg_replace( '/[^0-9]/', '', $t ) );
				$searcharr = ['termine', $id];
			}

			if( $this->polldata->isValue($searcharr) ){
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

	private function setMailList( string $list ){
		$mails = Utilities::validateInput($list, Poll::PREG_MAIL, 500);
		$mails = explode( ',', $list );
		$mails = array_map(
				function ($m) {
					return  trim( $m );
				},
				$mails
			);
		$savemails = array();
		foreach( $mails as $m ){
			if( filter_var( $m, FILTER_VALIDATE_EMAIL ) !== false ){
				$savemails[] = $m;
			}
		}
		$this->polldata->setValue(['notifymails'], $savemails);
		die('ok');
	}

	private function setAdditionals( $array ){
		if( ( isset($array['empty']) && $array['empty'] == 'true' )
			|| ( isset($array['data'] ) && !is_array($array['data']) )
			|| empty($array['data'])
		){
			$save = array(); // no additional fields
		}
		else{
			$save = array();
			foreach($array['data'] as $f ){
				if( !empty($f['text'])){
					$type = (!empty($f['type']) && $f['type'] == 'text') ? 'text' : 'checkbox';
					$req = (isset($f['require']) && $f['require'] == 'true');
					$text = Utilities::validateInput($f['text'], PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT);

					if( !empty($text)){
						$save[] = array(
							'type' => $type,
							'require' => $req,
							'text' => $text
						);
					}
				}
			}
			
		}
		$this->polldata->setValue(['additionals'], $save);
		die('ok');
	}


	
}

?>