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

function copyto( array &$option ) : void
{
	$list = [];
	foreach ( $option['srcdir'] as $src )
		tool::scan($list, $src);
	usort($list, 'version_compare');

	if ( ! $option['move'] )
		$func = ['tool', 'copy'];
	else
		$func = ['tool', 'move'];

	foreach ( $list as $ent )
		$func($ent, $option['dstdir']);
}

//tool::usage($argv, 'copyto');
/**
 * @desc To copy content of a folder, in natural filename order.
 *       By default, computer will copy files in filesystem order.
 *       This completely messed up the file order on USB stick or SD card.
 *
 * @option  BOOL=false  mv move $move
 *
 * @arg  DIR[]  $srcdir
 * @arg  DIR    $dstdir
 */
