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
$gp_code  = array();
$gp_label = array();
$gp_php   = array();
$gp_array = array();

define("FUNC_MSG", "sco_msg");
define("FUNC_CMD", "sco_cmd");
define("FUNC_JAL", "sco_jal");
define("PC_VAR" , '$gp_pc["var"]');
define("PC_LINE", '$gp_pc["pc"][1]');
////////////////////////////////////////
function findline( $label, $cur )
{
	global $gp_label;
	if ( ! isset( $gp_label[$label] ) )
	{
		echo "ERROR label $label for $cur not found\n";
		return 0;
	}
	$nxt = $gp_label[$label];
	//return ($nxt - $cur);
	return $nxt;
}

function ext_varno( $str , $cnt )
{
	$var = varid($str);
	if ( $var == -1 )  return $str;

	$n = array();
	for ( $i=0; $i < $cnt; $i++ )
		$n[] = '&' .PC_VAR. '[' . $var+$i . ']';
	return implode(' , ', $n);
}

function varid( $str )
{
	if ( empty($str) )
		return -1;
	if ( $str[0] != '|' )
		return -1;

	$var = explode( '|', trim($str,'|') );
	return $var[0] + $var[1];
}

function varno( $str, $ref )
{
	if ( empty($str) )
		return $str;
	if ( $str[0] != '|' )
		return $str;

	if ( $ref )
	{
		$v1 = varid( $str );
		return '&' .PC_VAR. "[$v1]";
	}
	else
	{
		$str = substr($str, 1, -1);
		$sep = strpos($str, '|');

		$v1 = substr($str, 0, $sep);
		$v2 = substr($str, $sep+1);

		global $gp_array;
		if ( isset( $gp_array[$v1] ) )
		{
			$v2 = calli( $v2 );
			return PC_VAR . "[$v1][$v2]";
		}
		else
		{
			if ( ! is_numeric($v2) )
				echo "ERROR VAR not array = $v1 [$v2]\n";
			$v1 += $v2;
			return PC_VAR . "[$v1]";
		}
	}
	return "";
}

function calli( $str )
{
	if ( is_numeric($str) )
		return $str;

	if ( $str[0] == '|' )
		return varno($str, false);

	$func = __FUNCTION__;
	if ( $str[0] == '(' && $str != '(' )
	{
		$exp = explode(' ', $str);
		foreach ( $exp as $k => $v )
			$exp[$k] = $func( $v );
		return implode(' ', $exp);
	}

	return $str;
}
////////////////////////////////////////
function save_phpasm( $outfile )
{
	global $gp_code, $gp_php;
	$end = count($gp_code);

	$fp = fopen($outfile, "w");
	if ( ! $fp )  return;

	for ( $i=0; $i < $end; $i++ )
	{
		if ( isset( $gp_php[$i] ) )
			fwrite($fp, "{$gp_php[$i]}\n");
		else
			fwrite($fp, "\n");
	}
	fclose($fp);
}

