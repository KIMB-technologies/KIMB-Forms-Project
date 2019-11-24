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

class SubmissionQuery{

	private $polldata,
		$pollsub,
		$configjson,
		$id,
		$error = '';

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
	 * Show the 
	 */
	public function showForm(){
		$this->template->setContent( 'FORMDEST', URL::currentLinkGenerator(array('submission' => 'query')) );
	}

	public function getTemplate(): Template {
		return $this->template;
	}

	
}

?>