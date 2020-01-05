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

class SubmissionQuery {

	private $polldata,
		$pollsub,
		$configjson,
		$id;

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->id = $pollid;
		$this->configjson = new Config();
		$this->polldata = new JSONReader( 'poll_' . $pollid );
		$this->pollsub = new JSONReader( 'pollsub_' . $pollid );
		$this->template = new Template( 'submissionquery' );
	}

	/**
	 * Check if user tries to query his submissons
	 * @return boolean if user does
	 */
	public static function queried() : bool {
		return !empty($_GET['submission']) && $_GET['submission'] === 'query';
	}

	/**
	 * Generates the poll submission query Link 
	 * (Uses the current Link and adds parameter!)
	 * @return The link
	 */
	public static function getLink() : string {
		return URL::currentLinkGenerator(array('submission' => 'query'));
	}

	/**
	 * Show the 
	 */
	public function showForm(){
		$this->template->setContent( 'FORMDEST', self::getLink() );
		$this->template->setContent( 'BACKTOPOLLLINK', URL::currentLinkGenerator() );
		
		$this->template->setContent( 'JSONDATA', json_encode(
			array(
				'pollid' => $this->id,
				'termine' => array_map( function ($a) {
							return array(
								Utilities::optimizeOutputString( $a['bez'] ),
								Utilities::optimizeOutputString( mb_substr(
									Utilities::validateInput( $a['des'], PollCreator::PREG_TEXTINPUT, PollCreator::MAXL_TEXTINPUT  ),
									0, 100 ) )
							);
						},
						$this->polldata->getValue(['termine'])
					),
				"polllink" => URL::generateLink('poll','<poll>','')
			), JSON_FORCE_OBJECT
		));


	}

	public function getTemplate(): Template {
		return $this->template;
	}

}

?>