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

/**
 * System Template class
 * 	Each Template consists of an <name>.html and <name>.json
 * 	The JSON defines all Placeholders used in the Template and the default values.
 * 	Templates can be included in each other, while the content of the innder goes to:
 * 		%%INNERCONTAINER%%
 */
class Template{
	/*
		Using this Template system, you must not allow users to insert strings like (wile xxx is some alphanum.)
			<!--MULTIPLE-xxxx-BEGIN-->, <!--MULTIPLE-xxxx-END-->, %%xxxx%%
	*/
	
	/**
	 * Name, Placeholderdata and included Template
	 */
	private $filename = '';
	private $placeholder = array();
	private $multiples = array();
	private $multiples_data = array();
	private $inner = null;

	private static $lang = 'de';
	private static $allLangs = array(
		'de',
		'en'
	);

	/**
	 * Change the language of the site
	 * see $allLangs for list
	 * @param lang the lang to use
	 */
	public static function setLanguage( $lang ){
		if( in_array( $lang, self::$allLangs ) ){
			self::$lang = $lang;
		}
	}

	/**
	 * Create an new Template
	 * @param name The name of the template
	 * 		./templates/<name>.json)
	 * 		./templates/<name>_<lang>.html
	 */
	public function __construct( $name ){
		if( Utilities::checkFileName( $name ) ) {
			try{
				$this->htmldata = file_get_contents( __DIR__ . '/templates/' . $name .  '_' . self::$lang . '.html' );
				$this->placeholder = json_decode( file_get_contents( __DIR__ . '/templates/' . $name . '.json' ) , true);
				if( isset($this->placeholder['multiples']) ){
					$this->multiples = $this->placeholder['multiples'];
					unset($this->placeholder['multiples']);
				}
			} catch (Exception $e) {
			    die( 'Unable to load Template data!' );
			}			
		}
	}

	/**
	 * Sets the content for one type of multiple page elements
	 * @param $name the name of the multiple page element
	 * @param $content the content for each part as array
	 * 	array(
	 * 		array(
	 * 			"key" => "val",
	 * 			//...
	 * 		)
	 * 		//...
	 * 	)
	 */
	public function setMultipleContent($name, $content){
		if( isset( $this->multiples[$name] ) ){
			$mults = array();
			foreach( $content as $data){
				$mul = $this->multiples[$name];
				foreach( $data as $key => $val){
					$key = "%%".str_replace("%%", "", $key)."%%";
					if( isset( $mul[$key] ) ){
						$mul[$key] = $val;
					}
				}
				$mults[] = $mul;
			}
			if( !empty($mults) ){
				$this->multiples_data[$name] = $mults;
			}
			return true;
		}
		else{
			return false;
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
		foreach( $this->multiples as $name => $val ){
			$a = explode( '<!--MULTIPLE-'.$name.'-BEGIN-->', $this->htmldata );
			$b = explode( '<!--MULTIPLE-'.$name.'-END-->', $this->htmldata );

			if( !empty($this->multiples_data[$name]) ){
				$inner = substr( $a[1], 0, strpos($a[1], '<!--MULTIPLE-'.$name.'-END-->') );
				$middle = '';
				foreach( $this->multiples_data[$name] as $data){
					$middle .= str_replace(
							array_keys( $data ),
							array_values( $data ),
						$inner );
				}
			}
			else{
				$middle = '';
			}
			$this->htmldata = $a[0] . $middle . $b[1];
		}

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