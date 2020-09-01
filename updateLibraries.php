<?php
define('ZIP', 1111);
define('FILES', 1112);
define('GET', 1113);

$basepath = __DIR__ . '/load/';
$js = array(
	'../core/external/' => array(
		GET => array(
			// check version !!
			'random_compat.phar' => 'https://github.com/paragonie/random_compat/releases/download/v2.0.18/random_compat.phar',
			// check version !!
			'random_compat.phar.pubkey' => 'https://github.com/paragonie/random_compat/releases/download/v2.0.18/random_compat.phar.pubkey',
			'Parsedown.php' => 'https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php'
		)
	),
	'bootstrap' => array(
		GET => array(
			'bootstrap.min.js' => 'https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.min.js',
			'bootstrap-light.min.css' => 'https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap.min.css',
			'bootstrap-grid.min.css' => 'https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap-grid.min.css',
			'bootstrap-reboot.min.css' => 'https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap-reboot.min.css',
			'bootstrap.min.css.map' => 'https://cdn.jsdelivr.net/npm/bootstrap/dist/css/bootstrap.min.css.map',
			'bootstrap-dark.min.css' => 'https://raw.githubusercontent.com/thomaspark/bootswatch/v4/dist/slate/bootstrap.min.css'
		)
	),
	'' => array(
		GET => array(
			'jquery.min.js' => 'https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js',
			// check version !!
			'jquery-ui.min.js' => 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',
			// check version !!
			'jquery-ui.min.css' => 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css',
			'md_parser.js' => 'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
		)
	)
);

foreach($js as $folder => $data){
	doFolder($basepath . $folder . '/', $data);
}

function doFolder($path, $data){
	if( isset($data[ZIP]) && isset($data[FILES]) ){
		$zipname = $path . '___tmp___.zip';
		downloadFileTo($data[ZIP], $zipname);

		$zip = new ZipArchive();
		if( $zip->open($zipname, ZipArchive::RDONLY) === true ){
			$filelist = array();
			for( $i = 0; $i < $zip->numFiles; $i++) {
				$name = $zip->statIndex($i)['name'];

				foreach( $data[FILES] as $file => $glob ){
					if(fnmatch($glob, $name)){
						if( !copy("zip://".$zipname."#".$name, $path . $file) ){
							echo "=> Error extract file from ZIP '$name'" . PHP_EOL;
						}
					}
				}
			}
			$zip->close();
		}
		else{
			echo "=> Error opening ZIP '" . $data[ZIP] . "'" . PHP_EOL;
		}
		unlink($zipname);
	}
	if( isset($data[GET]) ){
		foreach($data[GET] as $file => $link ){
			downloadFileTo($link, $path . $file);
		}
	}

}

function downloadFileTo($link, $path){
	$cont = file_get_contents($link);
	if( !empty($cont)){
		if( file_put_contents($path, $cont) === false ){
			echo "=> Write file error '$path'" . PHP_EOL;
		}
	}
	else{
		echo "=> Download error '$link'" . PHP_EOL;
	}
}
?>