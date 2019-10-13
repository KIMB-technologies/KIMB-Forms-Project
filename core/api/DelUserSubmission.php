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

class DelUserSubmission {

	/**
	 * Input validate
	 */
	const PREG_CODE = '/[^A-Z0-9a-z]/';
	const PREG_ID = '/[^0-9]/';

	const MAXL_CODE = 50;
	const MAXL_ID = 10;

	/**
	 * Poll submission storage
	 */
	private $pollsub;

	/**
	 * Deletes a submisson from a poll
	 * GET poll => PollID
	 * POST terminid => Termin, code => deletion code
	 */
	public function __construct(){
		header('Content-Type: text/plain; charset=utf-8');

		if( isset( $_POST['terminid'] ) && !empty( $_POST['code'] ) ){
			// open 
			$this->openPollSubmissions();

			//delete termin
			$id = intval(Utilities::validateInput($_POST['terminid'], self::PREG_ID, self::MAXL_ID ));
			$code = Utilities::validateInput($_POST['code'], self::PREG_CODE, self::MAXL_CODE );
			$this->deleteSubmission( $id, $code );
		}
		die( 'nok' );
	}

	/**
	 * Opens the poll submission file
	 */
	private function openPollSubmissions() {
		if( isset($_GET['poll']) ){
			$polls = new JSONReader( 'polls' );
			$pollid = $_GET['poll'];
			if( Utilities::checkFileName($pollid) && in_array( $pollid, $polls->getArray() ) ){
				$this->pollsub = new JSONReader( 'pollsub_' . $pollid, true ); //exclusive
			}
		}
		else{
			die('nok');
		}
	}

	/**
	 * Search Submisson and Delete
	 */
	private function deleteSubmission(int $id, string $code){
		if( $this->pollsub->isValue([$id]) ){
			$submisson = $this->pollsub->getValue([$id]);
			$key = false;
			foreach( $submisson as $k => $sub){
				if( $sub['editcode'] === $code ){
					$key = $k;
					break;
				}
			}
			if( $key !== false ){
				unset($submisson[$key]);
				$this->pollsub->setValue([$id], array_values($submisson));

				die('ok');
			}
		}
	}
}

?>