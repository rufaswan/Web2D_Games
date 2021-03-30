<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-atlas.inc";

function tsr( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$files = array();
	foreach ( scandir($dir) as $fname )
	{
		if ( $fname[0] === '.' )
			continue;

		$file = load_clutfile("$dir/$fname");
		if ( empty($file) )
			continue;

		//printf("add file %x x %x\n", $file['w'], $file['h']);
		$file['id'] = $fname;
		$files[] = $file;
	} // foreach ( scandir($dir) as $fname )

	list($ind, $cw, $ch) = atlasmap($files);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $cw;
	$pix['rgba']['h'] = $ch;
	$pix['rgba']['pix'] = canvpix($cw,$ch);

	foreach ( $files as $file )
	{
		$pix['src'] = $file;
		$pix['dx'] = $file['x'];
		$pix['dy'] = $file['y'];

		if ( isset($file['cc']) )
			copypix_fast($pix, 1);
		else
			copypix_fast($pix, 4);
	}

	savepix("$dir.atlas", $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tsr( $argv[$i] );
