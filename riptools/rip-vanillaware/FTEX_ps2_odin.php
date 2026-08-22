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
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');
tool::require('func-console');

//define('DRY_RUN', true);

function odin_fgst( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	$fgsz = tool::ordstr($file, 4, 4);
	$hdsz = tool::ordstr($file, 8, 4);

	// SLUS 215.77 , sub_177370
	$ver = tool::ordstr($file, 0x10, 4); // lw
	if ( $ver < 0x66 )
		tool::error('Loaded data is not lower version');

	$dp = ord( $file[0x18] );
	$a0 = ord( $file[0x19] ); // ???
	if ( $dp == 32 || $dp == 24 || $dp == 16 )
		$v1 = 0;
	else
	if ( $dp == 8 || $dp == 4 )
		$v1 = $a0 >> 3;

	// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
	// TextureSwizzling.pdf
	//   256x128 texture
	//   - 8-bit = swizzle 128x64
	//   - 4-bit = swizzle 128x32
	//   texture size (in bytes) remain the same
	$ow = tool::ordstr ($file, 0x14, 2);
	$oh = tool::ordstr ($file, 0x16, 2);
	$zw = tool::ordstr ($file, 0x2e, 2);
	$zh = tool::ordstr ($file, 0x30, 2);
	$tm = tool::substr0($file, 0x44);
		$pos = $hdsz;
	printf("%8x , %x[%x]x%x[%x] , %d-bpp , %s\n", $pos, $ow, $zw, $oh, $zh, $dp, $tm);

	$swizzle = ( $zw != 0 || $zh != 0 );
	if ( $swizzle )
		tool::trace('SWIZZLED');

	$img = new clutdata;
	switch ( $dp )
	{
		case 32:
			$pix = tool::substr($file, $pos+0x400, $ow*$oh*4);
			ps2::alpha2x($pix);

			$img->w = $ow;
			$img->h = $oh;
			$img->pix = $pix;
			break;

		case 24:
			tool::warning('24-bpp', $pfx);
			return false;
		case 16:
			tool::warning('16-bpp', $pfx);
			return false;

		// if ( $zw*2 == $ow && $zh*2 == $oh )
		case 8:
			$pal = tool::substr($file, $pos, 0x400);
			$pix = tool::substr($file, $pos+0x400, $ow*$oh);
			//tool::save("$pfx.pal", $pal);
			//tool::save("$pfx.pix", $pix);

			ps2::pal($pal, $swizzle);
			if ( $swizzle )
				ps2::pix8($pix, $ow, $oh);

			$img->w = $ow;
			$img->h = $oh;
			$img->pal = $pal;
			$img->pix = $pix;
			break;

		// if ( $zw*2 == $ow && $zh*4 == $oh )
		case 4:
			$pal = tool::substr($file, $pos, 0x40);
			$pix = tool::substr($file, $pos+0x400, $ow/2*$oh);
			//tool::save("$pfx.pal", $pal);
			//tool::save("$pfx.pix", $pix);

			psx::bpp4to8($pix);
			//tool::save("$pfx.4bpp", $pix);

			if ( $swizzle )
				ps2::pix4($pix, $ow, $oh);
			//tool::save("$pfx.swz", $pix);

			$img->w = $ow;
			$img->h = $oh;
			$img->pal = $pal;
			$img->pix = $pix;
			break;

		default:
			tool::error('Unknown texture depth');
	} // switch ( $dp )

	clutfile::save("$fname.tm2", $img);
	return true;
}

function odin_ftex( string &$file, string $fname ) : bool
{
	tool::trace(__FUNCTION__, $fname);
	if ( substr($file, 0, 4) !== 'FTEX' )
		return false;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$cnt = tool::ordstr($file, 12, 4);

	$ed = strlen($file);
	$st = tool::ordstr($file, 8, 4);
	$id = 0;
	while ( $st < $ed )
	{
		$mgc = tool::substr($file, $st, 4);
		$fn  = sprintf('%s.%d', $pfx, $id);
		switch ( $mgc )
		{
			case 'FGST':
				$siz = tool::ordstr($file, $st + 4, 4);
				$sub = tool::substr($file, $st, $siz + 0x80);
				odin_fgst($sub, $fn);

				$st = tool::int_ceil($st+0x80+$siz, 0x10);
				$id++;
				break;
			case 'FEOC':
				return true;
			default:
				tool::error('UNKNOWN mgc', $st);
				return false;
		} // switch ( $mgc )
	} // while ( $st < $ed )
	return true;
}

function odin( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$r = odin_ftex($file, $fname);
	if ( $r )  return;

	// PS2 uses TIM2 , not FGST
	//$r = odin_fgst($file, $fname);
	//if ( $r )  return;
}

tool::argv_callback($argv, 'odin');

