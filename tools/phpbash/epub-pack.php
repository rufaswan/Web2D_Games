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
require 'class-sh.inc';
require 'class-zipstore.inc';

$gp_mime = array(
	'html' => 'application/xhtml+xml',
	'adoc' => 'text/asciidoc',
	'txt'  => 'text/plain',
	'jpg'  => 'image/jpeg',
	'png'  => 'image/png',
);
//////////////////////////////
function epub_file( &$list, &$meta, $base )
{
	$MAIN = array(
		'book' => '',
		'nav'  => '',
	);
	if ( count($meta['html']) > 1 )
	{
		// generate book.html when the dir has 2+ .HTML
		$MAIN['book'] = $meta['uuid'] . '-book.html';

		$li = '';
		foreach ( $meta['html'] as $v )
			$li .= sprintf('<p><a href="%s">%s</a></p>'."\n", $v, $v);

		$xml = <<< __XML
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$base} EPUB</title>
  <meta charset="utf-8"/>
</head>
<body>{$li}</body>
</html>
__XML;

		$t = array(
			'name' => $MAIN['book'],
			'data' => $xml,
			'ext'  => 'html',
		);
		$list[] = $t;
		array_unshift($meta['html'], $t['name']);
	}
	// one .HTML found
	else
		$MAIN['book'] = $meta['html'][0];

	printf("book = %s\n", $MAIN['book']);
	//////////////////////////////
	$MAIN['nav'] = $meta['uuid'] . '-nav.html';
	$li = '';
	foreach ( $meta['html'] as $v )
		$li .= sprintf('<li><a href="%s">%s</a></li>'."\n", $v, $v);

	$xml = <<<__XML
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
  <title>{$base} EPUB nav</title>
  <meta charset="utf-8"/>
</head>
<body>
  <nav epub:type="toc">
    <ol>{$li}</ol>
  </nav>
</body>
</html>
__XML;

	printf("nav  = %s\n", $MAIN['nav']);
	$t = array(
		'name' => $MAIN['nav'],
		'data' => $xml,
		'ext'  => 'html',
	);
	$list[] = $t;
	array_unshift($meta['html'], $t['name']);
	//////////////////////////////
	global $gp_mime;

	$mtime = date('c', $meta['mtime']);
	$li_ma = '';
	$li_sp = '';
	foreach ( $meta['file'] as $k => $v )
		$li_ma .= sprintf('<item href="%s" id="file%d" media-type="%s"/>'."\n", $v[0], $k, $v[1]);
	foreach ( $meta['html'] as $k => $v )
	{
		if ( $v === $MAIN['book'] )
		{
			$li_ma .= sprintf('<item href="%s" id="book" media-type="%s"/>'."\n", $v, $gp_mime['html']);
			$li_sp .= sprintf('<itemref idref="book"/>'."\n");
		}
		else
		if ( $v === $MAIN['nav'] )
		{
			$li_ma .= sprintf('<item href="%s" id="nav"  media-type="%s" properties="nav"/>'."\n", $v, $gp_mime['html']);
			//$li_sp .= sprintf('<itemref idref="nav"/>'."\n");
		}
		else
		{
			$li_ma .= sprintf('<item href="%s" id="html%d" media-type="%s"/>'."\n", $v, $k, $gp_mime['html']);
			$li_sp .= sprintf('<itemref idref="html%d"/>'."\n", $k);
		}
	}

	$xml = <<< __XML
<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" xmlns:opf="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookID">
  <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
    <dc:title>{$base} EPUB</dc:title>
    <dc:identifier id="BookID">{$meta['uuid']}</dc:identifier>
    <dc:language>en-US</dc:language>
    <meta property="dcterms:modified">{$mtime}</meta>
  </metadata>
  <manifest>{$li_ma}</manifest>
  <spine>{$li_sp}</spine>
</package>
__XML;

	$t = array(
		'name' => 'root.opf',
		'data' => $xml,
	);
	array_unshift($list, $t);
	//////////////////////////////
	$xml = <<< __XML
<?xml version="1.0"?>
<container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
  <rootfiles>
    <rootfile full-path="root.opf" media-type="application/oebps-package+xml"/>
  </rootfiles>
</container>
__XML;

	$t = array(
		'name' => 'META-INF/container.xml',
		'data' => $xml,
	);
	array_unshift($list, $t);
	//////////////////////////////
	$t = array(
		'name' => 'mimetype',
		'data' => 'application/epub+zip',
	);
	array_unshift($list, $t);
	return;
}

function load_file( &$list, $dir )
{
	global $gp_mime;
	$t = array(
		'html'  => array(),
		'file'  => array(),
		'uuid'  => '',
		'mtime' => 0,
	);
	$data = '';
	$cnt  = count($list);
	while ( $cnt > 0 )
	{
		$cnt--;

		$ext = $list[$cnt]['ext'];
		if ( ! isset($gp_mime[$ext]) )
		{
			array_splice($list, $cnt, 1);
			continue;
		}
		$full = $dir .'/'. $list[$cnt]['name'];

		$d = file_get_contents($full);
		$list[$cnt]['data'] = $d;
		$data .= $d;

		$d = filemtime($full);
		$list[$cnt]['mtime'] = $d;
		if ( $d > $t['mtime'] )
			$t['mtime'] = $d;

		// all files will be put under OEBPS folder
		// OEBPS = Open eBook Publication Structure
		$list[$cnt]['name'] = 'OEBPS/' . $list[$cnt]['name'];
		if ( $ext === 'html' )
			$t['html'][] = $list[$cnt]['name'];
		else
			$t['file'][] = array($list[$cnt]['name'] , $gp_mime[$ext]);
	} // while ( $cnt > 0 )

	$t['uuid'] = sha1($data);
	return $t;
}

function dir2epub( $dir )
{
	if ( ! is_dir($dir) )
		return;
	$base = sh::realbase($dir);
	if ( empty($base) )
		return;

	$zip  = new zipstore;
	$list = $zip->scan($dir);
	usort($list, array($zip,'sort_size_asc'));
	//print_r($list);

	$meta = load_file($list, $dir);
	//print_r($meta);

	if ( empty($meta['html']) )
		return;
	usort($meta['html'], 'version_compare');
	epub_file($list, $meta, $base);

	$zip->save($base.'.epub', $list, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dir2epub( $argv[$i] );
