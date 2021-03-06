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

class DelUserSubmission {

	/**
	 * Input validate
	 */
	const PREG_CODE = '/[^A-Z0-9a-z]/';
	const PREG_ID = '/[^0-9]/';

	const MAXL_CODE = 50;
	const MAXL_ID = 10;

	/**
	 * Poll submission storages and pollid
	 */
	private $pollid, $pollsub, $polldata;

	/**
	 * Deletes a submisson from a poll
	 * GET poll => PollID
	 * POST terminid => Termin, code => deletion code
	 */
	public function __construct(){
		header('Content-Type: text/plain; charset=utf-8');

		if( isset( $_REQUEST['terminid'] ) && !empty( $_REQUEST['code'] ) ){
			// open 
			$this->openPollSubmissions();

			//delete termin
			$id = intval(Utilities::validateInput($_REQUEST['terminid'], self::PREG_ID, self::MAXL_ID ));
			$code = Utilities::validateInput($_REQUEST['code'], self::PREG_CODE, self::MAXL_CODE );
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
			$this->pollid = $_GET['poll'];
			if( Utilities::checkFileName($this->pollid) && in_array( $this->pollid, $polls->getArray() ) ){
				$this->pollsub = new JSONReader( 'pollsub_' . $this->pollid, true ); //exclusive
				$this->polldata = new JSONReader( 'poll_' . $this->pollid );
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
				$name = $submisson[$key]['name'];
				$mail = $submisson[$key]['mail'];

				unset($submisson[$key]);
				$this->pollsub->setValue([$id], array_values($submisson));

				// log delete [PollID, Name, Mail, [Option ID], Timestamp]
				file_put_contents(
					__DIR__ . '/../../data/pollsubmissions.log',
					json_encode( array( $this->pollid, 'delted entry' , $name, $mail, [$id], time() ) ) . "\r\n",
					FILE_APPEND | LOCK_EX );
				
				$this->informAdmin($name, $mail, $id);

				die('ok');
			}
		}
	}

	/**
	 * Send Mail to Poll Admin
	 */
	private function informAdmin(string $name, string $mail, int $termin) {
		if( $this->polldata->isValue(['notifymails']) ){
			$tos = $this->polldata->getValue(['notifymails']);
			if( !empty($tos) ){
				$m = new Mail( 'AdminDel' );

				$m->setContent('POLLNAME', Utilities::optimizeOutputString($this->polldata->getValue( ['pollname'] )));
				$m->setContent('TERMINE', Utilities::optimizeOutputString( $this->polldata->getValue( ['termine', $termin, 'bez'] ) ) );
				$m->setContent('NAME', Utilities::optimizeOutputString( $name ));
				$m->setContent('EMAIL', Utilities::optimizeOutputString( $mail ));
				$m->setContent('ADMINLINK', URL::generateLink('admin', '', $this->polldata->getValue(['code', 'admin'])));
				
				foreach( $tos as $to ){
					$m->sendMail( $to );
				}
			}
		}
	}
}

?>