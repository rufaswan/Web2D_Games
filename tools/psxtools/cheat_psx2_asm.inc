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
$gp_cheat = array(
	'SLPS_257.71' => 'ps2_jp_grim',
);

function ps2_jp_grim( $isop, &$list )
{
	$slps = isosearch($list, '/slps_257.71');
	$pos  = $slps['lba'] * 0x800 + 0x138330; // RAM 2382b0
	$siz  = 0x48 * 0x28;
	$fnm  = sprintf('%s.%x.bak', __FUNCTION__, $pos);

	$sub = baksub($fnm, $isop, $pos, $siz, '2aa2c21a4087739f17dbd2d1d880507cf120fd12');
	for ( $i=0; $i < $siz; $i += 0x48 )
	{
		lhu_update($sub, $i+0x14, 1); // lhu size
		lhu_update($sub, $i+0x1a, 0); // lhu crystal cost
		lhu_update($sub, $i+0x1e, 1); // lhu time
		lhu_update($sub, $i+0x20, 1); // lhu hp
	} // for ( $i=0; $i < $siz; $i += 0x48 )

	fp_update($isop, $pos, $sub);
	return;
}

/*
*/