/*
4-bpp GrimGrimoire
	 80x40   [un] chara_effect_arranged000.tm2
	400x200  circle_arranged011.tm2
	200x200  circle_arranged021.tm2
	 80x40   [un] cursor_arranged000.tm2
	200x100  effect_arranged011.tm2
	100x80   effect_arranged021.tm2
	200x100  interface_arranged000.tm2
	100x80   opening3_arranged032.tm2
	200x200  font1
	200x200  font2

4-bpp Odin Sphere
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	100x100  on_memory_icon_arranged000.tm2
	100x200  axeknight_icon_arranged000.tm2
	 40x20   [un] axeknight_icon_arranged010.tm2
	 80x80   beehive_icon_arranged000.tm2
	 40x40   [un] beehive_icon_arranged010.tm2
	100x100  berserker_icon_arranged000.tm2
	 80x80   berserker_icon_arranged010.tm2
	 80x40   [un] berserker_icon_arranged020.tm2
	100x100  brigand_icon_arranged000.tm2
	100x100  brigand_icon_arranged010.tm2
	 80x80   brigand_icon_arranged020.tm2
	 40x40   [un] brigand_icon_arranged030.tm2
	100x100  bugbear_icon_arranged000.tm2
	 80x80   bugbear_icon_arranged010.tm2
	 80x80   bugbear_icon_arranged020.tm2
	200x100  darkover_icon_arranged000.tm2
	200x100  dragon_g_icon_arranged000.tm2
	100x100  dragon_g_icon_arranged010.tm2
	100x80   dragon_g_icon_arranged020.tm2
	 80x100  dwarf_icon_arranged000.tm2
	 80x40   [un] dwarf_icon_arranged010.tm2
	100x200  effect_icon_arranged000.tm2
	100x80   effect_icon_arranged010.tm2
	200x100  elfhunter_icon_arranged000.tm2
	200x100  elfknight_icon_arranged000.tm2
	 40x40   [un] filter_arranged000.tm2
	100x80   fin2_arranged070.tm2
	100x80   gargoyle_icon_arranged000.tm2
	200x100  geist_icon_arranged000.tm2
	100x100  ghouls_icon_arranged000.tm2
	 80x100  goblin_icon_arranged000.tm2
	 80x80   goblin_icon_arranged010.tm2
	200x80   griffon_icon_arranged000.tm2
	100x80   griffon_icon_arranged010.tm2
	 80x80   griffon_icon_arranged020.tm2
	 80x80   grizzly_icon_arranged000.tm2
	 40x40   [un] grizzly_icon_arranged010.tm2
	 40x20   [un] grizzly_icon_arranged020.tm2
	200x100  gwendlyn_icon_arranged000.tm2
	100x100  gwendlyn_icon_arranged010.tm2
	100x100  gwendlyn_icon_arranged020.tm2
	200x100  kitchin_arranged011.tm2
	100x100  mage_icon_arranged000.tm2
	100x100  manticora_icon_arranged000.tm2
	 40x40   [un] manticora_icon_arranged010.tm2
	 20x20   [un] mercedes_eneme_arranged011.tm2
	100x200  mercedes_eneme_icon_arranged000.tm2
	 20x20   [un] mercedes_arranged011.tm2
	100x200  mercedes_icon_arranged000.tm2
	 20x20   [un] npc_icon_arranged000.tm2
	200x200  odet_icon_arranged000.tm2
	100x100  on_memory_icon_arranged000.tm2
	100x200  onyx_icon_arranged000.tm2
	100x100  onyx_icon_arranged010.tm2
	100x80   onyx_icon_arranged020.tm2
	200x80   ordyne_icon_arranged000.tm2
	200x80   ordyne_icon_arranged010.tm2
	200x80   ordyne_icon_arranged020.tm2
	100x80   ordyne_icon_arranged030.tm2
	 40x40   [un] ordyne_icon_arranged040.tm2
	200x200  oswald_eneme_icon_arranged000.tm2
	100x100  oswald_eneme_icon_arranged010.tm2
	100x100  oswald_eneme_icon_arranged020.tm2
	200x200  oswald_icon_arranged000.tm2
	100x100  oswald_icon_arranged010.tm2
	100x100  oswald_icon_arranged020.tm2
	 80x80   penitente_icon_arranged000.tm2
	 80x80   penitente_icon_arranged010.tm2
	 40x40   [un] penitente_icon_arranged020.tm2
	200x100  pooka01_icon_arranged000.tm2
	100x100  pooka01_icon_arranged010.tm2
	100x80   pooka01_icon_arranged020.tm2
	 80x100  salamander_elder_icon_arranged000.tm2
	 40x40   [un] salamander_elder_icon_arranged010.tm2
	 80x100  salamander_icon_arranged000.tm2
	 40x40   [un] salamander_icon_arranged010.tm2
	100x100  sorsal_icon_arranged000.tm2
	100x100  troll_icon_arranged000.tm2
	 80x80   trolls_icon_arranged000.tm2
	 40x40   [un] trolls_icon_arranged010.tm2
	100x100  unicornknight_icon_arranged000.tm2
	 80x80   unicornknight_icon_arranged010.tm2
	 80x80   unicornknight_icon_arranged020.tm2
	200x100  valkyie_another_icon_arranged000.tm2
	100x100  valkyie_cheapedition_icon_arranged000.tm2
	100x100  valkyie_cheapedition_icon_arranged010.tm2
	 40x20   [un] valkyie_cheapedition_icon_arranged020.tm2
	100x100  valkyie_icon_arranged000.tm2
	100x100  valkyie_icon_arranged010.tm2
	 40x20   [un] valkyie_icon_arranged020.tm2
	 20x20   [un] velbet_eneme_arranged011.tm2
	200x200  velbet_eneme_icon_arranged000.tm2
	 20x20   [un] velbet_arranged011.tm2
	200x200  velbet_icon_arranged000.tm2
	 20x20   [un] vender_icon_arranged000.tm2
	 80x80   volcane_icon_arranged000.tm2
	200x100  vulcan00_icon_arranged000.tm2
	200x200  wagner_icon_arranged000.tm2
	100x100  warrior_icon_arranged000.tm2
	 80x80   warrior_icon_arranged010.tm2
	 80x80   warrior_icon_arranged020.tm2
	 80x80   wizerdeye_icon_arranged000.tm2
	200x200  wraith_icon_arranged000.tm2
	 40x20   [un] wraith_icon_arranged010.tm2
	100x100  font1
	400x400  font2
*/
