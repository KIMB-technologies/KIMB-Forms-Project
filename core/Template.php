<?php

/**
 * System Template class
 * 	Each Template consists of an <name>.html and <name>.json
 * 	The JSON defines all Placeholders used in the Template and the default values.
 * 	Templates can be included in each other, while the content of the innder goes to:
 * 		%%INNERCONTAINER%%
 */
class Template{
	
	/**
	 * Name, Placeholderdata and included Template
	 */
	private $filename = '';
	private $placeholder = array();
	private $inner = null;

	/**
	 * Create an new Template
	 * @param name The name of the template ./templates/<name>.html(.json)
	 */
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

	/**
	 * Setting the content for one of the placeholders
	 * @param $key placeholder
	 * @param $value html value
	 */
	public function setContent($key, $value){
		$key = "%%".str_replace("%%", "", $key)."%%";
		if( isset( $this->placeholder[$key] ) ){
			$this->placeholder[$key] = $value;
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Includes a Tempalte in this. (Output of included on will be
	 * 	put in %%INNERCONTAINER%%)
	 * @param $template the template object to include
	 */
	public function includeTemplate( $template ){
		if( get_class( $template ) === 'Template' ){
			$this->inner = $template;
			return true;
		}
		return false;
	}

	/**
	 * Getting the output of this template (incl. included ones)
	 */
	public function getOutputString(){
		if( $this->inner !== null ){
			$this->placeholder['%%INNERCONTAINER%%'] = $this->inner->getOutputString();
		}
		return str_replace(
				array_keys( $this->placeholder ),
				array_values( $this->placeholder ),
			$this->htmldata );
	}

	/**
	 * Output the page using this template.
	 */
	public function output(){
		header( 'Content-type:text/html; charset=utf-8' );
		echo $this->getOutputString();
	}
}

?>