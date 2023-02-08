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

	$e = substr($t[1], strrpos($t[1],'.')+1);
	$meta['e'] = strtolower($e);
	return $meta;
}

function get_mime( $ext )
{
	$image = array('bmp','gif','png','apng','avif','jpeg','tiff','webp',);
	if ( array_search($ext, $image) !== false )
		return 'image/' . $ext;

	$video = array('mp4','webm',);
	if ( array_search($ext, $video) !== false )
		return 'video/' . $ext;

	$audio = array('aac','ogg','flac','midi','opus','wave',);
	if ( array_search($ext, $audio) !== false )
		return 'audio/' . $ext;

	return 'application/octet-stream';
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
	// exclude <![CDATA[character data]]>
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
		$mime = get_mime($meta['e']);
		$data = sprintf('data:%s;base64,%s', $mime, base64_encode($meta['f']));
		if ( ! $is_tag )
			return $data;

		switch ( substr($mime,0,5) )
		{
			case 'image':
				return sprintf('<img id="%s" alt="%s" title="%s" src="%s">', $meta['s'], $meta['s'], $meta['s'], $data);
			case 'video':
				return sprintf('<video id="%s" src="%s" controls loop></video>', $meta['s'], $data);
			case 'audio':
				return sprintf('<audio id="%s" src="%s" controls loop></audio>', $meta['s'], $data);
			default:
				printf("BINARY : no <tag> for %s\n", $mime);
				return $data;
		} // switch ( substr($mime,0,5) )
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
	if ( ! $is_tag )
		return $meta['f'];

	switch ( $meta['e'] )
	{
		case 'js':
			return sprintf('<script>%s</script>', $meta['f']);
		case 'css':
			return sprintf('<style>%s</style>', $meta['f']);
		default:
			printf("TEXT : no <tag> for %s\n", $meta['e']);
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
