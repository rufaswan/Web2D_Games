<?php
/*
[license]
[/license]
 */

function moztxt( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	preg_match_all('|<([^>]+)>|', $file, $match);
	if ( empty($match) )
		return;

	//print_r($match[1]);
	$list = [];
	foreach ( $match[1] as $url )
	{
		$http  = parse_url($url);
		if ( ! isset($http['path']) )
			continue;
		if ( isset($http['query']) )
			continue;

		$fname = substr($http['path'], strrpos($http['path'],'/')+1);
		if ( empty($fname) )
			continue;
		if ( strpos($fname, '.') === false )
			continue;

		$path = sprintf('<p><a href="%s">%s</a></p>', $url, urldecode($fname));
		$list[] = $path;
	} // foreach ( $match[1] as $url )

	sort($list);
	foreach ( $list as $p )
		echo "$p\n";
	return;
}

for ( $i=1; $i < $argc; $i++ )
	moztxt( $argv[$i] );
