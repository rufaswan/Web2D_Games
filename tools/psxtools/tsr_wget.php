<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */

function numname( $old, $ext )
{
	$fn = sprintf('%s.%s', $old, $ext);
	if ( ! is_file($fn) )
		return $old;

	// append number for handle duplicates
	$i = 1;
	while ( $i > 0 )
	{
		$new = sprintf('%s.%d', $old, $i);
		if ( strlen($new) >= 0x80 )
			return '';

		$fn = sprintf('%s.%s', $new, $ext);
		if ( ! is_file($fn) )
			return $new;
		$i++;
	} // while ( $i > 0 )
	return '';
}

function valid_name( $old, $ext )
{
	// filename limit 255 chars
	if ( strlen($old) < 0x80 )
		return numname($old, $ext);

	$new = array(
		substr($old, 0, 0x40),
		sprintf('%08x', crc32($old)),
		md5($old),
		sha1($old),
	);
	foreach ( $new as $k => $v )
	{
		$new = numname($v, $ext);
		if ( ! empty($new) )
			return $new;
	}
	return $old;
}

function wget( $fullurl )
{
	$http = array(
		'http'   => '',
		'domain' => '',
		'path'   => '',
		'fname'  => '',
		'fext'   => '',
		'query'  => '',
	);
	$url = $fullurl;

	// URL testing
	$p = strpos($url, '://');
	if ( $p === false )
		return printf("%s is not an URL\n", $url);
	$http['http'] = substr($url, 0, $p);
	$url = substr($url, $p + 3);


	$p = strpos($url, '?');
	if ( $p !== false )
	{
		$http['query'] = substr($url, $p + 1);
		$url = substr($url, 0, $p);
	}


	$p = strrpos($url, '/');
	if ( $p === false )
		return printf("%s is base url\n", $url);
	$fn  = substr($url, $p + 1);
	$url = substr($url, 0, $p);
	if ( empty($fn) )
		return printf("%s has no file\n", $url);

	$p = strrpos($fn, '.');
	$http['fname'] = urldecode ( substr($fn,0,$p) );
	$http['fext' ] = strtolower( substr($fn, $p + 1) );

	$http['fname'] = valid_name($http['fname'], $http['fext']);
	printf("> wget %s.%s\n", $http['fname'], $http['fext']);

	$wget  = 'wget';
	$wget .= ' --quiet';
	$wget .= ' --no-config';
	$wget .= ' --no-check-certificate';
	$wget .= ' --user-agent="Mozilla/5.0 (Linux; Android 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.66 Mobile Safari/537.36"';
	$cmd = sprintf("%s '%s' -O '%s.%s'", $wget, $fullurl, $http['fname'], $http['fext']);
	exec($cmd);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	wget($argv[$i]);
