<?php

class Template{
	
	private $filename = '';
	private $placeholder = array();

	public function __construct( $name ){
		if( Utilities::checkFileName( $name ) ) {
			try{
				$this->htmldata = file_get_contents( __DIR__ . '/templates/' . $name . '.html' );
				$this->placeholder = json_decode( file_get_contents( __DIR__ . '/templates/' . $name . '.json' ) , true);
			} catch (Exception $e) {
			    die( 'Unable to load Template data!' );
			}			
		}
	}

	public function output(){
		echo str_replace( array_keys($this->placeholder), array_values( $this->placeholder ), $this->htmldata );
	}
}

?>
