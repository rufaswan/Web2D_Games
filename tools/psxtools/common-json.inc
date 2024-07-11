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
class json_pretty
{
	private function jval_keylen( &$val )
	{
		$len = 0;
		foreach ( $val as $k => $v )
		{
			if ( strlen($k) > $len )
				$len = strlen($k);
		}
		return $len;
	}

	private function jval_is_list( &$val )
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

	private function jval_has_array( &$val )
	{
		foreach ( $val as $v )
		{
			if ( is_array($v) )
				return true;
		}
		return false;
	}

	private function jval_str( &$val )
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
		//   19.0000001
		// = 19.000000
		// = 19
		$b1 = (int)($val * 1000000);
		$b2 =  (int)$val * 1000000;
		if ( $b1 === $b2 ) // is .000000 , whole number = int
			return sprintf('%d', $val);
		else // has decimals = float
		{
			// ERROR
			// bf7fffff = -0.99999994039536
			// if -999999 === 0 , not int
			// else
			//   %.6f = -1.000000 , rtrim() = -1. error
			$b2 = sprintf('%.6f', $b1 * 0.000001);
			return rtrim($b2, '0');
		}
	}

	private function run( &$json, $tab )
	{
		if ( ! is_array($json) )
			return $this->jval_str($json);

		if ( empty($json) )
			return '[]';

		$is_list   = $this->jval_is_list  ($json); // check if keys are 0,1,2,...,n-1
		$has_array = $this->jval_has_array($json); // check for nested array
		if ( $is_list && ! $has_array )
		{
			// simple array []
			// put everything in one-line
			foreach ( $json as $jk => $jv )
				$json[$jk] = $this->jval_str($jv);
			return sprintf('[ %s ]', implode(' , ', $json));
		}

		$func = __FUNCTION__;
		$cnt  = count($json);
		$pad  = $tab . '  ';

		if ( $is_list )
		{
			// nested array []
			// put one item per line
			$txt = '[';

			foreach ( $json as $jv )
			{
				$jv = $this->$func($jv, $pad);
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
			// dictionary array {}
			// put key=value per line
			$txt = '{';

			$len = $this->jval_keylen($json) + 2;
			foreach ( $json as $jk => $jv )
			{
				$jk = "\"$jk\"";
				$jv = $this->$func($jv, $pad);
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

	static public function encode( &$json )
	{
		$class = new self;
		return $class->run($json, '');
	}
} // class json_pretty

// Difference with PHP json_encode() -> json_pretty->encode()
// - "key" : value                   -> aligned
// - simple array (no nested arrays) -> one line
// - tabs = 4 spaces                 -> 2 spaces
// - native LF or CRLF               -> always CRLF
if ( ! function_exists('json_encode') )
{
	function json_encode($value, $option='', $depth=512)
	{
		return json_pretty::encode($value);
	}
}
//////////////////////////////
function json_decode_comment( &$txt )
{
	if ( empty($txt) )
		return 0;

	// CRLF -> LF
	if ( strpos($txt, "\r\n") !== false )
		$txt = str_replace("\r", ''  , $txt);
	if ( strpos($txt, "\r") !== false )
		$txt = str_replace("\r", "\n", $txt);

	// remove comments
	//   multi-line   /* ... */
	//   single-line  // ...
	while (1)
	{
		$st = strpos($txt, '/*');
		if ( $str === false )
			break;

		$ed = strpos($txt, '*/', $st+2);
		if ( $ed === false ) // missing end tag == until file end
			$ed = strlen($txt);
		else
			$ed += 2;

		$sub = substr($text, $st, $ed-$st);
		$txt = str_replace($sub, '', $text);
	} // while (1)

	$line = explode("\n", $txt);
	foreach ( $line as $k => $v )
	{
		$ln = trim($v);
		$st = strpos($ln, '//');
		if ( $st !== false )
			$ln = substr($ln, 0, $st);
		$line[$k] = $ln;
	} // foreach ( $line as $k => $v )

	$txt = implode('', $line);
	return json_decode($txt, true);
}
