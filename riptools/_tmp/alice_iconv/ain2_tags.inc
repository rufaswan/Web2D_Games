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
require "ain2_tags_get.inc";

function ain2tags( &$file , &$st, $dir )
{
	global $gp_vers;
	printf("%8x : ", $st);
	$tag = substr($file, $st, 4);
	switch ( $tag )
	{
		// Popolytan.ain , System 4.0 SDK , AIN VERS 4
		case "VERS": // ain version
		case "KEYC": //
		case "GVER": // game version (100=ver1.00)
		case "OJMP": // function %d is onjump() , -1 = not used
		case "MAIN": // function %d is main()
		case "MSGF": //
			echo "$tag\n";
			$st += 4;
			$num = sint32($file, $st);
			$buf = "$num\n";
			if ( $tag == "VERS" )
				$gp_vers = $num;
			file_put_contents("$dir/$tag", $buf);
			return;

		case "MSG0": // strings
		case "STR0": // strings
		case "OBJG":
		case "ENUM": // enumerated
			echo "$tag\n";
			$st += 4;
			$cnt = sint32($file, $st);

			// zero(0) is not NULL !
			$buf = "";
			for ( $i=0; $i < $cnt; $i++ )
			{
				$str = utf8txt($file, $st);
				$buf .= "$str\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "FNAM": // EOF filenames
			echo "$tag\n";
			$st += 4;
			$cnt = sint32($file, $st);

			// zero(0) is not NULL !
			$buf = "";
			for ( $i=0; $i < $cnt; $i++ )
			{
				$str = utf8fnam($file, $st);
				$buf .= "$str\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "CODE": // 2-byte opcode instructions
			echo "$tag\n";
			$st += 4;
			$len = sint32($file, $st);

			$bak = $st;
			$code = substr($file, $st, $len);
			file_put_contents("$dir/$tag", $code);

			$st = $bak + $len;
			return;

		case "FUNC": // functions + class methods
			echo "$tag\n";
			$st += 4;
			$funcno = sint32($file, $st);

			$buf = "";
			for ( $fn=0; $fn < $funcno; $fn++ )
			{
				$varc = 0;
				$log  = "";
				if ( $gp_vers >= 11 ) // changed 11+
					$log .= get_FUNC_ain11($fn, $file, $st, $varc);
				else
				if ( $gp_vers >= 7 ) // changed 7+
					$log .= get_FUNC_ain7($fn, $file, $st, $varc);
				else
					$log .= get_FUNC_ain4($fn, $file, $st, $varc);

				for ( $vn=0; $vn < $varc; $vn++ )
				{
					if ( $gp_vers >= 14 ) // changed 14+
						$log .= get_FUNC_arg_ain14($file, $st);
					else
					if ( $gp_vers >= 8 ) // changed 8+
						$log .= get_FUNC_arg_ain8($file, $st);
					else
						$log .= get_FUNC_arg_ain4($file, $st);
				}
				$buf .= "$log\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "GLOB": // global var
			echo "$tag\n";
			$st += 4;
			$globno = sint32($file, $st);

			$buf = "";
			for ( $gn=0; $gn < $globno; $gn++ )
			{
				$varn = utf8txt($file, $st);
				$type = sint32($file, $st);
				$strt = sint32($file, $st);
				$arry = sint32($file, $st);

				$unk1 = "<???>";
				if ( $gp_vers >= 5 ) // added 5+
					$unk1 = sint32($file, $st);

				$buf .= "$gn,$varn,$type,$strt,$arry,$unk1\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "GSET": // init global var
			echo "$tag\n";
			$st += 4;
			$gsetno = sint32($file, $st);

			$buf = "";
			for ( $gn=0; $gn < $gsetno; $gn++ )
			{
				$id = sint32($file, $st);
				$ty = sint32($file, $st);

				if ( $ty == 0x0c ) // string
					$nm = utf8txt($file, $st);
				else
					$nm = sint32($file, $st);

				$buf .= "$id,$nm\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "STRT": // struct + class
			echo "$tag\n";
			$st += 4;
			$strtno = sint32($file, $st);

			$buf = "";
			for ( $sn=0; $sn < $strtno; $sn++ )
			{
				$strt = utf8txt($file, $st);
				$cons = sint32($file, $st);
				$dest = sint32($file, $st);
				$varn = sint32($file, $st);
				$buf .= "$sn,$strt,$cons,$dest";

				for ( $vn=0; $vn < $varn; $vn++ )
				{
					$var = utf8txt($file, $st);
					$vty = sint32($file, $st);
					$vst = sint32($file, $st);
					$vrr = sint32($file, $st);
					$buf .= ",$var,$vty,$vst,$vrr";
				}
				$buf .= "\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "HLL0": // DLL library functions
			echo "$tag\n";
			$st += 4;
			$hllno = sint32($file, $st);

			// SACT2.dll = 10
			// Log( $arg ) = 5
			// 10,5,SACT2,Log,argc
			$buf = "";
			for ( $hn=0; $hn < $hllno; $hn++ )
			{
				$hll = utf8txt($file, $st);

				$funcno = sint32($file, $st);
				for ( $fn=0; $fn < $funcno; $fn++ )
				{
					$func = utf8txt($file, $st);
					$type = sint32($file, $st);
					$buf .= "$hn,$fn,$hll,$func,$type";

					$parno = sint32($file, $st);
					$buf .= "$parno";
					for ( $pn=0; $pn < $parno; $pn++ )
					{
						$par = utf8txt($file, $st);
						$pty = sint32($file, $st);
						//$buf .= ",$par,$pty";
					}
					$buf .= "\n";
				}
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "SWI0": // switch cases
			echo "$tag\n";
			$st += 4;
			$swino = sint32($file, $st);

			$buf = "";
			for ( $sn=0; $sn < $swino; $sn++ )
			{
				$typ = sint32($file, $st);
				$def = sint32($file, $st);
				$vno = sint32($file, $st);
				$buf .= "$sn,$typ,$def";

				for ( $vn=0; $vn < $vno; $vn++ )
				{
					$case = sint32($file, $st);
					$addr = sint32($file, $st);
					$buf .= ",$case,$addr";
				}
				$buf .= "\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "FNCT": // functype
		case "DELG": // delegate
			echo "$tag\n";
			$st += 4;
			$bak = $st;
			$len = sint32($file, $st);
			$fnctno = sint32($file, $st);

			$buf = "";
			for ( $fn=0; $fn < $fnctno; $fn++ )
			{
				$varc = 0;
				$log  = "";
				if ( $gp_vers >= 11 ) // changed 11+
					$log .= get_DELG_ain11($fn, $file, $st, $varc);
				else
					$log .= get_DELG_ain4($fn, $file, $st, $varc);

				for ( $vn=0; $vn < $varc; $vn++ )
				{
					if ( $gp_vers >= 14 ) // changed 14+
						$log .= get_FUNC_arg_ain14($file, $st);
					else
					if ( $gp_vers >= 8 ) // changed 8+
						$log .= get_FUNC_arg_ain8($file, $st);
					else
						$log .= get_FUNC_arg_ain4($file, $st);
				}
				$buf .= "$log\n";
			}
			$st = $bak + $len;
			file_put_contents("$dir/$tag", $buf);
			return;

		// Sys42IDE.exe , System 4.0 SDK
		case "JDBG":
			return;
		case "JDG2":
			return;
		// changes:
		// KEYC  v6+ == 0 , v14+ removed
		// MSG0  v6+ removed
		// FNCT  v6+ removed
		// OJMP  v7+ removed
		// FNAM  v14+ removed
		// GSET  v14+ removed
		// MSGF  v14+ removed
		// OBJG  v5+
		// MSG1  v6+
		// DELG  v6+
		// ENUM  v14+
		case "MSG1":
			echo "$tag\n";
			$st += 4;
			$msgno = sint32($file, $st);
			$st += 8;
			if ( $gp_vers >= 7 ) // added 7+
				$st += 4;

			// NULL(0)
			$buf = "\n";
			for ( $mn=1; $mn < $msgno; $mn++ )
			{
				$len = sint32($file, $st);
				$msg = substr($file, $st, $len);
					$st += $len;

				$key = 0x60;
				for ( $i=0; $i < $len; $i++ )
				{
					$b1 = ord( $msg[$i] );
					$b1 = ($b1 - $key) & BIT8;
					$msg[$i] = chr($b1);

					$key = ($key + 1) & BIT8;
				}

				$msg = sjis2utf8($msg);
				$msg = str_esc($msg);
				$buf .= "$msg\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;
	}
	return;
}
