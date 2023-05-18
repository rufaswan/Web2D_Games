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
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';
require 'quad_mana.inc';

function mana( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$timoff = str2int($file, 0, 2);
	$datoff = str2int($file, 4, 2);

	// tim starts at 2
	$t = psxtim($file, $timoff);
	$tim  = array($t , $t , $t);
	$meta = substr($file, $datoff);

	sectmeta($meta, $tim, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
more than 1 parts
  antz2.stv
  govz2.stv
  govz3.stv
  vv442.stv
  vv451.stv
  vv471.stv
  vv472.stv
  vv512.stv
  vv531.stv
  vv532.stv
  vv541.stv
  vv544.stv
  vv672.stv
  s3rlv1.stv
  s3ulv1.stv
  vv84.stv
  vv85.stv
  tanken18.stv
  tanke18n.stv
  hammer17.stv
  yari16.stv
  yari17.stv
 */
