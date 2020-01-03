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
 * A JSON Reader, storage using json files, as defined in Reader.
 */
class JSONReader extends Reader {

	/**
	 * Deletes a JSON file. (needs exclusive rights)
	 */
	public static function deleteFile( string $name ) : bool{
		$file = parent::$path . $name . '.json';
		if( !is_file( $file ) && !is_file( $file . '.lock' ) ){
			return true;
		}
		file_put_contents( $file . '.lock', '', LOCK_EX );
		return (!is_file( $file ) || unlink( $file )) && (!is_file( $file . '.lock' ) || unlink( $file . '.lock' ));
	}

	// information about opened JSON
	private
		$filehandler, // fopen handler
		$data, // JSON data array
		$filepath, // full JSON file path
		$datahash, // hash of json data on disk
		$writeable = false; // file writeable?

	/**
	 * Creates a JSON Reader, opens and imports file if files exists.
	 * else creates a new file. Opens parent::$path/$filename, $filename
	 * contain a folder and a file (parent::$path/myfolder/myfile).
	 * Then myfolder has to be created before!
	 * 
	 * @param $filename the filename, without .json 
	 * @param $lockex lock the file exclusive, if yo
	 * @param $otherpath use another path than parent::$path 
	 */
	public function __construct( $filename, $lockex = false, $otherpath = ''){
		//create filename
		$this->filepath = (empty($otherpath) ? parent::$path : $otherpath . '/' ). $filename . '.json';
		
		$isfile = is_file( $this->filepath );

		// file lock
		$this->filehandler = fopen( $this->filepath . '.lock', 'c+' );
		if( !flock( $this->filehandler, $lockex ? LOCK_EX : LOCK_SH ) ){
			// error
			throw new Exception('Unable to lock file!');
		}

		//open file
		//	file exists?
		if( $isfile ){
			//read file
			$this->data = file_get_contents( $this->filepath );
		}
		//	folder exists, but file not => new file
		elseif( is_dir( dirname( $this->filepath ) ) ){
			//empty array
			$this->data = '[]';
			// create file
			file_put_contents( $this->filepath, $this->data );
		}
		else{
			//error
			throw new Exception('Unable to find file or folder!');
		}
		
		// no data => empty array
		if( empty( $this->data ) ){
			$this->data = '[]';
		}
		
		//hash content of file on disk (in the end, only save if changed)
		$this->datahash = hash( 'sha512', $this->data );
		//JSON parse
		$this->data = json_decode( $this->data, true);
		//error, if no json etc.
		if( !is_array( $this->data ) ){
			throw new Exception('Zombiefile!');	
		}

		//Check if file is writeable?
		//	=> enable changes only if writeable
		if( is_file( $this->filepath ) && is_writeable( $this->filepath ) ){
			$this->writeable = true;
		}
	}

	/**
	 * Write file contents on exit, if file changed
	 */
	public function __destruct(){
		$this->write_content();
		if( is_resource( $this->filehandler ) ){
			//unlock and close
			flock($this->filehandler, LOCK_UN );
			fclose($this->filehandler);
		}
	}

	/**
	 * Write file contents, if file writeable and changed
	 * @return successful written?
	 */
	private function write_content() : bool {
		if( $this->writeable ){
			//create JSON
			$json = json_encode( $this->data, JSON_PRETTY_PRINT);
			//calculate new hash
			$nowhash = hash( 'sha512', $json );
			//file changed? and JSON valid?
			if( $nowhash != $this->datahash && $json !== false ){
				if( flock( $this->filehandler, LOCK_EX ) ){ //exclusive log
					//write
					$re = file_put_contents( $this->filepath, $json );
					//if written, set new hash
					if( $re !== false ){
						$this->datahash = $nowhash;
					}
					//return error/ ok
					return $re;
				}
				return false;
			}
			return true;
		}
		return false;
	}

