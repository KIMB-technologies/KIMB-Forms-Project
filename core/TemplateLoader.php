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

class TemplateLoader{

	private $maintemplate,
		$includetemp,
		$configjson,
		$htmloutput = true; 

	/**
	 * All possible URL Tasks
	 */
	private static $tasks = array(
			'admin',
			'new',
			'poll',
			'start'
		);

	/**
	 * Init Template Loader
	 */
	public function __construct(){
		$this->maintemplate = new Template( 'main' );
		$this->configjson = new JSONReader( 'config' );

		$this->maintemplate->setContent( 'HOST', $this->configjson->getValue( ['site', 'hosturl'] ) );
		$this->maintemplate->setContent( 'PAGENAME', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'MOREFOOTER', $this->configjson->getValue( ['site', 'footercontent'] ) );
	}

	/**
	 * Decide what to display on URL Task
	 */
	public function decideOnTask( $task ){
		if( is_string($task) && in_array( $task, self::$tasks ) ){
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
			$this->maintemplate->setContent( 'MAINBUTTONDEST', URL::generateLink('start') );
			$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-start'));
			http_response_code(404);
		}
	}

	/**
	 * Setup for main Template
	 */
	private function mainSetup(){
		$this->maintemplate->setContent( 'TITLE', $this->configjson->getValue( ['site', 'pagename'] ) );
		$this->maintemplate->setContent( 'SUBTITLE', LanguageManager::getTranslation( 'SUBTITLE' ) );

		$langsel = '';
		foreach( LanguageManager::getAllLanguages() as $key => $name ){
			$active = ($key == LanguageManager::getCurrentLanguage()) ? ' active' : '';
			$langsel .= '<button type="button" class="btn btn-secondary'. $active .'" linkdest="'. URL::currentLinkGenerator(array( 'language' => $key ) ) .'">'.$name.'</button>';
		}
		$this->maintemplate->setContent( 'LANGUAGEBUTTONS', $langsel );
	}

	/**
	 * Setup for new Task
	 */
	private function taskNew(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', URL::generateLink('start') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-start'));

		if( PollCreator::checkForPostData() ){
			$capok = ( $this->configjson->getValue(['captcha', 'new']) && Captcha::checkImageData() ) || !$this->configjson->getValue(['captcha', 'new']);
			$einwill = ( ( $this->configjson->getValue(['texts', 'enableNew']) && !empty( $_POST['textseinwill'] )) || !$this->configjson->getValue(['texts', 'enableNew']) );
			$pollcreator = new PollCreator();
			if( $capok && $einwill && $pollcreator->createPollByPostData() ){ // Fehler??
				$this->htmloutput = false;
				http_response_code(303);
				header( 'Location: ' . $pollcreator->getAdminLink() );
				die();
			}
			else{
				$alert = new Template( 'alert' );
				$this->includetemp->includeTemplate($alert);
				$alert->setContent( 'ALERTMESSAGE', $capok ? ( $einwill ? $pollcreator->errorMessage() : LanguageManager::getTranslation('EinwillErr') ) : Captcha::getError() );
			}
		}
		$this->includetemp->setContent( 'FORMDEST', URL::generateLink('new') );
		if( $this->configjson->getValue(['captcha', 'new']) ){
			$this->includetemp->setContent( 'CAPTCHA', Captcha::getCaptchaHTML() );
		}
		if( $this->configjson->getValue(['texts', 'enableNew']) ){
			$this->includetemp->setContent( 'TEXTSEINWILL',
				Utilities::getRowHtml(
					'<input type="checkbox" class="form-control" name="textseinwill" value="yes">',
					$this->configjson->getValue(['texts', 'textNew']),
					'col-sm-1'
				)
			);
		}
	}

	/**
	 * Setup for Admin Task
	 */
	private function taskAdmin(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', URL::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-new'));

		$polladmins = new JSONReader( 'admincodes' );
		$admincode = URL::urlParser()['admincode'];

		if( Utilities::checkFileName($admincode) && $polladmins->isValue( [ $admincode ] ) ){
			$pollid = $polladmins->getValue( [ $admincode ] );

			$admin = new PollAdmin( $pollid, $this->includetemp );
		}
		else{
			$alert = new Template( 'alert' );
			$this->maintemplate->includeTemplate($alert);
			$alert->setContent( 'ALERTMESSAGE', LanguageManager::getTranslation('UnBeAdmC') );
		}		
	}

	/**
	 * Setup for do Poll Task
	 */
	private function taskPoll(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', URL::generateLink('start') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK', LanguageManager::getTranslation('MAINBUTTONTASK-start'));

		$polls = new JSONReader( 'polls' );
		$pollid = URL::urlParser()['pollid'];

		if( Utilities::checkFileName($pollid) && in_array( $pollid, $polls->getArray() ) ){
			$poll = new Poll( $pollid );
			if( $poll->checkSend() ){ // if poll form send
				if( !$poll->saveSendData( $this->maintemplate ) ){
					$poll->showPollForm( $this->includetemp );

					$alert = new Template( 'alert' );
					$this->includetemp->includeTemplate($alert);
					$alert->setContent( 'ALERTMESSAGE', $poll->getError() );
				}
				//else { template done in saveSendData }
			}
			else{
				$poll->showPollForm( $this->includetemp );
			}
		}
		else{
			$alert = new Template( 'alert' );
			$this->maintemplate->includeTemplate($alert);
			$alert->setContent( 'ALERTMESSAGE', LanguageManager::getTranslation('UnBeUmfr') );
		}		
	}

	/**
	 * Setup for Start Task
	 */
	private function taskStart(){
		$this->maintemplate->setContent( 'MAINBUTTONDEST', URL::generateLink('new') );
		$this->maintemplate->setContent( 'MAINBUTTONTASK',  LanguageManager::getTranslation('MAINBUTTONTASK-new'));

		$this->includetemp->setContent( 'URLLOS', URL::generateLink('new') );
		$this->includetemp->setContent( 'URLPOLL', URL::generateLink('poll', '<poll>') );
		$this->includetemp->setContent( 'URLADMIN', URL::generateLink('admin', '', '<admin>') );
	}

	/**
	 * End, output all, if not deactivated
	 */
	public function __destruct(){
		if( $this->htmloutput ){
			$this->maintemplate->output();
		}
	}
}

?>