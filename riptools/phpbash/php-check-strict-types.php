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

function del_line_str( string &$line ) : void
{
	$p = 0;
	while (1)
	{
		if ( ! isset($line[$p]) )
			return;

		if ( $line[$p] === '"' || $line[$p] === "'" )
		{
			$func = __FUNCTION__;
			$c  = $line[$p];
			$st = $p;
				$p++;

			while (1)
			{
				if ( ! isset($line[$p]) || $line[$p] === $c )
				{
					$str  = substr($line, $st, $p+1-$st);
					$line = str_replace($line, $str, '');
					$func($line);
					return;
				}
				if ( $line[$p] === '\\' )
					$p += 2;
				else
					$p++;
			} // while (1)
		}

		if ( $line[$p] === '\\' )
			$p += 2;
		else
			$p++;
	} // while (1)
}

function phpfile_check( string $fname ) : void
{
	$ext = substr($fname, strrpos($fname,'.'));
	if ( $ext !== '.inc' && $ext !== '.php' )
		return;

	$file = file($fname, FILE_IGNORE_NEW_LINES);
	if ( empty($file) )
		return;
	echo "= $fname\n";

	$ed = count($file);
	$st = 0;
	while ( $st < $ed )
	{
		$line = trim($file[$st]);
			$st++;

		if ( empty($line) )
			continue;

		// skip multi-line comment
		if ( substr($line,0,2) === '/*' )
		{
			while ( strpos($file[$st],'*/') === false )
				$st++;
			$st++;
			continue;
		}

		// remove single-line comment
		$p = strpos($line, '//');
		if ( $p !== false )
			$line = substr($line, 0, $p);

		// remove strings
		del_line_str($line);

		// function + method
		$p = strpos($line, 'function ');
		if ( $p !== false )
		{
			$line = substr($line, $p+9);

			// if { not on next line
			$p = strpos($line, '{');
			if ( $p !== false )
				$line = substr($line, 0, $p);

			$argst =  strpos($line, '(');
			$arged = strrpos($line, ')') + 1;

			$name = substr($line,0       ,$argst);
			$args = substr($line,$argst+1,$arged-$argst-2);
			$retn = substr($line,$arged  );
				$name = trim($name);
				$args = trim($args);
				$retn = trim($retn);

			// all uppercase function name
			$upp = strtoupper($name);
			if ( $name === $upp )
				printf("%d : %s() uppercase\n", $st, $name);

			// if has a return type
			if ( empty($retn) )
				printf("%d : %s() missing return type\n", $st, $name);

			// if no args
			if ( empty($args) )
				continue;

			// if has type defined
			foreach ( explode(',',$args) as $arg )
			{
				// remove default value
				$p = strpos($arg, '=');
				if ( $p !== false )
					$arg = substr($arg,0, $p);

				$arg = trim($arg);
				if ( $arg[0] === '$' )
					printf("%d : %s() arg %s missing type\n", $st, $name, $arg);

				$p = strpos($arg, '$');
				$arg = substr($arg, $p);
				$upp = strtoupper($arg);
				if ( $arg === $upp )
					printf("%d : %s() arg %s uppercase\n", $st, $name, $arg);
			}
			continue;
		}

		$m = [];
		preg_match_all('|\$[A-Z0-9_]+|', $line, $m);
		if ( ! empty($m) )
		{
			foreach ( $m[0] as $var )
			{
				$upp = strtoupper($var);
				if ( $var === $upp )
					printf("%d : var %s uppercase\n", $st, $var);
			} // foreach ( $m[0] as $var )
		}
	} // while ( $st < $ed )
}

function phpcheck( string $ent ) : void
{
	if ( is_file($ent) )
		phpfile_check($ent);
	if ( is_link($ent) )
		return;
	if ( is_dir($ent) )
	{
		$ent  = rtrim($ent, '\\/');
		$func = __FUNCTION__;
		foreach ( scandir($ent) as $e )
		{
			if ( $e[0] === '.' )
				continue;
			$func("$ent/$e");
		} // foreach ( scandir($ent) as $e )
	}
}

for ( $i=1; $i < $argc; $i++ )
	phpcheck( $argv[$i] );
