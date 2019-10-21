<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
function get_FUNC_ain4( $fn, &$file, &$st, &$varc )
{
	$res = array(
		$fn,
		sint32($file, $st) - 6, // start offset of CODE
		utf8txt($file, $st), // func name

		sint32($file, $st), // from OJMP
		sint32($file, $st), // return type
		sint32($file, $st), // if type is STRT , STRT type

		sint32($file, $st), // args count
		sint32($file, $st), // vars count
		sint32($file, $st), // crc32
	);
	$varc = $res[7];
	$r = implode(',', $res);
	return $r;
}

function get_FUNC_ain7( $fn, &$file, &$st, &$varc )
{
	// removed OJMP
	$res = array(
		$fn,
		sint32($file, $st) - 6, // start offset of CODE
		utf8txt($file, $st), // func name

		sint32($file, $st), // return type
		sint32($file, $st), // if type is STRT , STRT type

		sint32($file, $st), // args count
		sint32($file, $st), // vars count
		sint32($file, $st), // crc32
	);
	$varc = $res[6];
	$r = implode(',', $res);
	return $r;
}

function get_FUNC_ain11( $fn, &$file, &$st, &$varc )
{
	// added 2 ???
	$res = array(
		$fn,
		sint32($file, $st) - 6, // start offset of CODE
		utf8txt($file, $st), // func name

		sint32($file, $st), // return type
		sint32($file, $st), // if type is STRT , STRT type

		sint32($file, $st),
	);
	$pad = 0;
	if ( $res[5] > 0 )
	{
		$res[] = sint32($file, $st);
		$res[] = sint32($file, $st);
		$res[] = sint32($file, $st);
		$pad = 3;
	}
	$res[] = sint32($file, $st); // args count
	$res[] = sint32($file, $st); // vars count
	$res[] = sint32($file, $st);
	$res[] = sint32($file, $st); // crc32

	$varc = $res[7+$pad];
	$r = implode(',', $res);
	echo "$r\n";
	return $r;
}
//////////////////////////////
function get_FUNC_arg_ain4( &$file, &$st )
{
	$res = array(
		utf8txt($file, $st), // var name
		sint32($file, $st), // var type
		sint32($file, $st), // if type is STRT , STRT type
		sint32($file, $st), // array dept
	);
	$r = ',' . implode(',', $res);
	return $r;
}

function get_FUNC_arg_ain8( &$file, &$st )
{
	$res = array(
		utf8txt($file, $st), // var name
		sint32($file, $st), // var type
		sint32($file, $st), // if type is STRT , STRT type
		sint32($file, $st), // array dept
		sint32($file, $st),
	);
	if ( $res[4] > 0 )
		$res[] = sint32($file, $st);
	$r = ',' . implode(',', $res);
	return $r;
}

function get_FUNC_arg_ain14( &$file, &$st )
{
	$res = array(
		utf8txt($file, $st), // var name
		utf8txt($file, $st),
		sint32($file, $st), // var type
		sint32($file, $st), // if type is STRT , STRT type
		sint32($file, $st), // array dept
		sint32($file, $st),
	);
	if ( $res[5] > 1 )
	{
		$res[] = sint32($file, $st);
		$res[] = sint32($file, $st);
		$res[] = sint32($file, $st);
	}
	else
	if ( $res[5] > 0 )
		$res[] = sint32($file, $st);

	$r = ',' . implode(',', $res);
	echo "$r\n";
	return $r;
}
//////////////////////////////
function get_DELG_ain4( $fn, &$file, &$st, &$varc )
{
	$res = array(
		$fn,
		utf8txt($file, $st), // delegate name
		sint32($file, $st), // return type
		sint32($file, $st), // if type is STRT , STRT type

		sint32($file, $st), // args count
		sint32($file, $st), // vars count
	);
	$varc = $res[5];
	$r = implode(',', $res);
	return $r;
}

function get_DELG_ain11( $fn, &$file, &$st, &$varc )
{
	// added ???
	$res = array(
		$fn,
		utf8txt($file, $st), // delegate name
		sint32($file, $st), // return type
		sint32($file, $st), // if type is STRT , STRT type

		sint32($file, $st),
		sint32($file, $st), // args count
		sint32($file, $st), // vars count
	);
	$varc = $res[6];
	$r = implode(',', $res);
	echo "$r\n";
	return $r;
}
//////////////////////////////
