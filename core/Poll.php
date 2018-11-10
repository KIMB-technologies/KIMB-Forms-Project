<?php

class Poll{

	private $polldata;

	/**
	 * Generate poll participate by poll id
	 */
	public function __construct( $pollid ){
		$this->polldata = new JSONReader( 'poll_' . $pollid );
	}

	/**
	 * Check if poll part. form send.
	 * @return boolean send?
	 */
	public function checkSend(){

		/*
		 * ToDo !!!!!
		 */
		return false;
	}

	/**
	 * Saves the send data from poll part form.
	 * @param $template the poll template
	 */
	public function saveSendData( $template ){

		/*
		 * ToDo !!!!!
		 * 
		 * Participation LOG
		 */
		$template->setContent( 'INNERCONTAINER', 'Saved!' );
	}

	/**
	 * Show the form to fill out the poll
	 * @param $template the poll template
	 */
	public function showPollForm( $template ){

		/*
		 * ToDo !!!!!
		 */
		$template->setContent( 'INNERCONTAINER','<pre>'.print_r(  $this->polldata->getArray(), true).'</pre>');
	}		
}

?>