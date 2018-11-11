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

class PollAdmin{

	private $polldata,
		$template;

	/**
	 * Init polladmin by pollid and template for website
	 */
	public function __construct($pollid, $template){
		$this->polldata = new JSONReader( 'poll_' . $pollid );
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

		$this->template->setContent( 'INNERCONTAINER', '<pre>'.print_r($this->polldata->getArray(), true).'</pre>');
	}
}
?>