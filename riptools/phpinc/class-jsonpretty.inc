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
declare(strict_types=1);

// Difference with PHP json_encode() -> json_pretty->encode()
// - "key" : value                   -> aligned
// - simple array (no nested arrays) -> one line
// - tabs = 4 spaces                 -> 2 spaces
// - native LF or CRLF               -> always CRLF
class jsonpretty
{
	private function jval_keylen( array &$val ) : int
	{
		$len = 0;
		foreach ( $val as $k => $v )
		{
			if ( strlen($k) > $len )
				$len = strlen($k);
		}
		return $len;
	}

	private function jval_is_list( array &$val ) : bool
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

	private function jval_has_array( array &$val ) : bool
	{
		foreach ( $val as $v )
		{
			if ( is_array($v) )
				return true;
		}
		return false;
	}

	// https://learn.microsoft.com/en-us/sql/relational-databases/json/how-for-json-escapes-special-characters-and-control-characters-sql-server?view=sql-server-ver16
	// Escape of special characters
	//   x22  \"  Quotation mark (")
	//   x5c  \\  Backslash (\)
	//   x2f  \/  Slash (/)
	//   x08  \b  Backspace
	//   x0c  \f  Form feed
	//   x0a  \n  New line
	//   x0d  \r  Carriage return
	//   x09  \t  Horizontal tab
	private function escape_str( string &$str ) : void
	{
		// PHP bug : \b become \x5c\x62 , instead \x08
		$str = str_replace("\x5c", "\x5c\x5c", $str);
		$fr = ["\x22" , "\x2f" , "\x08" , "\x0c" , "\x0a" , "\x0d" , "\x09"];
		$to = ['\\"'  , '\\/'  , '\\b'  , '\\f'  , '\\n'  , '\\r'  , '\\t' ];
		$str = str_replace($fr, $to, $str);
	}

	// mixed $val , return string
	private function jval_str( &$val ) : string
	{
		// is string
		if ( "$val" === $val )
		{
			// decode failed
			//   escape the escape char
			$this->escape_str($val);
			return sprintf('"%s"', $val);
		}

		// is int/float
		//   19.0000001
		// = 19.000000
		// = 19
		// ERROR
		//   bf7fffff = -0.99999994039536
		//       %.6f = -1.000000
		//   -> rtrim('0') = -1.
		//   -> rtrim('.') = -1
		$num = number_format(0.0 + $val, 6, '.', '');
		$num = rtrim($num, '0'); // 0.500000 -> 0.5
		return rtrim($num, '.'); // 2.000000 -> 2.  -> 2
	}

	// mixed $json
	private function run( &$json, string $tab ) : string
	{
		if ( is_object($json) )
			$json = (array)$json;
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

		$func = [$this, __FUNCTION__];
		$cnt  = count($json);
		$pad  = $tab . '  ';

		if ( $is_list )
		{
			// nested array []
			// put one item per line
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
			// dictionary array {}
			// put key=value per line
			$txt = '{';

			$len = $this->jval_keylen($json) + 2;
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

	// mixed $json
	public static function encode( &$json ) : string
	{
		$class = new self;
		return $class->run($json, '');
	}
} // class jsonpretty

/*
if ( ! function_exists('json_encode') )
{
	// mixed $value
	function json_encode( $value, int $option='', int $depth=512 ) : string
	{
		return jsonpretty::encode($value);
	}
}
*/
//////////////////////////////
