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
if ( ! function_exists('json_encode') )
	exit("no json extension\n");

function jval_keylen( &$val )
{
	$len = 0;
	foreach ( $val as $k => $v )
	{
		if ( strlen($k) > $len )
			$len = strlen($k);
	}
	return $len;
}

function jval_is_list( &$val )
{
	$cnt = count($val);
	while ( $cnt > 0 )
	{
		$cnt--;
		if ( ! isset($val[$cnt]) )
			return false;
	}
	return true;
}

function jval_has_array( &$val )
{
	foreach ( $val as $v )
	{
		if ( is_array($v) )
			return true;
	}
	return false;
}

function jval_str( &$val )
{
	if ( "$val" === $val )
	{
		// decode failed
		//   escape the escape char
		$val = str_replace('\\', '\\\\', $val);
		return sprintf('"%s"', $val);
	}

	if ( (int)$val === $val )
		return sprintf('%d', $val);

	//return sprintf('%.e', $val);
	return sprintf('%.f', $val);
}
//////////////////////////////
function json_pretty( &$json, $tab )
{
	if ( ! is_array($json) )
		return jval_str($json);

	$is_list   = jval_is_list  ($json);
	$has_array = jval_has_array($json);
	if ( $is_list && ! $has_array )
	{
		foreach ( $json as $jk => $jv )
			$json[$jk] = jval_str($jv);
		return sprintf('[ %s ]', implode(' , ', $json));
	}

	$func = __FUNCTION__;
	$cnt  = count($json);
	$pad  = $tab . '  ';

	if ( $is_list )
	{
		$txt = '[';

		foreach ( $json as $jv )
		{
			$jv = $func($jv, $pad);
			$txt .= sprintf("\r\n%s%s", $pad, $jv);

			$cnt--;
			if ( $cnt > 0 )
				$txt .= ' ,';
		} // foreach ( $json as $jv )

		$txt .= sprintf("\r\n%s%s", $tab, ']');
		return $txt;
	}
	else
	{
		$txt = '{';

		$len = jval_keylen($json) + 2;
		foreach ( $json as $jk => $jv )
		{
			$jk = "\"$jk\"";
			$jv = $func($jv, $pad);
			$txt .= sprintf("\r\n%s%-{$len}s : %s", $pad, $jk, $jv);

			$cnt--;
			if ( $cnt > 0 )
				$txt .= ' ,';
		} // foreach ( $json as $jk => $jv )

		$txt .= sprintf("\r\n%s%s", $tab, '}');
		return $txt;
	}
}
//////////////////////////////
function jsonfile( $pretty, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$json = json_decode($file, true);
	if ( empty($json) )  return;

	echo "JSON $fname\n";
	if ( $pretty )
	{
		//$file = json_encode($json, JSON_PRETTY_PRINT);
		//$file = str_replace('    ', "\t", $file);
		$file = json_pretty($json, '');
	}
	else
		$file = json_encode($json);

	//echo "$file\n";
	file_put_contents($fname, $file);
	return;
}

$pretty = true;
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '0':  $pretty = false; break;
		case '1':  $pretty = true;  break;
		default:
			jsonfile( $pretty, $argv[$i] );
			break;
	}
}

/*
	Difference with PHP json_encode() -> json_pretty()
	- "key" : value                   -> aligned
	- simple array (no nested arrays) -> one line
	- floats max 5 precision          -> fixed 6 precision
	- tabs = 4 spaces                 -> 2 spaces
 */
