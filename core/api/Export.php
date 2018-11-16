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

class Export{

	/**
	 * List of export types
	 */
	private static $types = array(
		'csv',
		'print'
	);

	private $type,
		$pollid,
		$pollsub,
		$polldata;

	/**
	 * Does the data export, the get[type] will choose the mode
	 * 	admin code has to be set
	 */
	public function __construct(){
		$this->type = $_GET['type'];
		if( !empty( $this->type ) && is_string( $this->type ) && in_array( $this->type, self::$types) ){
			
			$this->auth(); //dies, if not ok

			switch( $this->type ){
				case 'csv':
					$this->csv();
					break;
				case 'print':
					$this->printview();
					break;
			}
		}
		else{
			header('Content-Type: text/plain; charset=utf-8');
			http_response_code(404);
			die( 'Unknown export option.' );
		}
	}

	/**
	 * Checks the admin code, and dies if not valid
	 * 	if ok, opens polldata and pollsubmissions
	 */
	private function auth(){
		$polladmins = new JSONReader( 'admincodes' );
		$admincode = $_GET['admin'];

		if( Utilities::checkFileName($admincode) && $polladmins->isValue( [ $admincode ] ) ){
			$this->pollid = $polladmins->getValue( [ $admincode ] );
			$this->polldata = new JSONReader( 'poll_' . $this->pollid );
			$this->pollsub = new JSONReader( 'pollsub_' . $this->pollid );

			//ok
		}
		else{
			header('Content-Type: text/plain; charset=utf-8');
			http_response_code(403);
			die( 'Unknown admin code.' );
		}

	}

	private function csv(){
		header('Content-Type: text/csv; charset=utf-8');
		//header('Content-Type: text/plain; charset=utf-8');
		header('Content-Disposition: inline; filename="export_'. $this->polldata->getValue(['pollname']) .'_'. time() .'.csv"');

		$data = array();
		$data[] = array( $this->polldata->getValue(['pollname']) );
		
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			$data[] = array();
			$data[] = array( $values['bez'] );

			$i = 1;
			foreach( $this->pollsub->getValue( [$id] ) as $sub){
				$data[] = array( '', $i++, $sub['name'], $sub['mail'], date( 'H:i:s d.m.Y', $sub['time'] ) );
			}
			if( $values['anz'] !== false ){
				$data[] = array('', ($i-1) . '/' . $values['anz']);
			}
		}

		$browser = fopen('php://output', 'w');
		foreach($data as $line ){
			fputcsv($browser, $line);
		}
		fclose($browser);
		die();
	}

	/**
	 * Generates and outputs a print view
	 * 	simple html
	 */
	private function printview(){
		$h = '<html><head><title>'. $this->polldata->getValue(['pollname']) .'</title><meta charset="utf-8">';
		$h .= '<style>@page{size: auto; margin: 0; } body{ -webkit-print-color-adjust: exact; margin: 30px; font-family: sans-serif; } table, tr, td, th{ border: 1px solid black; border-collapse: collapse;} div { background-color: #ccc; margin: 5px 0; padding: 5px; }</style>';
		$h .= '</head><body onload="window.print()">';
		$h.= '<h1>Export <span style="font-size:12px;">'. date( 'H:i:s d.m.Y' ) .'</small></h1>';
		$h.= '<h2>' . $this->polldata->getValue(['pollname']) . '</h2>';
		$h .= '<div>' . Utilities::optimizeOutputString($this->polldata->getValue( ['description'] )) . '</div>';

		$termine = array();
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			$h .= '<hr /><h3>'. $values['bez'] .'</h3>';
			$h .= empty( $values['des'] ) ? '' : '<div>'. $values['des'] .'</div>';
			$h .= '<table style="width: 100%;"><tr><th>ID</th><th>Name</th><th>E-Mail</th><th style="width:30%;">Time</th></tr>';

			$i = 1;
			foreach( $this->pollsub->getValue( [$id] ) as $sub){
				$h .= '<tr><td>'. $i++ .'</td><td>'. $sub['name'] .'</td><td>'. $sub['mail'] .'</td><td>'. date( 'H:i:s d.m.Y', $sub['time'] ) .'</td></tr>';
			}
			$h .= '</table>';
			$h .= $values['anz'] === false ? '' : '<p>'. ($i-1) . '/' . $values['anz'] .'</p>';
		}
	
		$h .= '</body></html>';

		header('Content-Type: text/html; charset=utf-8');
		die( $h );
	}

}

?>