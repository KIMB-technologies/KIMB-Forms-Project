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

class PollAdmin{

	/**
	 * Checks the admin code, and dies if not valid
	 * @param $admincode the admincode to check (default 'admin' get param)
	 * @return polldata jsonreader, if ok else dies
	 */
	public static function authByAdmincode( $admincode = '' ){
		$polladmins = new JSONReader( 'admincodes' );

		if( empty( $admincode ) ){
			$admincode = $_GET['admin'];
		}
		
		if( !empty( $admincode ) && Utilities::checkFileName($admincode) && $polladmins->isValue( [ $admincode ] ) ){
			$pollid = $polladmins->getValue( [ $admincode ] );

			//ok
			return new JSONReader( 'poll_' . $pollid );
		}
		else{
			http_response_code(403);
			die( 'Unknown admin code.' );
		}
	}

	private $polldata,
		$pollsub,
		$template;

	/**
	 * Init polladmin by pollid and template for website
	 */
	public function __construct($pollid, $template){
		$this->polldata = new JSONReader( 'poll_' . $pollid );
		$this->pollsub = new JSONReader( 'pollsub_' . $pollid );
		$this->template = $template;

		$this->doDelete();
		$this->showInfo();
	}

	/**
	 * Checks for delete all or submissions query and does it
	 */
	private function doDelete(){
		if( !empty( $_GET['delete'] ) ){
			if( $_GET['delete'] === 'all' ){
				$code = $this->polldata->getValue(['code', 'poll']);
				$admin = $this->polldata->getValue(['code', 'admin']);

				// open id files exclusive
				$polls = new JSONReader( 'polls', true ); 
				$admins = new JSONReader( 'admincodes', true );

				$polls->setValue( [$polls->searchValue( [], $code)], null ); //delete poll from lists
				$admins->setValue( [$admin], null );

				// force system to write deleted poll ids
				unset($polls);
				unset($admins);

				unset( $this->polldata ); // unlock poll files
				unset( $this->pollsub );

				JSONReader::deleteFile( 'poll_' . $code ); // delete data
				JSONReader::deleteFile( 'pollsub_' . $code );

				http_response_code(303);
				header( 'Location: ' . URL::generateLink() );
				die();
			}
			else if( !empty($_SESSION['DELETE_SUBMISSIONS_CODE']) && $_GET['delete'] == $_SESSION['DELETE_SUBMISSIONS_CODE'] ){
				$this->pollsub->setArray(array());
				file_put_contents(
					__DIR__ . '/../data/pollsubmissions.log',
					json_encode( array( $this->polldata->getValue(['code', 'poll']), 'deleted all submissions', time() ) ) . "\r\n",
					FILE_APPEND | LOCK_EX );
			}
		}
	}

	/**
	 * Show the poll template page
	 */
	private function showInfo(){
		$type = $this->polldata->getValue(['formtype']);

		$this->template->setContent( 'UMFRAGEID', $this->polldata->getValue(['code', 'poll']));
		$this->template->setContent( 'UMFRAGEIDLINK', URL::generateLink('poll', $this->polldata->getValue(['code', 'poll']), '') );
		$this->template->setContent( 'ADMINCODE', $this->polldata->getValue(['code', 'admin']));
		$this->template->setContent( 'ADMINCODELINK', URL::generateLink('admin', '', $this->polldata->getValue(['code', 'admin'])));

		$this->template->setContent( 'POLLNAME', $this->polldata->getValue(['pollname']) );
		$this->template->setContent( 'POLLDESCRIPT', $this->polldata->getValue(['description']) );
		$this->template->setContent( 'POLLTYPE',
			$type == 'meeting' ? LanguageManager::getTranslation( 'TermFin' ) : LanguageManager::getTranslation( 'HelfFin' )
		);
		$this->template->setContent( 'EXPORTLINK', URL::generateAPILink('export', array( 'type' => 'csv', 'admin' =>  $this->polldata->getValue(['code', 'admin']) ) ) );
		$this->template->setContent( 'PRINTLINK', URL::generateAPILink('export', array( 'type' => 'print', 'admin' =>  $this->polldata->getValue(['code', 'admin']) ) ) );

		if( $this->polldata->isValue(['notifymails']) ){
			$this->template->setContent( 'NOTIFMAILS', implode( ',', $this->polldata->getValue(['notifymails']) ) );
		}

		$termine = array();
		$terminmeta = array();
		$submissempty = true;
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			if( $type === 'person' ){
				$maxanz = '/' . $values["anz"];
			}
			else{
				$maxanz = '';
			}
			
			$i = 1;
			$submiss = array();
			if( $this->pollsub->isValue( [$id] ) ){
				foreach( $this->pollsub->getValue( [$id] ) as $sub){
					$i++;
					$submiss[] = Utilities::optimizeOutputString(
							$sub['name'] . ' (' . $sub['mail'] . ') '
						) . (!empty($sub['editcode']) ? '<span class="ui-icon ui-icon-trash delsinglesub" subcode="'.$id.','.$sub['editcode'].'"></span>' : '' );
					$submissempty = false;
				}
			}

			$termine[] = array(
				"NAME" => Utilities::optimizeOutputString( $values["bez"] ),
				"ANZAHL" => ($i-1) . $maxanz,
				"HINWEISE" => Utilities::optimizeOutputString( $values["des"] ),
				"TERMINID" => "termin_" . $id,
				"TEILNEHMER" => Utilities::getCollapseHtml(
					LanguageManager::getTranslation("Teilnehm"),
					'<ul class="list-group"><li class="list-group-item">' . implode( '</li><li class="list-group-item">',  $submiss ) . '</li></ul>'
				)
			);

			$terminmeta["termin_" . $id] = array( $values["bez"], $values["anz"], $values["des"]  );
		}

		$this->template->setMultipleContent('Termin', $termine);

		if( $this->polldata->isValue(['additionals']) ){
			$additionals = array();
			foreach( $this->polldata->getValue(['additionals']) as $adds ){
				$additionals[] = array(
					"ADDDATA" =>  $adds['type'] . "," . ($adds['require'] ? 'true' : 'false'),
					"ADDTYPE" => ($adds['type'] == 'text' ? 'pencil' : 'check'),
					"ADDNAME" => Utilities::optimizeOutputString( $adds['text'] ),
					"REQOPT" => ($adds['require'] ? '*' : '(optional)'),
				);
			}
			$this->template->setMultipleContent('Additionals', $additionals);
		}

		$_SESSION['DELETE_SUBMISSIONS_CODE'] = Utilities::randomCode(6, Utilities::POLL_ID);

		$this->template->setContent( 'JSONDATA', str_replace( array("\\r", "\\n"), array( "\\\\r", "\\\\n"), json_encode(
			array(
				"delallurl" => URL::currentLinkGenerator( array( 'delete' => 'all' ) ),
				"delsuburl" => URL::currentLinkGenerator( array( 'delete' => $_SESSION['DELETE_SUBMISSIONS_CODE'] ) ),
				"polladmin" => URL::generateLink('admin', '', $this->polldata->getValue(['code', 'admin'])),
				"meta" => array(  $this->polldata->getValue(['pollname']), $this->polldata->getValue(['description']) ),
				"terminmeta" => $terminmeta,
				"editurl" => URL::generateAPILink( 'editpoll', array( 'admin' =>  $this->polldata->getValue(['code', 'admin']) ) ),
				"polltype" => $type,
				"submissempty" => $submissempty,
				"delsinglesub" => URL::generateAPILink( 'delsubmission', array( 'poll' => $this->polldata->getValue(['code', 'poll']) ) )
			)
		)));
	}
}
?>