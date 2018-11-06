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
	}

	private function mainSetup(){
		$this->maintemplate->setContent( 'TITLE', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'SUBTITLE', LanguageManager::getTranslation( 'SUBTITLE' ) );
		$this->maintemplate->setContent( 'MAINBUTTONDEST', Utilities::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK'));

		$langsel = '';
		foreach( LanguageManager::getAllLanguages() as $key => $name ){
			$active = ($key == LanguageManager::getCurrentLanguage()) ? ' active' : '';
			$langsel .= '<button type="button" class="btn btn-secondary'. $active .'" linkdest="'. Utilities::currentLinkGenerator($append = '&language='.$key) .'">'.$name.'</button>';
		}
		$this->maintemplate->setContent( 'LANGUAGEBUTTONS', $langsel );
	}

	private function taskNew(){
	
	}

	private function taskAdmin(){

	}

	private function taskPoll(){

	}

	private function taskStart(){
		
	}

	public function __destruct(){
		$this->maintemplate->output();
	}
}

?>