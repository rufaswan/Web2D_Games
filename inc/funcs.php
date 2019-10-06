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

function str2int( &$str, &$pos, $byte )
{
	$int = 0;
	for ( $i=0; $i < $byte; $i++ )
	{
		$c = ord( $str[$pos+$i] );
		$int += ($c << ($i*8));
	}
	$pos += $byte;
	return $int;
}

function int2str( $int, $byte, $big = false )
{
	$str = "";
	if ( $big )
	{
		while ( $byte > 0 )
		{
			$byte--;
			$n = ($int >> ($byte*8)) & 0xff;
			$str .= chr($n);
		}
	}
	else
	{
		while ( $byte > 0 )
		{
			$byte--;
			$n = $int & 0xff;
			$str .= chr($n);
			$int >>= 8;
		}
	}
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

function cheat_exp( $exp )
{
	if ( empty($exp) )
		return false;

	$m = array();
	preg_match_all("@[0-9a-zA-Z]+|[^0-9a-zA-Z]@", $exp, $m);
	$m = $m[0];
	$len = count($m);
	if ( $len < 3 )
		return true;

	global $gp_pc;
	$opr = "";
	$v1 = 0;
	$v2 = 0;

	$st = 0;
	//print_r($m);
	if ( $m[$st] == '&' )
	{
		$v1 = &$gp_pc["var"][ $m[$st+1] ];
		$st += 2;
	}
	else
	{
		$v1 = $m[$st];
		$st++;
	}

	if ( $m[$st+1] == '=' )
	{
		$opr = $m[$st+0] . $m[$st+1];
		$st += 2;
	}
	else
	{
		$opr = $m[$st];
		$st++;
	}

	if ( $m[$st] == '&' )
	{
		$v2 = &$gp_pc["var"][ $m[$st+1] ];
		$st += 2;
	}
	else
	{
		$v2 = $m[$st];
		$st++;
	}

	//trace("cheat $opr , $v1 , $v2");
	switch ( $opr )
	{
		case '=':  $v1  = $v2; return false;
		case '+=': $v1 += $v2; return false;
		case '-=': $v1 -= $v2; return false;
		case '*=': $v1 *= $v2; return false;
		case '/=': $v1 /= $v2; return false;
	}
	return var_math($opr,$v1,$v2);
}

function init_cheat()
{
	global $gp_init;
	if ( empty( $gp_init["cheat"] ) )
		return;

	foreach ( $gp_init["cheat"] as $cht )
	{
		//trace("cheat $cht");
		$t1  = explode(',', $cht);
		if ( cheat_exp( $t1[1] ) )
			cheat_exp( $t1[0] );
	}
}

function initcfg_var( $fname )
{
	$var = array();
	$file = file( $fname );
		if ( empty($file) )  return $var;

	foreach( $file as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;

		$line = str_replace(' ', '', $line);
		$line = strtolower($line);
		$sep = strpos($line, '=');
		$k = substr($line, 0, $sep);
		$v = substr($line, $sep+1);
		if ( strpos($k, '[]') )
		{
			$k = str_replace('[]', '', $k);
			$var[$k][] = $v;
		}
		else
			$var[$k] = $v;
	}
	return $var;
}

function fileline( $txtfile, $id )
{
	$fp = fopen($txtfile, "r");
	if ( ! $fp )
		debug("fileline fopen $txtfile");

	$src = "$id,";
	$len = strlen($src);
	$no  = 0;
	while ( ! feof($fp) )
	{
		$line = fgets($fp);
		if ( strpos($line,$src) === 0 )
		{
			$line = rtrim($line);
			$r = array($k, substr($line, $len));
			return $r;
		}
		$no++;
	}
	return array(-1, "");
}

function fileline2( $txtfile, $id1, $id2 )
{
	return fileline( $txtfile, "$id1,$id2" );
}

function findfile( $sprint , $num , $default , $b )
{
	if ( $num < 0 )
		return $default;

	$fn = $sprint;
	$s  = count_chars($sprint, 1);
	switch ( $s[0x25] ) // %
	{
		case 1: $fn = sprintf($sprint,  $num);  break;
		case 2: $fn = sprintf($sprint, ($num >> $b),      $num);  break;
		case 3: $fn = sprintf($sprint, ($num >> (2*$b)), ($num >> $b),      $num);  break;
		case 4: $fn = sprintf($sprint, ($num >> (3*$b)), ($num >> (2*$b)), ($num >> $b), $num);  break;
	}
	if ( empty($default) )
		return $fn;

	if ( ! file_exists( ROOT . "/$fn" ) )
		$fn = $default;
	return $fn;
}

function pc_save( $ext, $pc )
{
	file_put_contents(SAVE_FILE . $ext, json_encode($pc) );

	//$pc = "<?php\n\$pc=". var_export($pc,true) .";";
	//file_put_contents(SAVE_FILE . $ext, $pc );
}

function pc_load( $ext )
{
	$save = SAVE_FILE . $ext;
	$pc = array();
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
		case '|':   case "or":   $n = $v1 | $v2; break;
		case '^':   case "xor":  $n = $v1 ^ $v2; break;
		case '<':   case "lt":   $n = ($v1 <  $v2); break;
		case '>':   case "gt":   $n = ($v1 >  $v2); break;
		case '<=':  case "lte":  $n = ($v1 <= $v2); break;
		case '>=':  case "gte":  $n = ($v1 >= $v2); break;
		case '==':  case "eq":   $n = ($v1 == $v2); break;
		case '!=':  case "neq":  $n = ($v1 != $v2); break;
	}
	return (int)$n;
}

// var always positive , for width/height
function var_size( $n )
{
	if ( $n < 0 )
		return $n * -1;
	else
		return $n;
}
// var no lower than min
function var_min( $var, $min )
{
	return ( $var < $min ) ? $min : $var;
}
// var no higher than max
function var_max( $var, $max )
{
	return ( $var > $max ) ? $max : $var;
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
