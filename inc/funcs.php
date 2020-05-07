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
function trace()
{
	if ( ! TRACE )  return;
	$args = func_get_args();
	$var  = array_shift($args);
	if ( $var[0] != '=' )
		$var .= "\n";

	$log = vsprintf($var, $args);
	if ( TRACE_OB )
		echo $log;
	else
		file_put_contents(SAVE_FILE . "log", $log, FILE_APPEND);
	return;
}

function debug()
{
	$args = func_get_args();
	$var  = array_shift($args);

	$log = vsprintf("ERROR {$var}\n", $args);
	file_put_contents(SAVE_FILE . "log", $log, FILE_APPEND);
	return;
}

function req_define( $define )
{
	if ( ! defined($define) )
		exit("ERROR $define not defined!\n");
}

function str2int( &$str, $pos, $byte )
{
	$s = substr($str, $pos, $byte);
	return ordint($s);
}

function ordint( $str )
{
	$len = strlen($str);
	$int = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$int += ($b << ($i*8));
	}
	return $int;
}

function chrint( $int, $byte = 0 )
{
	$str = "";
	for ( $i=0; $i < $byte; $i++ )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
	}
	while ( strlen($str) < $byte )
		$str .= ZERO;
	return $str;
}

function box_inter( $box1 , $box2 )
{
	list($x1a,$y1a,$w1,$h1) = $box1;
	list($x2a,$y2a,$w2,$h2) = $box2;
	$x1b = $x1a + $w1 - 1;
	$y1b = $y1a + $h1 - 1;
	$x2b = $x2a + $w2 - 1;
	$y2b = $y2a + $h2 - 1;
	if ( $x2b < $x1a )  return false;
	if ( $x1b < $x2a )  return false;
	if ( $y2b < $y1a )  return false;
	if ( $y1b < $y2a )  return false;
	return true;
}

function box_within( $big , $small )
{
	list($x1a,$y1a,$w1,$h1) = $big;
	list($x2a,$y2a,$w2,$h2) = $small;
	if ( $w2 > $w1 )  return false;
	if ( $h2 > $h1 )  return false;
	$x1b = $x1a + $w1 - 1;
	$y1b = $y1a + $h1 - 1;
	$x2b = $x2a + $w2 - 1;
	$y2b = $y2a + $h2 - 1;
	if ( $x1a > $x2a )  return false;
	if ( $x1b < $x2b )  return false;
	if ( $y1a > $y2a )  return false;
	if ( $y1b < $y2b )  return false;
	return true;
}

function init_input( $get )
{
	global $gp_input;
	$gp_input = array();

	$type = array_shift($get);
	$type = strtolower($type);
	switch ( $type )
	{
		case "mouse":
			$gp_input[$type] = array(0+$get[0] , 0+$get[1]);
			return;
		case "key":
		case "select":
			$gp_input[$type] = 0 + $get[0];
			return;
	}
	return;
}

function init_cheat()
{
	global $gp_init;
	if ( empty( $gp_init["cheat"] ) )
		return;

	global $gp_pc;
	$var = &$gp_pc["var"];
	foreach ( $gp_init["cheat"] as $cht )
	{
		//trace("cheat $cht");
		$t1  = explode(',', $cht);
		if ( empty($t1[1]) )
			$t1[1] = '0';
		eval( "if ( {$t1[1]} )  {$t1[0]};" );
	}
	return;
}

