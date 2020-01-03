<?php
/** 
 * KIMB-Forms-Project
 * https://github.com/KIMB-technologies/KIMB-Forms-Project
 * 
 * (c) 2018 - 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'KIMB-FORMS-PROJECT' ) or die('Invalid Endpoint!');

/**
 * Reader class to define the api of a reader and/ or writer.
 * 
 * A index is a array of json keys:
 * {
 * 	"a" : {
 * 		"b" : 12
 * 	}
 * }
 * 		 ["a", "b"] => 12
 */
abstract class Reader {

	/**
	 * Path where the JSON files are located, in redis used as key prefix
	 */
	protected static $path = __DIR__.'/';
	
	/**
	 * Changes the data path, the path on filesystem
	 * @param $path the new path (optional)
	 * @return the current path
	 */
	public static function changePath( string $path = '' ) : string {
		//got new path
		if( !empty( $path ) ){
			// if no slash at the end, add one
			if( substr( $path , -1 ) != '/' ){
				$path = $path.'/';
			}
			// change path
			self::$path = $path;
		}
		// return the current path
		return self::$path;
	}

	/**
	 * Deletes a storage storage file
	 * @param $name file to delete
	 * @return deleted?
	 */
	abstract public static function deleteFile( string $name ) : bool;

	/**
	 * Returns the array of this dataset
	 * @return the array
	 */
	abstract public function getArray() : array;

	/**
	 * Sets the array of this dataset
	 * @param $array the array to set
	 * @return successful changed?
	 */
	abstract public function setArray( array $data ) : bool;

	/**
	 * Print the array
	 */
	public function output() : void {
		print_r( $this->getArray() );
	}

	/**
	 * Checks if a key has a value.
	 * @param $index array of indexes, the index
	 * @param $value the value need at index
	 * @return $value and value at $index matches, or $index exists
	 */
	abstract public function isValue( array $index, $value = null ) : bool;

	/**
	 * Fetch a value by the index
	 * @param $index the array of indexes, the index
	 * @param $exception throw an exception if index not found, or return false (optional)
	 * @return the value or false
	 */
	abstract public function getValue( array $index, bool $exception = false );

	/**
	 * Search a value.
	 * @param $index $index the array of indexes, the index 
	 * @param $value the value to search
	 * @param $column search a colum, name here or null (optional)
	 * @return like array_search(), the first found index or false
	 */
	abstract public function searchValue( $index, $value, $column = null );

	/**
	 * Set a value.
	 * @param $index array of indexes, the index
	 * @param $value the value to set
	 */
	abstract public function setValue( array $index, $value ) : bool;
}

?>