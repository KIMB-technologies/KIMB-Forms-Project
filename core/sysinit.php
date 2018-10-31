<?php
error_reporting(0);
session_name( 'KIMB-Forms-Project' );
session_start();

JSONReader::changepath( __DIR__ . '/../data/' );

$loader = new TemplateLoader();
$loader->decideOnTask( Utilities::urlParser()['task'] );
?>