function gp_init_cfg( $fname )
{
	global $gp_init;
	foreach( file($fname, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;

		$line = strtolower($line);
		$sep = strpos($line, '=');
		$k = substr($line, 0, $sep);
		$v = substr($line, $sep+1);
		if ( strpos($k, '[]') )
		{
			$k = str_replace('[]', '', $k);
			$gp_init[$k][] = $v;
		}
		else
			$gp_init[$k] = $v;
	}
	return;
}

function scanfiles( $dir, &$result )
{
	$func = __FUNCTION__;
	foreach ( scandir($dir) as $d )
	{
		if ( $d[0] == '.' )  continue;
		if ( is_dir("$dir/$d") )
			$func( "$dir/$d", $result );
		else
			$result[] = "$dir/$d";
	}
	return;
}

function init_listfile( $forced )
{
	if ( ! $forced && file_exists( LIST_FILE ) )
		return;
	$res = array();
	$dir = ROOT ."/". GAME;
	scanfiles( $dir, $res );

	$len = strlen(ROOT);
	$buf = "";
	foreach ( $res as $r )
		$buf .= substr($r, $len+1) . "\n";

	file_put_contents( LIST_FILE, $buf );
	return;
}

function fgetline( $txtfile, $line )
{
	//$file = file($txtfile, FILE_IGNORE_NEW_LINES);
	//if ( ! isset($file[$line]) )
		//return "";
	//return $file[$line];

	// simpler
	$fp = fopen($txtfile, "r");
	if ( ! $fp )  return "";
	$cur = 0;
	while ( ! feof($fp) )
	{
		$str = fgets($fp);
		if ( $cur == $line )
			return rtrim($str, "\r\n");
		$cur++;
	}
	return "";
}

function save_savefile( $ext, &$pc )
{
	file_put_contents(SAVE_FILE . $ext, json_encode($pc) );

	//$pc = "<?php\n\$pc=". var_export($pc,true) .";";
	//file_put_contents(SAVE_FILE . $ext, $pc );
	return;
}

function load_savefile( $ext )
{
	$pc = array();
	$save = SAVE_FILE . $ext;
	if ( ! file_exists($save) )
		return $pc;

	$pc = json_decode( file_get_contents($save), true);

	//$pc = file_get_contents(SAVE_FILE . $ext);
	//eval("\$pc={$pc};");
	//require SAVE_FILE . $ext;

	return $pc;
}

function time2date( $time )
{
	// year-month-day hour-min-sec
	$date = date("Y,n,j,G,i,s", $time);
	return explode(',', $date);
}

// multi-bits AND , as in ($var & 0xc0)
function bit_and( $val, $flags )
{
	$r = $val & $flags;
	return ( $r == $flags );
}

function var_math( $opr, $v1, $v2 )
{
	$n = 0;
	switch ( $opr )
	{
		case '+':   case "add":  $n = $v1 + $v2; break;
		case '-':   case "sub":  $n = $v1 - $v2; break;
		case '*':   case "mul":  $n = $v1 * $v2; break;
		case '/':   case "div":  $n = $v1 / $v2; break;
		case '%':   case "rem":  $n = $v1 % $v2; break;
		case '&':   case "and":  $n = $v1 & $v2; break;
		case '|':   case "or" :  $n = $v1 | $v2; break;
		case '^':   case "xor":  $n = $v1 ^ $v2; break;
		case '<':   case "lt" :  $n = ($v1 <  $v2); break;
		case '>':   case "gt" :  $n = ($v1 >  $v2); break;
		case '<=':  case "lte":  $n = ($v1 <= $v2); break;
		case '>=':  case "gte":  $n = ($v1 >= $v2); break;
		case '==':  case "eq" :  $n = ($v1 == $v2); break;
		case '!=':  case "neq":  $n = ($v1 != $v2); break;
	}
	return (int)$n;
}

// var always positive , for width/height
function var_size( $n )
{
	if ( $n < 0 )
		$n *= -1;
	return $n;
}
// var no lower than min
function var_min( $var, $min )
{
	if ( $var < $min )
		return $min;
	return $var;
}
// var no higher than max
function var_max( $var, $max )
{
	if ( $var > $max )
		return $max;
	return $var;
}

function var_box( $x, $y, $w, $h, $bw, $bh )
{
	$x1 = var_min($x, 0);
	$y1 = var_min($y, 0);
	$x2 = var_max($x + $w, $bw);
	$y2 = var_max($y + $h, $bh);
	return array($x1, $y1, $x2-$x1, $y2-$y1);
}

// str meant to be boolean
function var_bool( $str )
{
	$s = strtolower($str);
	$ok = array("true" , "on" , "yes", "enable" , "show");
	$no = array("false", "off", "no" , "disable", "hide");
	if ( in_array($s, $ok) )  return true;
	if ( in_array($s, $no) )  return false;
	return $str;
}

function str_html( $str )
{
	$rs = array('&'    ,'<'   ,'>'   ,'"'     ,"'"     );
	$rp = array("&amp;","&lt;","&gt;","&quot;","&apos;");
	return str_replace($rs, $rp, $str);
}

function unstr_html( $str )
{
	$rs = array("&amp;","&lt;","&gt;","&quot;","&apos;");
	$rp = array('&'    ,'<'   ,'>'   ,'"'     ,"'"     );
	return str_replace($rs, $rp, $str);
}
