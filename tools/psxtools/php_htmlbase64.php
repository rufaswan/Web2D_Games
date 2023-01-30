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

function is_utf8( &$file )
{
	$invalid = "\x00\x7f";

	$len = strlen($invalid);
	for ( $i=0; $i < $len; $i++ )
	{
		if ( strpos($file, $invalid[$i]) !== false )
			return false;
	}
	return true;
}

function get_tag( $fname )
{
	$b = trim($fname, '<>');
	if ( $fname !== $b )
		return array('<>', $b);

	$b = trim($fname, '[]');
	if ( $fname !== $b )
		return array('[]', $b);

	return array('', $fname);
}

function get_meta( $fname )
{
	// file    = data
	// <file>  = <html>data</html>
	// [file]  = var t = Uint8Array([data]);
	$meta = array();
	$t = get_tag($fname);

	$meta['t'] = $t[0];
	$meta['f'] = file_get_contents($t[1]);
	$meta['s'] = preg_replace('|[^a-zA-Z0-9]|', '_', $t[1]);

	$p = strrpos($t[1], '.');
	$e = substr ($t[1], $p+1);
	$meta['e'] = strtolower($e);
	return $meta;
}
//////////////////////////////
function strip_js_css( &$file )
{
	while (1)
	{
		$p1 = strpos($file, '/*');
		if ( $p1 === false )
			break;
		$p2 = strpos($file, '*/', $p1+2);
		if ( $p2 === false )
			break;
		$p2 += 2;

		$s = substr($file, $p1, $p2-$p1);
		$file = str_replace($s, "\n", $file);
	}
	return;
}

function strip_html( &$file )
{
	// exclude <!doctype html>
	// exclude <![CDDATA]>
	while (1)
	{
		$p1 = strpos($file, '<!--');
		if ( $p1 === false )
			break;
		$p2 = strpos($file, '-->', $p1+4);
		if ( $p2 === false )
			break;
		$p2 += 3;

		$s = substr($file, $p1, $p2-$p1);
		$file = str_replace($s, "\n", $file);
	}
	return;
}
//////////////////////////////
function html64( $fname )
{
	printf("== html64( %s )\n", $fname);
	$meta = get_meta($fname);

	// any = if ArrayBufferView
	if ( $meta['t'] === '[]' )
	{
		$b = array();
		$len = strlen($meta['f']);
		for ( $i=0; $i < $len; $i++ )
			$b[$i] = ord( $meta['f'][$i] );
		return sprintf('var %s = Uint8Array([%s]);', $meta['s'], implode(',',$b));
	}

	// BINARY file = no recursice parsing
	$is_txt = is_utf8($meta['f']);
	$is_tag = ( $meta['t'] === '<>' );
	if ( ! $is_txt )
	{
		$mime = 'application/octet-stream';
		$elem = '';
		switch ( $meta['e'] )
		{
			case 'bmp' :  $mime = 'image/bmp' ;  $elem = 'img'  ;  break;
			case 'jpeg':
			case 'jpg' :  $mime = 'image/jpeg';  $elem = 'img'  ;  break;
			case 'gif' :  $mime = 'image/gif' ;  $elem = 'img'  ;  break;
			case 'png' :  $mime = 'image/png' ;  $elem = 'img'  ;  break;

			case 'mp4' :  $mime = 'video/mp4' ;  $elem = 'video';  break;
			case 'm4v' :  $mime = 'video/mp4' ;  $elem = 'video';  break;
			case 'webm':  $mime = 'video/webm';  $elem = 'video';  break;
			case 'mov' :  $mime = 'video/quicktime';  $elem = 'video';  break;

			case 'wav' :  $mime = 'audio/wav' ;  $elem = 'audio';  break;
			case 'mp3' :  $mime = 'audio/mpeg';  $elem = 'audio';  break;
			case 'ogg' :  $mime = 'audio/ogg' ;  $elem = 'audio';  break;
			case 'm4a' :  $mime = 'audio/mp4' ;  $elem = 'audio';  break;
			case 'aac' :  $mime = 'audio/aac' ;  $elem = 'audio';  break;
			case 'flac':  $mime = 'audio/flac';  $elem = 'audio';  break;
		} // switch ( $meta['e'] )

		$data = sprintf('data:%s;base64,%s', $mime, base64_encode($meta['f']));
		if ( $elem === '' || ! $is_tag )
			return $data;

		switch ( $elem )
		{
			case 'img':
				return sprintf('<img id="%s" alt="%s" title="%s" src="%s">', $meta['s'], $meta['s'], $meta['s'], $data);
			case 'video':
				return sprintf('<video id="%s" src="%s" controls loop></video>', $meta['s'], $data);
			case 'audio':
				return sprintf('<audio id="%s" src="%s" controls loop></audio>', $meta['s'], $data);
		} // switch ( $elem )
	}

	// TEXT files = recursive parsing
	// remove comments
	strip_js_css($meta['f']); // C-style /* ... */
	strip_html  ($meta['f']); // HTML <!-- ... -->

	$func = __FUNCTION__;
	while (1)
	{
		$p1 = strpos($meta['f'], '@@');
		if ( $p1 === false )
			break;
		$p2 = strpos($meta['f'], '@@', $p1+2);
		if ( $p2 === false )
			break;
		$p2 += 2;

		$s    = substr($meta['f'], $p1, $p2-$p1);
		$file = $func( trim($s,'@') );
		$meta['f'] = str_replace($s, $file, $meta['f']);
	}

	switch ( $meta['e'] )
	{
		case 'js':
			if ( $is_tag )
				return sprintf('<script>%s</script>', $meta['f']);
			else
				return $meta['f'];
		case 'css':
			if ( $is_tag )
				return sprintf('<style>%s</style>', $meta['f']);
			else
				return $meta['f'];
		default:
			return $meta['f'];
	} // switch ( $meta['e'] )
	return '';
}

function html64file( $fname )
{
	$sha = file_get_contents($fname);
	if ( empty($sha) )  return;
	$sha = sha1($sha);

	printf("%s  %s\n", $sha, $fname);
	$file = html64($fname);
	if ( sha1($file) === $sha )
		return;
	file_put_contents("$fname.html", $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	html64file( $argv[$i] );

/*
func1.js
func2.js

script.js
	@@func1.js@@  ->  ...
	@@func2.js@@  ->  ...
	@@file.zip@@  -> var file_zip = Uint8Array([...]);

style.css
	background : url('@@bg.png@@');  -> data:image/png;base64,...
	background : url('@@bg.jpg@@');  -> data:image/jpeg;base64,...

index.html
	@@<script.js>@@  -> <script id=''>...</script>
	@@<style.css>@@  -> <style  id=''>...</style>
	@@<image.png>@@  -> <img id='' src='data:image/png;base64,...'  alt='' title=''>
	@@<image.jpg>@@  -> <img id='' src='data:image/jpeg;base64,...' alt='' title=''>
 */
