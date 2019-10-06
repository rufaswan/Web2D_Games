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
function ain2tags( &$file , &$st, $dir )
{
	global $gp_pc;
	printf("%8x : ", $st);
	$tag = substr($file, $st, 4);
	switch ( $tag )
	{
		// Popolytan.ain , System 4.0 SDK , AIN VERS 4
		case "VERS": // 0x00
		case "KEYC": // 0x08
		case "GVER": // 0x3c36d
		case "OJMP": // 0x3dd98
		case "MAIN": // 0x3dec5
		case "MSGF": // 0x37df6
			$st += 4;
			$num = sint32($file, $st);
			printf("$tag %8x\n", $num);
			$gp_pc[$tag] = $num;
			file_put_contents("$dir/$tag", $num);
			return;

		case "MSG0": // 0x37c94
		case "STR0": // 0x3c375
		case "FNAM": // 0x3d66b
			$st += 4;
			$cnt = str2int($file, $st, 4);
			printf("$tag %8x\n", $cnt);

			$buf = "";
			for ( $i=0; $i < $cnt; $i++ )
			{
				$str = get_text($file, $st);
				$gp_pc[$tag][$i] = $str;
				$buf .= "$i,$str\n";
			}

			//print_r( $gp_pc[$tag] );
			file_put_contents("$dir/$tag", $buf);
			return;

		case "CODE": // 0x10
			$st += 4;
			$code = str2int($file, $st, 4);
			$bak = $st;
			$st += $code;
			printf("CODE %8x\n", $code);
			$gp_pc["CODE"] = $code;
			file_put_contents("$dir/$tag", substr($file, $bak, $code));
			return;

		case "FUNC": // 0x219d2
			$st += 4;
			$cnt = str2int($file, $st, 4);
			printf("FUNC %8x\n", $cnt);

			for ( $i=0; $i < $cnt; $i++ )
			{
				$data1 = array(
					"st"     => $st,
					"addr"   => str2int($file, $st, 4) - 6,
					"name"   => get_text($file, $st),
					"label"  => sint32($file, $st),
					"dtype"  => get_datatype($file, $st),
					"struct" => sint32($file, $st),
					"arg_c"  => sint32($file, $st),
					"var_c"  => sint32($file, $st),
					"crc"    => str2int($file, $st, 4),
				);
				$data2 = array();
				for ( $p=0; $p < $data1["var_c"]; $p++ )
				{
					$data2[$p] = array(
						'n' => get_text($file, $st),
						'd' => get_datatype($file, $st),
						't' => sint32($file, $st),
						'a' => sint32($file, $st),
					);
				}
				$data1["para"] = $data2;

				$gp_pc["FUNC"][$i] = $data1;
			} // for ( $i=0; $i < $cnt; $i++ )

			//print_r( $gp_pc["FUNC"] );
			$buf = "";
			foreach( $gp_pc["FUNC"] as $k => $f )
			{
				$t1 = ( $f["struct"] < 0 ) ? "{$f['dtype']}" : "{$f['dtype']}_{$f['struct']}";
				$buf .= sprintf("%4x , %8x , %s %s , %d , %d", $k, $f["addr"], $t1, $f["name"], $f["arg_c"], $f["var_c"]);
				foreach( $f["para"] as $p )
				{
					if ( $p['t'] < 0 )
						$buf .= " , {$p['d']} {$p['n']}";
					else
						$buf .= " , {$p['d']}_{$p['t']} {$p['n']}";
				}
				$buf .= "\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "GLOB": // 0x359e6
			$st += 4;
			$cnt = str2int($file, $st, 4);
			printf("GLOB %8x\n", $cnt);

			for ( $i=0; $i < $cnt; $i++ )
			{
				$data = array(
					'n' => get_text($file, $st),
					'd' => get_datatype($file, $st),
					't' => sint32($file, $st),
					'a' => sint32($file, $st),
				);
				$gp_pc["GLOB"][$i] = $data;
			} // for ( $i=0; $i < $cnt; $i++ )

			//print_r( $gp_pc["GLOB"] );
			$buf = "";
			foreach( $gp_pc["GLOB"] as $k => $g )
			{
				$dim = ( $g['a'] > 1 ) ? "@{$g['a']}": "";

				$buf .= sprintf("%4x , ", $k);

				if ( $g['t'] < 0 )
					$buf .= sprintf("%s%s", $g['d'], $dim);
				else
					$buf .= sprintf("%s_%s%s", $g['d'], $g['t'], $dim);

				$buf .= "  {$g['n']}\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "GSET": // 0x35f31
			$st += 4;
			$cnt = str2int($file, $st, 4);
			printf("GSET $cnt\n");

			for ( $i=0; $i < $cnt; $i++ )
			{
				$data = array("st" => $st);

				$id = sint32($file, $st);
				$data["dtype"] = get_datatype($file, $st);

				if ( $data["dtype"] == "string" )
					$data["data"] = get_text($file, $st);
				else
					$data["data"] = sint32($file, $st, 4);

				$gp_pc["GSET"][$id] = $data;
			} // for ( $i=0; $i < $cnt; $i++ )

			//print_r( $gp_pc["GSET"] );
			$buf = "";
			foreach( $gp_pc["GSET"] as $id => $g )
				$buf .= sprintf("%d,%s\n", $id, $g['data']);
			file_put_contents("$dir/$tag", $buf);
			return;

		case "STRT": // 0x36050
			$st += 4;
			$cnt = str2int($file, $st, 4);
			printf("STRT $cnt\n");

			for ( $i=0; $i < $cnt; $i++ )
			{
				$data1 = array(
					'n' => get_text($file, $st),
					'd' => sint32($file, $st),
					't' => sint32($file, $st),
					'a' => sint32($file, $st),
				);
				$data2 = array();
				for ( $j=0; $j < $data1['a']; $j++ )
				{
					$data2[$j] = array(
						'n' => get_text($file, $st),
						'd' => get_datatype($file, $st),
						't' => sint32($file, $st),
						'a' => sint32($file, $st),
					);
				}
				$data1['p'] = $data2;

				$gp_pc["STRT"][$i] = $data1;
			} // for ( $i=0; $i < $cnt; $i++ )

			//print_r( $gp_pc["STRT"] );
			$buf = "";
			foreach( $gp_pc["STRT"] as $k => $s )
			{
				$buf .= "struct {$s['n']}\n";

				foreach( $s['p'] as $p )
				{
					if ( $p['t'] < 0 )
						$buf .= "  {$p['d']} {$p['n']}\n";
					else
						$buf .= "  {$p['d']}_{$p['t']} {$p['n']}\n";
				}

				if ( $s['d'] > 0 )  $buf .= sprintf("  @0  func_%x\n", $s['d']);
				if ( $s['t'] > 0 )  $buf .= sprintf("  @1 ~func_%x\n", $s['t']);
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "HLL0": // 0x37dfe
			$st += 4;
			$hll = str2int($file, $st, 4);
			printf("HLL0 $hll\n");

			for ( $h=0; $h < $hll; $h++ )
			{
				$dll = get_text($file, $st);
				$cnt = str2int($file, $st, 4);
				$gp_pc["HLL0"][$h]["hll"] = $dll;

				for ( $i=0; $i < $cnt; $i++ )
				{
					$data1 = array(
						'n' => get_text($file, $st),
						'd' => get_datatype($file, $st, 4),
						'a' => str2int($file, $st, 4),
					);
					$data2 = array();
					for ( $a=0; $a < $data1['a']; $a++ )
					{
						$data2[] = array(
							'n' => get_text($file, $st),
							'd' => get_datatype($file, $st, 4),
						);
					}
					$data1['p'] = $data2;
					$gp_pc["HLL0"][$h][$i] = $data1;
				}
			}

			//print_r( $gp_pc["HLL0"] );
			$buf = "";
			foreach( $gp_pc["HLL0"] as $k1 => $v1 )
			{
				foreach( $v1 as $k2 => $v2 )
				{
					if ( $k2 === "hll" )
						continue;

					$buf .= sprintf("%4x , %4x , ", $k1, $k2);
					$buf .= sprintf("%s %s@@%s , %d", $v2['d'], $v1["hll"], $v2['n'], $v2['a']);
					foreach ( $v2['p'] as $p )
					{
						$buf .= " , {$p['d']} {$p['n']}";
					}
					$buf .= "\n";
				}
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "SWI0": // 0x3bfd9
			$st += 4;
			$swi = str2int($file, $st, 4);
			printf("SWI0 $swi\n");

			for ( $s=0; $s < $swi; $s++ )
			{
				$ty = str2int($file, $st, 4);
				$c0 = sint32 ($file, $st, 4);
				$no = str2int($file, $st, 4);

				$gp_pc["SWI0"][$s]['t'] = $ty;
				$gp_pc["SWI0"][$s][-1]  = $c0;
				for ( $n=0; $n < $no; $n++ )
				{
					$v0 = str2int($file, $st, 4);
					$v1 = str2int($file, $st, 4);
					$gp_pc["SWI0"][$s][$v0] = $v1;
				}
			}

			//print_r( $gp_pc["SWI0"] );
			$buf = "";
			foreach( $gp_pc["SWI0"] as $s )
			{
				$type = "";
				if ( $s['t'] == 2 )  $type = "int";
				if ( $s['t'] == 4 )  $type = "string";
				$buf .= "switch ($type)\n";

				if ( $s[-1] > 0 )
					$buf .= sprintf("  default: 0x%x\n", $s[-1]);

				foreach ( $s as $k => $v )
				{
					if ( $k === -1  )  continue;
					if ( $k === 't' )  continue;
					$buf .= sprintf("  case %d: 0x%x\n", $k, $v);
				}
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		case "FNCT": // 0x3dda0
			$st += 4;
			$bak = $st;
			$len = str2int($file, $st, 4);
			$cnt = str2int($file, $st, 4);
			printf("FNCT $len , $cnt\n");

			for ( $f=0; $f < $cnt; $f++ )
			{
				$data = array(
					"name"   => get_text($file, $st),
					"dtype"  => get_datatype($file, $st),
					"struct" => sint32($file, $st),
					"arg_c"  => sint32($file, $st),
					"var_c"  => sint32($file, $st),
				);
				$data["para"] = array();
				for ( $c=0; $c < $data["var_c"]; $c++ )
				{
					$data["para"][$c] = array(
						'n' => get_text($file, $st),
						'd' => get_datatype($file, $st),
						't' => sint32($file, $st),
						'a' => sint32($file, $st),
					);
				}
				$gp_pc["FNCT"][$f] = $data;
			}

			$st = $bak + $len;
			//print_r( $gp_pc["FNCT"] );
			$buf = "";
			foreach( $gp_pc["FNCT"] as $k => $f )
			{
				$t1 = ( $f['struct'] < 0 ) ? "{$f['dtype']}" : "{$f['dtype']}_{$f['struct']}";
				$buf .= "$t1 {$f['name']}(";
				$t1 = "";
				foreach( $f['para'] as $p )
				{
					if ( $t1 != "" )
						$t1 .= ',';

					if ( $p['t'] < 0 )
						$t1 .= " {$p['d']} {$p['n']} ";
					else
						$t1 .= " {$p['d']}_{$p['t']} {$p['n']} ";
				}
				$buf .= "$t1)\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;

		// Sys42IDE.exe , System 4.0 SDK
		case "JDBG":
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
		case "OBJG":
			return;
		case "MSG1":
			return;
		case "DELG":
			return;
		case "ENUM":
			return;
	}
	return;
}
