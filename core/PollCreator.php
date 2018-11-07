<?php

class PollCreator{

	/**
	 * Checks if there was data from the new poll form send.
	 */
	public static function checkForPostData(){
		return isset( $_POST['formtype'] );
	}

	/**
	 * Creates PollCreator
	 */
	public function __construct(){
		
	}

	/**
	 * Creates a new poll by POST data
	 * @return (bool) able to create?
	 * 	if no, see ->errorMessage()
	 * 	else, see ->getAdminLink()
	 */
	public function createPollByPostData(){
		$_POST;

		// poll created?
		return false;
	}

	private function validateInput(){
		//html?
		//max lenght
		// ...
	}

	/**
	 * Gets the link to the admin interface for the new generated poll
	 * @return the link, only if poll created
	 */
	public function getAdminLink(){
		//link to new poll admin page
		return Utilities::generateLink('admin');
	}
	
	/**
	 * Returns a error message, if no poll was created
	 */
	public function errorMessage(){
		return 'Not implemented!';
	}
}

?>