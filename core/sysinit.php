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

error_reporting(0);
session_name( 'KIMB-Forms-Project' );
session_start();

JSONReader::changepath( __DIR__ . '/../data/' );
LanguageManager::init();

if( constant( 'KIMB-FORMS-PROJECT' ) === 'PAGE' ){ //load as normal HTML page
	$loader = new TemplateLoader();
	$loader->decideOnTask( Utilities::urlParser()['task'] );
}
else if( constant( 'KIMB-FORMS-PROJECT' ) === 'API'){ //load as API Request (AJAX, etc.)
	require_once( __DIR__ . '/api/api.php' );
}
else{
	die('Invalid Endpoint!');
}
?>