<?php
require('/opt/rfslib.php');

function pkmdexp( $fname )
{
	$fp = fopen( "{$fname}", "rb+" );
		if ( ! $fp )   return;

	$block = 0x58;
	$st = 0x379df;
	$ed = 0x40727;

	while ( $st < $ed )
	{
		$flg = rfs_fgetint ( $fp, $st, 1 );
		if ( $flg )
		{
			$lvl = rfs_fgetint ( $fp, $st+3, 1 );
			$nam = rfs_fgetstr0( $fp, $st+0x4c );
			$exp = rfs_fgetint ( $fp, $st+0x1c, 4 );
			$iq  = rfs_fgetint ( $fp, $st+0x14, 2 );
			$hld = rfs_fgetint ( $fp, $st+0x28, 1 );
			printf("%3d , %7d , %4d , %3d , {$nam}\n", $lvl, $exp, $iq, $hld);

			//$mhp = rfs_fgetint ( $fp, $st+0x16, 2 );
			//$atk = rfs_fgetint ( $fp, $st+0x18, 1 );
			//$def = rfs_fgetint ( $fp, $st+0x1a, 1 );
			//$spatk = rfs_fgetint ( $fp, $st+0x19, 1 );
			//$spdef = rfs_fgetint ( $fp, $st+0x1b, 1 );

			rfs_fputint( $fp, $st+0x14, 999, 2 ); // IQ max
			if ( $lvl < 100 )
				rfs_fputint( $fp, $st+0x1e, 0x70, 1 );

			if ( ! $hld )
				rfs_fputint( $fp, $st+0x28, 0xc8, 1 ); // hold escape orb

		}

		$st += $block;
	}

	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	pkmdexp( $argv[$i] );

/*
=== Item ID ===
01-08 pebble

09 mobile scarf
0a heal ribbon
0b twist band
0c scope lens
0d patsy band
0e no-stick cap
0f pierce band

10 joy ribbon
11 x-ray specs
12 persim band
13 power band
14 pecha scarf
15 insomniscope
16 warp scarf
17 tight belt
18 sneak scarf
19 gold ribbon
1a goggle specs
1b diet ribbon
1c trap scarf
1d racket band
1e def scarf
1f stamina band

20 plain ribbon
21 special band
22 zinc band
23 detect band
24 alert specs
25 dodge scarf
26 bounce band
27 curve band
28 whiff specs
29 no-aim scope
2a lockon specs
2b munch belt
2c pass scarf
2d weather band
2e friend bow

2f beauty scarf (feebas)
30 sun ribbon   (eevee)
31 lunar ribbon (eevee)

35 heal seed
36 wish stone
37 oran berry
38 sitrus berry

39-4c seeds

4d max elixir
4e protein
4f calcium
50 iron
51 zinc
52 apple
53 big apple
54 grimy food
55 huge apple

56-66 gummi

67 banana
68 chestnut
69 4 poke

6a upgrade        (porygon+)
6b king rock      (poliwhirl+, slowpoke+)
6c thunderstone   (eevee, pikachu)
6d deep sea scale (clamperl+)
6e deep sea tooth (clamperl+)
6f sun stone      (gloom, sunkern)
70 moon stone     (clefairy, jigglypuff, nidorina, nidorino, skitty)
71 fire stone     (eevee, growlithe, vulpix)
72 water stone    (eevee, lombre, poliwhirl, shellder, staryu)
73 metal coat     (onix+, scyther+)
74 leaf stone     (exeggcute, gloom, nuzleaf, weepinbell)
75 dragon scale   (seadra+)
76 link cable     (graveler, haunter, kadabra, machoke, clamperl+, poliwhirl+, porygon+, onix+, seadra+, scyther+, slowpoke+)

77 ice part
78 steel part
79 rock part
7a music box

7b key
7c used tm

7d-b1 tms
b2-df orbs
	c8 escape orb

e0 cut        (buried relic 80f)
e1 fly        (wynern hill 30f)
e2 surf       (solar cave 20f key)
e3 strength   (buried relic 60f)
e4 flash      (buried relic 70f)
e5 rock smash (buried relic 45f)
e6 waterfall  (solar cave 15f)
e7 dive       (solar cave 10f key)

e8 link box
e9 switch box
ea weavile fig
eb mime jr fig
*/
