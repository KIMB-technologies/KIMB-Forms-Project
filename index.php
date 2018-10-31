<?php

require_once( __DIR__ . '/core/include.php' );

$t = new Template( 'main' );
$p = new Template( 'poll' );
$t->includeTemplate($p);
$t->output();

?>
