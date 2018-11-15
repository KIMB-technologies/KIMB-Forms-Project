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
 * Diese Klasse stellt den Zugriff auf JSON-Dateien des Systems bereit
 */
class JSONReader{

	//Pfad unter dem alle Dateien liegen
	//	können auch Unterordner sein!
	private static $path = __DIR__.'/';

	//Den Pfad fuer die json Dateien aendern
	//	$path => neuer Pfad (vollständig ab /)
	//	Rückgabe => neuer Pfad, bzw. bei keinem Übergabeparameter aktueller Pfad
	public static function changepath( $path = '' ){
		//neuen Pfad uebergeben?
		if( !empty( $path ) ){
			//hat neuer Pfad einen abschließend Slash?
			if( substr( $path , -1 ) != '/' ){
				//nein, anfügen
				$path = $path.'/';
			}
			//neuen Pfad setzen
			self::$path = $path;
		}
		//Pfad ausgeben (neu oder unverändert)
		return self::$path;
	}

	//Daten über die geöffnete JSON
	private
		$filehandler, // fopen handler
		$data, //JSON Daten Array
		$filepath, //Vollständiger Dateipfad
		$datahash, //Hash des JSON-Strings auf dem Dateissystem
		$writeable = false; //Schreibbar?

	//Konstruktor
	//	Liest eine JSON Datei ein bzw. erstelle eine neue.
	//	Order muss existieren
	//	$filename => Dateiname (ohne .json, relativ zu self::$path)
	//	$lockex => directly lock the file exclusive
	public function __construct( $filename, $lockex = false){
		//Dateinamen erstellen
		$this->filepath = self::$path . $filename . '.json';
		
		$isfile = is_file( $this->filepath );

		// file lock
		$this->filehandler = fopen( $this->filepath, 'c+' );
		if( !flock( $this->filehandler, $lockex ? LOCK_EX : LOCK_SH ) ){
			//Fehler
			throw new Exception('Unable to lock file!');
		}

		//Datei öffnen
		//	vorhnaden?
		if( $isfile ){
			//auslesen
			$this->data = file_get_contents( $this->filepath );
		}
		//	Order vorhanden => neue Datei
		elseif( is_dir( dirname( $this->filepath ) ) ){
			//leeres Array
			$this->data = '[]';
		}
		else{
			//Fehler
			throw new Exception('Unable to find file or folder!');
		}
		
		if( empty( $this->data ) ){
			$this->data = '[]';
		}

		
		//Hash für später
		$this->datahash = hash( 'sha512', $this->data );
		//JSON Parsen
		$this->data = json_decode( $this->data, true);
		//Fehler?
		if( !is_array( $this->data ) ){
			throw new Exception('Zombiefile!');	
		}

		//Schreibbar?
		//	=> nur dann schreiben per Methode erlauben
		if( is_file( $this->filepath ) && is_writeable( $this->filepath ) ){
			$this->writeable = true;
		}
	}

	//schreibe Datei bei Objektzerstörung
	public function __destruct(){
		$this->write_content();
		//unlock and close
		flock($this->filehandler, LOCK_UN );
		fclose($this->filehandler);
	}

	//Datei schreiben
	private function write_content(){
		//schreibbar?
		if( $this->writeable ){
			//zu JSON
			$json = json_encode( $this->data, JSON_PRETTY_PRINT);
			//neuen Hash erzuegen
			$nowhash = hash( 'sha512', $json );
			//ueberhaupt geändert?
			//JSON okay?
			if( $nowhash != $this->datahash && $json !== false ){
				if( flock( $this->filehandler, LOCK_EX ) ){ //exclusive log
					//schreiben
					$re = file_put_contents( $this->filepath, $json );
					//Hash anpassen?
					if( $re ){
						$this->datahash = $nowhash;
					}
					//Rückgabe
					return $re;
				}
			}
		}
		return false;
	}

	//Array der Datei holen
	//	Return => Array der Datei
	public function getArray(){
		return $this->data;
	}

	//Array der Datei ersetzen
	//	alles wird überschrieben
	//	(Array muss sich zu JSON konvertieren lassen!!)
	//	$data => Neues Array
	//	Return => true/false 
	public function setArray( $data ){
		//schreibbar?
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

	//Ausgabe des Arrays
	public function print(){
		//raus
		print_r( $this->data );
	}

	//Prüfen, ob ein Wert vorhanden
	//	$index => array() der Indexe
	//	$value => gewuenschter Wert
	//	Return => true/false, gefunden und gleich $value (nicht typecht)
	public function isValue( $index, $value = null ){
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
				return false;
			}
		}
		//Rückgabe
		return ( is_null( $value ) ) ? true : ( $data == $value );
	}

	
	//Einen Wert aus Array holen
	//	$index => array() der Indexe
	//	$exception => true/ false Fehlermeldung werfen, wenn Index nicht gefunden
	//	Return => Wert unter diesem Index, bzw. false
	public function getValue( $index, $exception = false ){
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
	public function setValue( $index, $value ){
		//schreibbar?
		if( $this->writeable ){
			$this->data = $this->setValueHelper( $index, $value, $this->data );
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
