<?php

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