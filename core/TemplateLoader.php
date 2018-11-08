<?php

class TemplateLoader{

	private $maintemplate,
		$includetemp,
		$configjson,
		$htmloutput = true; 

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

			$this->mainSetup();

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
		else{
			$this->mainSetup();
			$this->includetemp = new Template( 'error' );
			$this->maintemplate->includeTemplate($this->includetemp);
			http_response_code(404);
		}
	}

	private function mainSetup(){
		$this->maintemplate->setContent( 'TITLE', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'SUBTITLE', LanguageManager::getTranslation( 'SUBTITLE' ) );

		$langsel = '';
		foreach( LanguageManager::getAllLanguages() as $key => $name ){
			$active = ($key == LanguageManager::getCurrentLanguage()) ? ' active' : '';
			$langsel .= '<button type="button" class="btn btn-secondary'. $active .'" linkdest="'. Utilities::currentLinkGenerator(array( 'language' => $key ) ) .'">'.$name.'</button>';
		}
		$this->maintemplate->setContent( 'LANGUAGEBUTTONS', $langsel );
	}

	private function taskNew(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('start') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-start'));

		if( PollCreator::checkForPostData() ){
			$pollcreator = new PollCreator();
			if( $pollcreator->createPollByPostData() ){ // Fehler??
				$this->htmloutput = false;
				http_response_code(303);
				header( 'Location: ' . $pollcreator->getAdminLink() );
				die();
			}
			else{
				$alert = new Template( 'alert' );
				$this->includetemp->includeTemplate($alert);
				$alert->setContent( 'ALERTMESSAGE', $pollcreator->errorMessage() );
			}
		}
		$this->includetemp->setContent( 'FORMDEST', Utilities::generateLink('new') );
	}

	private function taskAdmin(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-new'));
	}

	private function taskPoll(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('start') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-start'));
	}

	private function taskStart(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-new'));

		$this->includetemp->setContent( 'URLLOS', Utilities::generateLink('new') );
		$this->includetemp->setContent( 'URLPOLL', Utilities::generateLink('poll', '<poll>') );
		$this->includetemp->setContent( 'URLADMIN', Utilities::generateLink('admin', '', '<admin>') );
	}

	public function __destruct(){
		if( $this->htmloutput ){
			$this->maintemplate->output();
		}
	}
}

?>