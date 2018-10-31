<?php

class TemplateLoader{

	private $maintemplate,
		$includetemp,
		$configjson; 

	private static $tasks = array(
		'admin',
		'new',
		'poll',
		'start'
	);

	public function __construct(){
		$this->maintemplate = new Template( 'main' );
		$this->configjson = new JSONReader( 'config' );

		$this->maintemplate->setContent( 'HOST', $this->configjson->getValue( ['site', 'hosturl'] ) );
		$this->maintemplate->setContent( 'PAGENAME', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'MOREFOOTER', $this->configjson->getValue( ['site', 'footercontent'] ) );
	}

	public function decideOnTask( $task ){
		if( in_array( $task, self::$tasks ) ){
			$this->includetemp = new Template( $task );
			$this->maintemplate->includeTemplate($this->includetemp);

			switch ($task){
				case 'new' :
					$this->taskNew();
					break;
				case 'admin' :
					$this->taskAdmin();
					break;
				case 'poll' :
					$this->taskPoll();
					break;
				default:
					$this->taskStart();
			}
		}
	}

	private function taskNew(){

	}

	private function taskPoll(){

	}

	private function taskAdmin(){

	}

	private function taskStart(){
		$this->maintemplate->setContent( 'TITLE', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'SUBTITLE', 'Small tool for coordinate volunteers and meetings.' );
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK', 'New Poll' );
	}

	

	public function __destruct(){
		$this->maintemplate->output();
	}
}

?>