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

//global settings
error_reporting( !empty( $_ENV['DEVMODE'] ) && $_ENV['DEVMODE'] == 'true' ? E_ALL : 0 );
session_name( 'KIMB-Forms-Project' );
session_start();

// static env setups
Reader::changepath( __DIR__ . '/../data/' );
URL::setup();
LanguageManager::init();
CSSManager::init();

//load
if( constant( 'KIMB-FORMS-PROJECT' ) === 'PAGE' ){ //load as normal HTML page
	$loader = new TemplateLoader();
	$loader->decideOnTask( URL::urlParser()['task'] );
}
else if( constant( 'KIMB-FORMS-PROJECT' ) === 'API'){ //load as API Request (AJAX, etc.)
	require_once( __DIR__ . '/api/api.php' );
}
else{
	die('Invalid Endpoint!');
}
?>