	public function getArray() : array {
		return $this->data;
	}

	public function setArray( array $data ) : bool {
		if( $this->writeable ){
			if( is_array( $data ) ){
				$this->data = $data;
				return true;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}

	public function isValue( array $index, $value = null ) : bool {
		// copy
		$data = $this->data;
		// iterate to index
		foreach( $index as $i ){
			// found key
			if( isset( $data[$i] ) ){
				// go to this key (step by step)
				$data = $data[$i];
			}
			else{
				// index not found
				return false;
			}
		}
		// return if index found or value correct
		return ( is_null( $value ) ) ? true : ( $data == $value );
	}

	public function getValue( array $index, bool $exception = false ){
		//Daten kopieren
		$data = $this->data;
		//nach und nach zu gesuchtem Wert gehen
		foreach( $index as $i ){
			//Wert vorhanden?
			if( isset( $data[$i] ) ){
				//Immer einen Weiter
				$data = $data[$i];
			}
			else{
				//Exception?
				if( $exception ){
					throw new Exception( "Unknown Index" );
				}
				//Fehler
				return false;
			}
		}
		return $data;
	}

	//Einen bestimmten Wert suchen 
	//	$index => Stelle an der gesucht werden soll
	//	$value => Zu suchenden Wert (nicht typecht)
	//	$column => Soll das Array mit array_column behandelt werden (dann hier die Spalte nennen, sonst 'null', angepasste array_column Funktion, erhält die Keys!)
	//	Return => Ausgabe nach array_search(); (Erster Index mit gesuchtem Wert oder false)
	public function searchValue( $index, $value, $column = null ){
		//bis zur durchsuchenden Stelle ranzoomen
		$data = $this->getValue( $index );
		//noch ein Array?
		// ein einzenler Wert kann mit isValue(); geprueft werden!
		if( is_array( $data ) ){
			//eine Spalte gegeben
			if( $column !== null ){
				//Array der Spalte bilden
				//	array_column geht nicht, da es die Keys nicht sicher
				//	erhält
				//	für neues Array
				$newdata = array();
				//	füllen
				foreach( $data as $key => $ar ){
					//neues Array schreiben
					$newdata[$key] = $ar[$column]; 
				}
				//	neues Array übernehmen
				$data = $newdata;
			}
			//suchen
			return array_search( $value, $data );
		}
		else{
			return false;
		}
	}

	//Einen Wert in das Array schreiben (neu oder dazu)
	//	$index => array() der Indexe, wenn der letze Wert 'null' ist, wir $value angefügt
	//	$value => Neuer Wert (einfach 'null' um Wert zu löschen, Indexe bleiben erhalten)
	public function setValue( array $index, $value ) : bool {
		//schreibbar?
		if( $this->writeable ){
			$this->data = $this->setValueHelper( $index, $value, $this->data );
			return true;
		}
		else{
			return false;
		}
	}

	//Rekursiver Helper fuer setValue();
	//	=> siehe setValue();
	//	$data => Array, welches angepasst werden soll
	//	Return => Angepasstes Array (mit geändertem, neuen, gelöschten Wert)
	private function setValueHelper( $index, $value, $data ){
		//Abbruch wenn nur noch ein Index
		//	=> dann schreiben
		if( count( $index ) == 1){
			//neu hinzu?
			if( $index[0] === null ){
				//hinzu
				array_push( $data, $value );
			}
			else{
				//Wert löschen?
				if( $value === null ){
					//löschen
					unset( $data[$index[0]] );
				}
				else{
					//ueberschreiben
					$data[$index[0]] = $value;
				}
			}
			
		}
		//per Rekursion weiter
		else{
			//ersten Index raus
			$i0 = array_shift( $index );
			//machen (Rekursion)
			$data[$i0] = $this->setValueHelper( $index, $value, $data[$i0] );
		}
		//ganzes Array zurueck
		return $data;
	}
}

?>