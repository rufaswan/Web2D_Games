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
require "common.inc";
require "common_sco.inc";

$gp_msg  = array("", "iconv() error");
$gp_code = array("# <?php exit();\n");
$gp_func = array();

// based on games run with "system3.exe"
// for Windows PC
//  - prostudent G
//  - Ayumi-chan Monogatari
//  - Funny Bee
//  - Rance 4.1
//  - Rance 4.2
//  - Toushin Toshi 2 After...
//  - Mugen Houyou
$sco_cmd = <<<_TXT
-1,\,int16
-1,@,int16
-1,[,int16,int16
-1,{,401,int16
-1,:,401,int16,int16
-1,%,401
-1,&,401
-1,]
-1,A
-1,B,400,401,401,401,401,401,401
-1,E,401,401,401,401,401,401
-1,F
-1,G,401
-1,H,400,401
-1,I,401,401,401,401,401,401
-1,J,401,401
-1,K,400
-1,L,401
-1,M,403
-1,O,401,401
-1,Q,401
-1,P,401,401,401,401
-1,R
-1,S,400
-1,T,401,401
-1,U,401,401
-1,X,400
-1,Y,401,401
-1,Z,401,401
_TXT;

//////////////////////////////
function sco_msg( $msg )
{
	$id = add_str($msg);
	return array("@MSG=$id");
}

function sco_cmd_msg( &$file, &$st, &$jump )
{
	$len = 0;
	while (1)
	{
		$by = $file[$st+$len];
		if ( $by == ZERO || $by == ':' )
			break;
		if ( in_array($st+$len, $jump) )
			break;

		$b1 = ord($by);
		if ( $b1 >= 0xe0 )
			$len += 2;
		else
		if ( $b1 >= 0xa0 )
			$len++;
		else
		if ( $b1 >= 0x80 )
			$len += 2;
		else
		if ( $b1 == 0x20 )
			$len++;
		else
			break;
	}
	$str = substr($file, $st, $len);
	$st += $len;
	if ( $file[$st] == ZERO || $file[$st] == ':' )
		$st++;
	$str = utf8_conv( CHARSET, sjistxt($str) );
	if ( $str == "" )
		$str = "iconv() error";
	return array("MSG", $str);
}

function sco_get_cmd( &$file, &$st, &$select, &$jump )
{
	$cmd = ord( $file[$st] );
	if ( $cmd & 0x80 || $cmd == 0x20 )
		return sco_cmd_msg( $file, $st, $jump );
	if ( $cmd == 0x1a )
	{
		$st++;
		return array( "SYS_END", array() );
	}

	$cmd = $file[$st];
	global $sco_cmd;
	if ( isset( $sco_cmd[$cmd] ) )
	{
		$st++;
		$arg = sco_cmd_arg($file, $st, $sco_cmd[$cmd]);
		return array( $cmd, $arg );
	}

	$arg = array();
	switch ( $cmd )
	{
		case '!': // 0x21
			$st++;
			$arg[] = sco_varno($file, $st);
			$arg[] = sco_calli($file, $st);
			return array($cmd, $arg);
		case '$': // 0x24
			$st++;
			if ( $select )
			{
				$select = false;
				$cmd = "sel_ed";
			}
			else
			{
				$select = true;
				$cmd = "sel_st";
				$arg[] = sint16($file, $st);
			}
			return array($cmd, $arg);
		case '~': // 0x7e
			$st++;
			$t = sint16( $file, $st );
			$arg[] = $t;
			if ( $t == 0 || $t == -1 )
				$arg[] = sco_calli($file, $st);
			else
				$arg[] = sint16($file, $st);
			return array($cmd, $arg);
		case '<': // 0x3c
			$t = ord( $file[$st+1] );
			$st += 2;
			$cmd = "for_$t";
			if ( $t != 0 )
			{
				$arg[] = sint16($file, $st);
				$arg += sco_var_arg( 4, $file, $st );
			}
			return array($cmd, $arg);
	}
	return array(0,0);
}
//////////////////////////////
function save_sco( $fname, $var )
{
	$fp = fopen($fname, "w");
	foreach( $var as $v )
		fwrite($fp, "$v\n");
	fclose($fp);
}

function sco_run( $id, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )
		return;
	$file .= ZERO;

	//$ed = str2int($file, 0, 2) + 1;
	//if ( ! isset($file[$ed]) || $file[$ed] != chr(0x1a) )
		//return;
	printf("%s\n", $fname);

	global $gp_func;
	$select = false;
	$jump = array();

	$ed = strlen($file);
	$st = 2;
	$buf = "# $id,2\n";
	while ( $st < $ed )
	{
		$bak = $st;
		list($cmd,$arg) = sco_get_cmd( $file, $st, $select, $jump );
		if ( ! $cmd )
			break;

		if ( $cmd == "MSG" )
			$arg = sco_msg($arg);

		array_unshift($arg, $cmd);
		$buf .= "$id,$bak," .implode(',', $arg). "\n";

		switch ( $cmd )
		{
			case '@': // label jump
				$jump[] = $arg[0];
				break;
			case '<': // for
			case '{': // if
				$jump[] = $arg[1];
				break;
			case "sel_st":
				$jump[] = $arg[0];
				break;

			case '~': // function call
				$jal = "{$arg[0]},{$arg[1]}";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			case '%': // page call
				$jal = "{$arg[0]},0";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			case '\\': // label call
				$jal = "$id,{$arg[0]}";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			default:
				break;
		} // switch ( $cmd )

		if ( $st == $bak )
			printf("ERROR $fname + %x infinite loop\n", $st);

	} // while ( $st < $ed )
	if ( $file[$st] != ZERO )
		printf("ERROR $fname + %x unknown command\n", $st);

	global $gp_code;
	$gp_code[] = $buf;
	return;
}

function sco_init_cmd()
{
	global $sco_cmd;
	$cmd = array();
	foreach ( explode("\n", $sco_cmd) as $v1 )
	{
		$v2 = explode(',', $v1);
		$c = $v2[1];
		$a = array_slice($v2, 2);
		$cmd[$c] = $a;
	}
	//print_r($cmd);
	$sco_cmd = $cmd;
}
//////////////////////////////
sco_init_cmd();

foreach ( scandir('.') as $f )
{
	if ( $f[0] == '.' )
		continue;
	if ( is_dir($f) || $f == 'log' )
		continue;
	if ( stripos($f, '.txt') || stripos($f, '.php') )
		continue;

	$id = substr($f, 0, strrpos($f, '.')) + 0;
	sco_run($id, $f);
}
//sco_run(1, "001.dat");

$gp_func = array_keys($gp_func);
sort($gp_func);

save_sco("sco_code.txt", $gp_code);
save_sco("sco_msg.txt",  $gp_msg);
save_sco("sco_func.txt", $gp_func);

/*
 * iconv() invalid chars
 *   Ayumi     , 004.dat
 *   Funny Bee , 002.dat
 *   Rance 4.1 , 002.dat
 *   Rance 4.2 , 002.dat
 */
