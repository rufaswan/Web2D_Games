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

	$dir = rtrim($dir, '/\\');

	$list = array();
	lsfile_r($dir, $list);

	$files = array();
	foreach ( $list as $fname )
	{
		$img = load_clutfile($fname);
		if ( empty($img) )
			continue;

		printf("add image %x x %x = %s\n", $img['w'], $img['h'], $fname);
		$img['id'] = $fname;
		$files[] = $img;
	} // foreach ( $list as $fname )

	list($ind, $cw, $ch) = atlasmap($files);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $cw;
	$pix['rgba']['h'] = $ch;
	$pix['rgba']['pix'] = canvpix($cw,$ch);

	foreach ( $files as $img )
	{
		$pix['src'] = $img;
		$pix['dx'] = $img['x'];
		$pix['dy'] = $img['y'];

		if ( isset($img['cc']) )
			copypix_fast($pix, 1);
		else
			copypix_fast($pix, 4);
	} // foreach ( $files as $img )

	savepix("$dir.atlas", $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tsr( $argv[$i] );
