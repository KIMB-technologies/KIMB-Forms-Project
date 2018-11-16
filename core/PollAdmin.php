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

class PollAdmin{

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

				$polls = new JSONReader( 'polls' );
				$admins = new JSONReader( 'admincodes' );

				$polls->setValue( [$code], null ); //delete poll from lists
				$admins->setValue( [$admin], null );

				unset( $this->polldata ); // unlock poll files
				unset( $this->pollsub );

				JSONReader::deleteFile( 'poll_' . $code ); // delete data
				JSONReader::deleteFile( 'pollsub_' . $code );

				http_response_code(303);
				header( 'Location: ' . Utilities::generateLink() );
				die();
			}
			else{
				$this->pollsub->setArray(array());
			}
		}
	}

	/**
	 * Show the poll template page
	 */
	private function showInfo(){
		$type = $this->polldata->getValue(['formtype']);

		$this->template->setContent( 'UMFRAGEID', $this->polldata->getValue(['code', 'poll']));
		$this->template->setContent( 'UMFRAGEIDLINK', Utilities::generateLink('poll', $this->polldata->getValue(['code', 'poll']), '') );
		$this->template->setContent( 'ADMINCODE', $this->polldata->getValue(['code', 'admin']));
		$this->template->setContent( 'ADMINCODELINK', Utilities::generateLink('admin', '', $this->polldata->getValue(['code', 'admin'])));

		$this->template->setContent( 'POLLNAME', $this->polldata->getValue(['pollname']) );
		$this->template->setContent( 'POLLDESCRIPT', $this->polldata->getValue(['description']) );
		$this->template->setContent( 'POLLTYPE',
			$type == 'meeting' ? LanguageManager::getTranslation( 'TermFin' ) : LanguageManager::getTranslation( 'HelfFin' )
		);
		$this->template->setContent( 'EXPORTLINK', Utilities::generateAPILink('export', array( 'type' => 'csv', 'admin' =>  $this->polldata->getValue(['code', 'admin']) ) ) );
		$this->template->setContent( 'PRINTLINK', Utilities::generateAPILink('export', array( 'type' => 'print', 'admin' =>  $this->polldata->getValue(['code', 'admin']) ) ) );

		$termine = array();
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			if( $type === 'person' ){
				$maxanz = '/' . $values["anz"];
			}
			else{
				$maxanz = '';
			}
			
			$i = 1;
			$submiss = array();
			foreach( $this->pollsub->getValue( [$id] ) as $sub){
				$i++;
				$submiss[] = Utilities::optimizeOutputString( $sub['name'] . ' (' . $sub['mail'] . ') ' );
			}

			$termine[] = array(
				"NAME" => Utilities::optimizeOutputString( $values["bez"] ),
				"ANZAHL" => ($i-1) . $maxanz,
				"HINWEISE" => Utilities::optimizeOutputString( $values["des"] ),
				"TERMINID" => "termin_" . $id,
				"TEILNEHMER" => implode( '</li><li class="list-group-item">',  $submiss )
			);

		}

		$this->template->setMultipleContent('Termin', $termine);

		$this->template->setContent( 'JSONDATA', json_encode( array(
			"delallurl" => Utilities::currentLinkGenerator( array( 'delete' => 'all' ) ),
			"delsuburl" => Utilities::currentLinkGenerator( array( 'delete' => 'sub' ) ),
		)));
	}
}
?>