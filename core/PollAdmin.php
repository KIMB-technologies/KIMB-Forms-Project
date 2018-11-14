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

		/*
		 * ToDo !!!!!
		 */
		$this->showInfo();
	}

	/**
	 * Show the poll template page
	 */
	public function showInfo(){
		
		/*
		 * ToDo !!!!!
		 */

		$this->template->setContent( 'INNERCONTAINER', '<pre>'.print_r($this->polldata->getArray(), true).print_r($this->pollsub->getArray(), true).'</pre>');
	}
}
?>