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

function tagattr( $tag )
{
	$m = array();
	if ( preg_match('|@@\<(.+)\>@@|', $tag, $m) )
		return array('<>', $m[1]);

	if ( preg_match('|@@\[(.+)\]@@|', $tag, $m) )
		return array('[]', $m[1]);

	if ( preg_match('|@@(.+)@@|', $tag, $m) )
		return array('', $m[1]);

	return 0;
}

function inc64file( $t, &$file )
{
	// file    = data
	// <file>  = <html>data</html>
	// [file]  = var t = Uint8Array([data]);
	$base = basename($t[1]);
	$name = preg_replace('|[^0-9a-zA-z]+|', '_', $base);
	switch ( $t[0] )
	{
		case '[]':
			// any = if ArrayBufferView
			$len = strlen($file);
			$b   = array();
			for ( $i=0; $i < $len; $i++ )
				$b[$i] = ord( $file[$i] );
			$file = sprintf('var %s = Uint8Array([%s]);', $name, implode(',',$b));
			return;
		case '<>':
			$ext = substr($base, strrpos($base,'.')+1);
				$ext = strtolower($ext);

			if ( $ext === 'jpg' )  $ext = 'jpeg';
			if ( $ext === 'wav' )  $ext = 'wave';
			switch ( $ext )
			{
				case 'png':
				case 'jpeg':
				case 'bmp':
				case 'gif':
				case 'webp':
					$data = sprintf('data:image/%s;base64,%s', $ext, base64_encode($file));
					$file = sprintf('<img id="%s" alt="%s" title="%s" src="%s" />', $name, $name, $name, $data);
					return;
				case 'mp4':
				case 'webm':
					$data = sprintf('data:video/%s;base64,%s', $ext, base64_encode($file));
					$file = sprintf('<video id="%s" src="%s" controls loop></video>', $name, $data);
					return;
				case 'ogg':
				case 'mp3':
				case 'wave':
					$data = sprintf('data:audio/%s;base64,%s', $ext, base64_encode($file));
					$file = sprintf('<audio id="%s" src="%s" controls loop></audio>', $name, $data);
					return;
				case 'js':
					$file = '<script>' . $file . '</script>';
					return;
				case 'css':
					$file = '<style>' . $file . '</style>';
					return;
			} // switch ( $ext )
			return;
	} // switch ( $t[0] )
	return;
}

function tplfile( $fname )
{
	printf("%s( %s )\n", __FUNCTION__, $fname);
	$dir  = dirname($fname);
	$file = file_get_contents($fname);

	// BINARY file = no recursice parsing
	if ( strpos($file,"\x00") !== false )
		return $file;

	$func = __FUNCTION__;
	while (1)
	{
		$stpos = strpos($file, '@@');
		if ( $stpos === false )
			break;
		$edpos = strpos($file, '@@', $stpos + 2);
		if ( $edpos === false )
			break;

		$len = $edpos + 2 - $stpos;
		$tag = substr($file, $stpos, $len);

		$t = tagattr($tag);
		if ( ! $t )
			break;

		$srcn = sprintf('%s/%s', $dir, $t[1]);
		$srcp = $func($srcn);
		inc64file($t, $srcp);

		$file = str_replace($tag, $srcp, $file);
	} // while (1)
	return $file;
}

function tpl2html( $fname )
{
	printf("== %s( %s )\n", __FUNCTION__, $fname);
	$oglen = filesize($fname);
	if ( $oglen < 1 )
		return;

	$real = realpath($fname);
	$file = tplfile($real);
	if ( empty($file) )
		return;

	if ( $oglen < strlen($file) )
		file_put_contents("$fname.html", $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tpl2html( $argv[$i] );

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
	@@<image.png>@@  -> <img id='' src='data:image/png;base64,...'  alt='' title='' />
	@@<image.jpg>@@  -> <img id='' src='data:image/jpeg;base64,...' alt='' title='' />
 */
