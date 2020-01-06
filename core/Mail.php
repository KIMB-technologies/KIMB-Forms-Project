<?php
/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 - 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'KIMB-FORMS-PROJECT' ) or die('Invalid Endpoint!');

/**
 * System Mailer Class
 * 	Uses PHP-Mail Function (to use SMTP use the Docker-Image, which uses internally https://hub.docker.com/r/kimbtechnologies/php_smtp_nginx)
 */
class Mail {

	private static $templates = array(
		'mailAdminNotif',
		'mailNewPollNotif',
		'mailPollSubm'
	);

	private $type, $template, $mailHeader;

	/**
	 * Creates the Mail (also using a HTML Template)
	 */
	public function __construct( string $mailType ){
		$this->type = 'mail' . $mailType;
		if( !in_array( $this->type, self::$templates ) ){
			throw new Exception('Unknown Mail Template Type');
		}
		$this->template = new Template( $this->type );

		$this->setUpMailMeta();
	}

	/**
	 * Set a content key in the mail template
	 */
	public function setContent( $key, $value ){
		$this->template->setContent($key, $value);
	}

	/**
	 * Set a multiple content key in the mail template
	 */
	public function setMultipleContent( $key, $value ){
		$this->template->setMultipleContent($key, $value);
	}

	/**
	 * Sets up the meta data for the mail (header, ...)
	 */
	private function setUpMailMeta(){
		$c = new Config();
		preg_match( '/https?:\/\/([^\/\:]+).*/', $c->getValue(['site', 'hosturl']), $match );
		if( !isset($match[1])){
			$match[1] = 'example.com';
		}

		$this->mailHeader = implode("\r\n", 
			array(
				'MIME-Version: 1.0',
				'Content-type: text/html; charset=utf-8',
				'From: KIMB-Forms <forms@' . $match[1] . '>'
			)
		);
	}

	/**
	 * Sends the created Mail 
	 * @param $to The destination mail address
	 */
	public function sendMail(string $to){
		mail(
			$to,
			LanguageManager::getTranslation($this->type),
			$this->template->getOutputString(),
			$this->mailHeader
		);
	}
}

?>