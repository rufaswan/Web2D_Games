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
require 'common.inc';
require 'class-pixlines.inc';

function pjoint( &$pix, $pos )
{
	if ( strpos($pos, ',') === false )
		return;

	$pos = explode(',', $pos);
	switch ( count($pos) )
	{
		case 2: // x,y
			return $pix->addpoint($pos, "\x0e");
		case 4: // x1,y1,x2,y2
			return $pix->addline ($pos, "\x0e");
		case 8: // x1,y1,x2,y2,x3,y3,x4,y4
			return $pix->addquad ($pos, "\x0e");
	} // switch ( count($pos) )
	return;
}

$pix = new PixLines;

$pix->new();
for ( $i=1; $i < $argc; $i++ )
	pjoint( $pix, $argv[$i] );

$img = $pix->draw();
save_clutfile('pixlines.clut', $img);

/*
116,-109,77,-41,86,-71,51,-136
70,-49,254,-33,101,-116,95,-57
*/
