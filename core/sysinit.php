<?php
error_reporting(0);
session_name( 'KIMB-Forms-Project' );
session_start();

JSONReader::changepath( __DIR__ . '/../data/' );
LanguageManager::init();

$loader = new TemplateLoader();
$loader->decideOnTask( Utilities::urlParser()['task'] );
?>