function sco_cmd( $ck, $cv )
{
	global $gp_code, $gp_label, $gp_php, $gp_array;

	$cv = explode(',', $cv);
	$sco = $cv[0];
	$pos = $cv[1];
	$cmd = "'{$cv[2]}'";
	//$cmd = sprintf('%-8s', "'{$cv[2]}'");
	$arg = array_slice($cv, 3);

	switch ( $cv[2] )
	{
		case 'S350':
		case 'S351':
		case '153S':
		case 'S360':
		case 'S380':
		case 'for_0':
			return;
		case '!':
			$vno = varno( $arg[0], false );
			$con = calli( $arg[1] );
			$gp_php[$ck] = "$vno = $con;";
			return;
		//////////////////////////////
		case '@': // label jump
			$loc = $arg[0];
			$lno = findline("$sco,$loc", $ck);

			$gp_php[$ck] = PC_LINE . " = $lno;";
			return;
		case '{': // if
			$con = calli( $arg[0] );

			$loc = $arg[1];
			$lno = findline("$sco,$loc", $ck);

			$gp_php[$ck] = "if ( ! $con )  " .PC_LINE. " = $lno;";
			return;
		case 'for_1': // for/while
			list($loc,$exp,$plus,$stp) = $arg;

			$lno = findline("$sco,$loc", $ck);
			$exp = calli($exp);

			$var = "FOR";
			$t1 = explode(',', $gp_code[$ck-2]);
			if ( $t1[2] == '!' )
				$var = calli($t1[3]);

			$add = ( $plus ) ? $stp : $stp * -1;
			$gp_php[$lno - 1] = "$var += $add; ";

			$add = ( $plus ) ? '>' : '<';
			$gp_php[$ck] = "if ( $var $add $exp )  " .PC_LINE. " = $lno;";
			return;
		case '>': // end loop FOR and WHILE(IF)
			$loc = $arg[0];
			$lno = findline("$sco,$loc", $ck);

			$t1 = "";
			if ( isset( $gp_php[$ck] ) )
				$t1 .= $gp_php[$ck];
			$t1 .= PC_LINE . " = $lno;";
			$gp_php[$ck] = $t1;
			return;
		//////////////////////////////
		case '\\': // label call
			$t1 = $sco;
			$t2 = $arg[0];
			if ( $arg[0] )
				$gp_php[$ck] = FUNC_CMD . "( 'call' , array( $t1 , $t2 ) );";
			else
				$gp_php[$ck] = FUNC_CMD . "( 'call' , array( 0 , 0 ) );";
			return;
		case '%': // page call
			$id = calli( $arg[0] );
			$gp_php[$ck] = FUNC_CMD . "( 'call' , array( $id , 0 ) );";
			return;
		case '&': // page jump
			$id = calli( $arg[0] );
			$gp_php[$ck] = FUNC_CMD . "( 'jump' , array( $id , 0 ) );";
			return;
		//////////////////////////////
		case "sel_st":
			$loc = $arg[0];
			$lno = findline("$sco,$loc", $ck);
			$gp_php[$ck] = FUNC_CMD . "( 'sel_st' , array( $lno ) );";
			return;
		case "sel_ed":
			$gp_php[$ck] = FUNC_CMD . "( 'sel_ed' , array(  ) );";
			return;
		//////////////////////////////
		case "DF":
			$var = varid( $arg[0] );
			$gp_array[$var] = 1;

			$arg[0] = varno($arg[0], true);
			$arg = implode(' , ', $arg);
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $arg ) );";
			return;
		case "DS":
			$var = varid( $arg[1] );
			$gp_array[$var] = 1;

			$arg[0] = varno($arg[0], true);
			$arg[1] = varno($arg[1], true);
			$arg = implode(' , ', $arg);
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $arg ) );";
			return;
		//////////////////////////////
		case "ZZ_3": // extend => width,height,bits
		case "ZZ_9": // extend => width,height,bits
			$var = ext_varno( $arg[0] , 3 );
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $var ) );";
			return;
		case "SC": // extend => track,min,sec,frame
			$var = ext_varno( $arg[0] , 4 );
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $var ) );";
			return;
		case "ZT_0": // extend => year,month,days,hour,min,sec,day
			$var = ext_varno( $arg[0] , 7 );
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $var ) );";
			return;

		case "GS": // extend => x,y,w,h
			$num = $arg[0];
			$var = ext_varno( $arg[1] , 4 );
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $num , $var ) );";
			return;
		case "LT": // extend => year,month,days,hour,min,sec
			$num = $arg[0];
			$var = ext_varno( $arg[1] , 6 );
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $num , $var ) );";
			return;
		//////////////////////////////
		default:
			foreach ( $arg as $ak => $av )
			{
				if ( $av[0] == '(' )
				{
					$arg[$ak] = calli($av);
					continue;
				}
				if ( $av[0] == '|' )
				{
					$arg[$ak] = varno($av, true);
					continue;
				}
				if ( $av[0] == '@' )
				{
					$v = explode('=', $av);
					if ( $v[0] == '@MSG' )
						$arg[$ak] = FUNC_MSG . "( {$v[1]} )";
					continue;
				}
			} // foreach ( $arg as $ak => $av )
			$arg = implode(' , ', $arg);

			//$t1 = "\$arg = array( $arg ); " .FUNC_CMD. "( $cmd , \$arg );";
			$gp_php[$ck] = FUNC_CMD . "( $cmd , array( $arg ) );";
			return;
	} // switch ( $cv[2] )

	return;
}

function phpasm( $infile, $outfile )
{
	global $gp_code, $gp_label, $gp_php;

	$gp_code = file($infile, FILE_IGNORE_NEW_LINES);
	if ( empty($gp_code) )  return;

	$gp_label = array();
	foreach( $gp_code as $ck => $cv )
	{
		if ( empty($cv) )
			continue;
		if ( $cv[0] == '#' )
			continue;

		$t1 = explode(',', $cv);
		$t2 = "{$t1[0]},{$t1[1]}";
		if ( isset($gp_label[$t2]) )
			echo "DUPL label $t2 for $ck [now {$gp_label[$t2]}]\n";

		$gp_label[$t2] = $ck;
	}

	$gp_php = array();
	foreach( $gp_code as $ck => $cv )
	{
		if ( empty($cv) )
			continue;
		if ( $cv[0] == '#' )
		{
			$gp_php[$ck] = str_replace('#', '//', $cv);
			continue;
		}

		sco_cmd( $ck, $cv );

	} // foreach( $gp_code as $k => $v )

	save_phpasm( $outfile );
	return;
}
phpasm("sco_code.txt" , "phpasm.php");
