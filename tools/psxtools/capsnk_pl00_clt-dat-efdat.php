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
/*
 * prefixes
 *  pl00  ryu
 *  pl01  ken
 *  pl02  chun-li
 *  pl03  guile
 *  pl04  zangief
 *  pl05  dhalsim
 *  pl06  e.honda
 *  pl07  blanka
 *  pl08  balrog (claw)
 *  pl09  sagat
 *  pl0a  vega (dictator)
 *  pl0b  sakura
 *  pl0c  cammy
 *  pl0d  *unlock* gouki
 *  pl0e  *unlock* morrigan
 *  pl0f  *unlock* evil ryu
 *  pl10  *+ex* kyo
 *  pl11  iori
 *  pl12  terry
 *  pl13  ryo
 *  pl14  *+ex* mai
 *  pl15  kim
 *  pl16  geese
 *  pl17  *+ex* yamazaki
 *  pl18  raiden
 *  pl19  *+ex* rugal
 *  pl1a  *+ex* vice
 *  pl1b  benimaru
 *  pl1c  yuri
 *  pl1d  king
 *  pl1e  *unlock* nakoruru
 *  pl1f  *unlock* orochi iori
 *  pl20  m.bison (boxer)
 *  pl21  dan
 *  pl22  joe
 */
require 'common.inc';
require 'capsnk.inc';

function capsnk( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$file = capsnk_load($pfx);
	if ( $file === -1 )
		return;

	for ( $i=0; $i < 6; $i++ )
	{
		$fn = sprintf('%s/meta.%d', $pfx, $i);
		save_file($fn, $file['dat'][$i]);
	}

	$cnt = count($file['efdat']);
	for ( $i=1; $i < $cnt; $i++ )
	{
		$fn = sprintf('%s/efdat-tim.%d.clut', $pfx, $i);
		save_clutfile($fn, $file['efdat'][$i]);
	}

	$spr = capsnk_sprite($file['dat'][0], $file['dat'][1], $file['efdat'][0], $file['clt']);
	foreach ( $spr as $sk => $sv )
	{
		$fn = sprintf('%s/sprgfx/%04d.rgba', $pfx, $sk);
		save_clutfile($fn, $sv);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	capsnk( $argv[$i] );

/*
mai idle = spr d8 , 5fe4
	55 06 -- d0  87 cd 87 cc  87 e0 af 87  7f ff f2 d0

	-1-1 -1-1
		e77d8  00 * 6
		e77df  d0
		c77e0  00 * 6
		c77e7  cd
		c77e8  00 * 6
		c77ef  cc
		c77f0  00 * 6
		c77f7  e0
	1-1- 1111
		c77f8  00 * 6
		c77ff  00 * e
		c780e  00 * e
		c781d  00 * 1
		c781f  d0
 */
