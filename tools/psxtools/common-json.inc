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
	// is string
	if ( "$val" === $val )
	{
		// decode failed
		//   escape the escape char
		$val = str_replace('\\', '\\\\', $val);
		return sprintf('"%s"', $val);
	}

	// is int/float
	$b1 = (int)($val * 1000000);
	$b2 =  (int)$val * 1000000;
	if ( $b1 === $b2 ) // is .000000 , whole number = int
		return sprintf('%d', $val);
	else // has decimals = float
	{
		$b1 = sprintf('%.6f', $val);
		return rtrim($b1, '0');
	}
}
//////////////////////////////
function json_pretty( &$json, $tab )
{
	if ( ! is_array($json) )
		return jval_str($json);

	if ( empty($json) )
		return '[]';

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

/*
	Difference with PHP json_encode() -> json_pretty()
	- "key" : value                   -> aligned
	- simple array (no nested arrays) -> one line
	- tabs = 4 spaces                 -> 2 spaces
	- native LF or CRLF               -> always CRLF
 */
