<?php
/*
[license]
[/license]
 */
php_req_extension("json_encode", "json");

function load_idtagfile( $pfx )
{
	$fn = __DIR__ . "/idtag/$pfx.txt";
	if ( ! file_exists($fn) )
		return array();

	$data = array('TAG' => array());
	$head = '';
	foreach ( file($fn) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$line = htmlspecialchars($line, ENT_QUOTES|ENT_HTML5, 'UTF-8');
		if ( $line[0] == '[' )
			$head = trim($line, '[]');
		else
		{
			if ( ! isset( $data['TAG'][$head] ) )
				$data['TAG'][$head] = array();
			$data['TAG'][$head][] = $line;
		}
	} // foreach ( file($fn) as $line )

	foreach ( $data['TAG'] as $head => $line )
	{
		if ( count($line) == 1 )
			$data['TAG'][$head] = $line[0];
		else
			$data['TAG'][$head] = implode('<br>', $line);
	}
	return $data;
}

function save_quadfile( $pfx, &$json, $pretty=false )
{
	if ( empty($json) )
		return;

	// JSON_PRETTY_PRINT
	// JSON_FORCE_OBJECT
	if ( $pretty )
		$json = json_encode($json, JSON_PRETTY_PRINT);
	else
		$json = json_encode($json);

	file_put_contents("$pfx.quad", $json);
	return;
}
