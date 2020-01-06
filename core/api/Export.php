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

class Export{

	/**
	 * List of export types
	 */
	private static $types = array(
		'csv',
		'print'
	);

	private $type,
		$pollsub,
		$polldata;

	/**
	 * Does the data export, the get[type] will choose the mode
	 * 	admin code has to be set
	 */
	public function __construct(){
		$this->type = $_GET['type'];
		if( !empty( $this->type ) && is_string( $this->type ) && in_array( $this->type, self::$types) ){
			
			$this->polldata = PollAdmin::authByAdmincode(); //dies, if not ok
			$this->pollsub = new JSONReader( 'pollsub_' . $this->polldata->getValue( ['code', 'poll'] ) );

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

	private function csv(){
		header('Content-Type: text/csv; charset=utf-8');
		//header('Content-Type: text/plain; charset=utf-8');
		header('Content-Disposition: inline; filename="export_'. $this->polldata->getValue(['pollname']) .'_'. time() .'.csv"');

		$data = array();
		$data[] = array( $this->polldata->getValue(['pollname']) );

		$addsHeader = array();
		if( $this->polldata->isValue(['additionals']) ){
			foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
				$addsHeader[] = $add['text'];
			}
		}

		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			$data[] = array();
			$data[] = array_merge(
				array( $values['bez'] , '', '', '', '' ),
				$addsHeader
			);

			$i = 1;
			if( $this->pollsub->isValue( [$id] ) ){
				foreach( $this->pollsub->getValue( [$id] ) as $sub){
					$addsCells = array();
					if( $this->polldata->isValue(['additionals']) ){
						foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
							if( isset($sub['additionals'][$key]) ){
								if( $add['type'] === 'checkbox' ){
									$addsCells[] = ($sub['additionals'][$key] ? 'yes' : 'no');
								}
								else {
									$addsCells[] = $sub['additionals'][$key];
								}
							}
							else {
								$addsCells[] = '';
							}
						}
					}

					$data[] = array_merge(
						array( '', $i++, $sub['name'], $sub['mail'], date( 'H:i:s d.m.Y', $sub['time'] ) ),
						$addsCells
					);
				}
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
		$mdp = new Parsedown(); // Markdown Parser
		$mdp->setSafeMode(true);
		
		$h = '<html><head><title>'. $this->polldata->getValue(['pollname']) .'</title><meta charset="utf-8">';
		$h .= '<style>@page{size: auto; margin: 0; } body{ -webkit-print-color-adjust: exact; margin: 30px; font-family: sans-serif; } table, tr, td, th{ border: 1px solid black; border-collapse: collapse;} div { background-color: #ccc; margin: 5px 0; padding: 5px; }</style>';
		$h .= '</head><body onload="window.print()">';
		$h.= '<h1>Export <span style="font-size:12px;">'. date( 'H:i:s d.m.Y' ) .'</small></h1>';
		$h.= '<h2>' . Utilities::optimizeOutputString( $this->polldata->getValue(['pollname']) ) . '</h2>';
		$h .= '<div>' . $mdp->text($this->polldata->getValue( ['description'] )) . '</div>';

		$addsHeader = '';
		if( $this->polldata->isValue(['additionals']) ){
			foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
				$addsHeader .= '<th>'. Utilities::optimizeOutputString( $add['text'] ) .'</th>';
			}
		}

		$termine = array();
		foreach( $this->polldata->getValue( ['termine'] ) as $id => $values){
			$h .= '<hr /><h3>'. Utilities::optimizeOutputString( $values['bez'] ) .'</h3>';
			$h .= empty( $values['des'] ) ? '' : '<div>'. $mdp->text($values['des']) .'</div>';
			$h .= '<table style="width: 100%;"><tr><th>ID</th><th>Name</th><th>E-Mail</th><th>Time</th>'. $addsHeader .'</tr>';
			$i = 1;
			if( $this->pollsub->isValue( [$id] ) ){
				foreach( $this->pollsub->getValue( [$id] ) as $sub){
					$addsCells = '';
					if( $this->polldata->isValue(['additionals']) ){
						foreach( $this->polldata->getValue(['additionals']) as $key => $add ){
							if( isset($sub['additionals'][$key]) ){
								if( $add['type'] === 'checkbox' ){
									$addsCells .= '<td>'. ($sub['additionals'][$key] ? '&check;' : '&cross;' ) .'</td>';
								}
								else {
									$addsCells .= '<td>'. Utilities::optimizeOutputString( $sub['additionals'][$key] ) .'</td>';
								}
							}
							else {
								$addsCells .= '<td></td>';
							}
						}
					}
					$h .= '<tr><td>'. $i++ .'</td><td>'. Utilities::optimizeOutputString( $sub['name'] ) .'</td><td>'. Utilities::optimizeOutputString( $sub['mail'] ) .'</td><td>'. date( 'H:i:s d.m.Y', $sub['time'] ) .'</td>'. $addsCells .'</tr>';
				}
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