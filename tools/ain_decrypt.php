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
//////////////////////////////
define("BIT8" , 0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

// from System 4.0 SDK/Popolytan/DLL/Sys42VM.dll
// sub_10001260
class gal_key
{
	public  $len;
	public  $key;
	private $seed;

	// loc_100012da
	public function xorkey( $k )
	{
		// eax = $k
		// ecx = $key
		$key = $this->key[$k];

		$tmp = $key >> 11;
		$key ^= $tmp;

		$tmp = $key & 0xff3a58ad;
		$key ^= ($tmp << 7);

		$tmp = $key & 0xffffdf8c;
		$key ^= ($tmp << 15);

		$tmp = $key >> 18;
		$key ^= $tmp;

		return ($key & BIT8);
	}

	// sub_10035260
	public function update()
	{
		$i  = 0;
		$n  = 1;
		$nn = 2;
		$ah = 0x18d;
		$kc = $this->key[1];
		$kl = $this->key[0];

		while ( $i < $this->len )
		{
			// eax = $kc
			// ecx = $kl
			$tmp  = $kc ^ $kl;
			$tmp &= 0x7ffffffe;
			$tmp ^= $kl;
			$tmp >>= 1;

			// CF = Carry Flag
			// 1003529e and cl, 1  // cl &= 1
			// 100352a3 neg cl     // CF = (cl == 0) ? 0 : 1;
			// 100352a5 sbb ecx, ecx // ecx = (CF == 0) ? 0 : -1;
			// 100352e8 and ecx, 0x9908b0df // ecx = (ecx == 0) ? 0 : 0x9908b0df;
			$kk = ($kc & 1) ? 0x9908b0df : 0;
			$tmp ^= $kk;
			$tmp ^= $this->key[$ah];

			$this->key[$i] = $tmp & BIT32;
			$kl = $kc;
			$kc = $this->key[$nn];
			$i++;
			$nn = ($nn + 1) % $this->len;
			$ah = ($ah + 1) % $this->len;
		}

	}

	// loc_100092a1
	public function init()
	{
		$this->seed = 0x5d3e3;
		$this->len  = 0x270;
		for ( $i=0; $i < $this->len; $i++ )
		{
			$this->key[$i] = $this->seed;
			$this->seed = ($this->seed * 0x10dcd) & BIT32;
		}
	}

	public function debug( $note )
	{
		echo "DEBUG : $note\n";
		for ( $i=0; $i < $this->len; $i++ )
			printf("  %4x = %8x\n", $i, $this->key[$i] );
	}
}
//////////////////////////////
function ain42( $rem, $fn )
{
	//$fn  = "Galzoo.ain";
	//$fn  = "Popolytan.ain";
	$ain = file_get_contents($fn);
	echo "[$rem] $fn\n";

	$gal = new gal_key();
	$gal->init();
	//$gal->debug("init()");

	$ed = strlen($ain);
	for ( $i=0; $i < $ed; $i++ )
	{
		$k = ($i % $gal->len);
		if ( $k == 0 )
		{
			//printf("%x => %x update()\n", $i, $k);
			$gal->update();
			//$gal->debug("update()");
		}

		$c = ord( $ain[$i] );
		$c = ($c ^ $gal->xorkey($k)) & BIT8;;
		$ain[$i] = chr($c);
	}

	file_put_contents("$fn.dec", $ain);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	ain42( $argc-$i, $argv[$i] );
