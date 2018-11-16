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

$apitasks = array(
	'captcha',
	'export',
	'editpoll'
	// add more tasks
);

if( !empty( $_GET['task'] ) && is_string( $_GET['task'] ) && in_array($_GET['task'], $apitasks) ){
	if( $_GET['task'] == 'captcha' ){
		Captcha::showImage();
	}
	else if( $_GET['task'] == 'export' ){
		new Export();
	}
	else if( $_GET['task'] == 'editpoll' ){
		new EditPoll();
	}
	// add more tasks
}
else{
	header('Content-Type: text/plain; charset=utf-8');
	http_response_code(404);
	echo 'Invalid Request';
}
?>