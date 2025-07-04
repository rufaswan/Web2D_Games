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
require 'class-html.inc';

function strip_dom( &$dom, $tag )
{
	$c = count($dom);
	while ( $c > 0 )
	{
		$c--;
		if ( stripos($dom[$c],$tag) === 0 )
			array_splice($dom, $c, 1);
	}
	return;
}

function strip_dom_nest( &$dom, $tag )
{
	$is_nest = true;
	$stpos = -1;
	while ( $is_nest )
	{
		$is_nest = false;
		foreach ( $dom as $dk => $dv )
		{
			if ( stripos($dv,'<'.$tag) === 0 )
			{
				$stpos = $dk;
				continue;
			}
			if ( stripos($dv,'</'.$tag) === 0 )
			{
				if ( $stpos < 0 )
					return trigger_error("no tag = <$tag", E_USER_NOTICE);
				array_splice($dom, $stpos, $dk + 1 - $stpos);

				$stpos   = -1;
				$is_nest = true;
				goto done;
			}
		} // foreach ( $dom as $dk => $dv )
done:
	} // while ( $is_nest )

	if ( $stpos >= 0 )
		trigger_error("no tag = </$tag", E_USER_NOTICE);
	return;
}

function strip_attrib( &$tag )
{
	if ( empty($tag) )
		return;
	if ( $tag[0] !== '<' )
		return;
	if ( stripos($tag,'<!doctype') === 0 )
		return;
	if ( strpos($tag,'</',3) !== false )
		return;

	// ASCII whitespace = TAB LF FF CR space
	$tag = preg_replace("|[\x09\x0a\x0c\x0d\x20]+|", ' ', $tag);
	$p = strpos($tag, ' ');
	if ( $p === false )
		return;
	$new = substr($tag, 0, $p);

	$pat = array(
		'|([a-zA-Z0-9\-]+)="([^"]+)"|' ,
		"|([a-zA-Z0-9\-]+)='([^']+)'|" ,
		'|([a-zA-Z0-9\-]+)=([^ />]+)|' ,
	);
	foreach ( $pat as $pv )
	{
		$m = array();
		if ( ! preg_match_all($pv,$tag,$m) )
			continue;

		//print_r($m);
		foreach ( $m[1] as $mk => $mv )
		{
			$mv = strtolower($mv);
			switch ( $mv )
			{
				case 'name':     case 'content':  case 'charset':
				case 'href':
				case 'src':      case 'srcset':
				case 'colspan':  case 'rowspan':
					$new .= sprintf(' %s="%s"', $mv, $m[2][$mk]);
					break;
			}
			$tag = str_replace($m[0][$mk], '', $tag);
		}
	} // foreach ( $pat as $pv )

	$tag = $new . '>';
	return;
}

function htmlclean( $fname )
{
	$file = @file_get_contents($fname);
	if ( empty($file) )  return;

	$dom = html::text2dom($file);
	//print_r($dom);
	if ( ! $dom )
		return;

	$ogcnt = count($dom);
	$tag = array(
		'<![CDATA[' , '<!--'      ,

		'<script'   , '<style'    ,
		'<link'     ,
		'<form'     , '<input'    , '<button' ,
		'<iframe'   , '<template' ,
		'<svg'      ,

		'<div'      , '</div'     ,
		'<span'     , '</span'    ,
		'<font'     , '</font'    ,
	);
	foreach ( $tag as $t )
		strip_dom($dom, $t);
	//print_r($dom);

	$tag = array(
		'header' , 'footer' , // Content model: Flow content, but with no header or footer element descendants.
		'aside'  , 'nav'    , // Sectioning content
	);
	foreach ( $tag as $t )
		strip_dom_nest($dom, $t);

	$cnt  = count($dom);
	for ( $i=0; $i < $cnt; $i++ )
		strip_attrib($dom[$i]);

	//print_r($dom);
	printf('[%d -> %d] %s'."\n", $ogcnt, $cnt, $fname);

	$file = implode("\n", $dom);
	$file = preg_replace("|[\r\n]+|", "\n", trim($file));
	//echo $file;
	file_put_contents("$fname.t", $file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	htmlclean( $argv[$i] );
