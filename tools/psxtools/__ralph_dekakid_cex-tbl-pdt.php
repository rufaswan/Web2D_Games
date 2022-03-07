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
require 'ralph.inc';

function ralph( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$tim = load_tbltim("$pfx.tb2", "$pfx/tim"); // rename st04bos2.tbl -> st04boss.tb2
	$cex = load_tbl   ("$pfx.tbl");
	$pdt = load_pdt   ("$pfx.pdt");
	if ( empty($tim) || empty($cex) || empty($pdt) )
		return;

	$sect = array(
		5  => 0x04,
		6  => 0x14,
		7  => 0x0c,
		8  => 0x0c,
		9  => 0x10,
		10 => 0x0c,
	);
	ralph_tbl_cex ($cex, "$pfx/cex", $sect);
	ralph_cex_cpdt($cex, $pdt, $tim[1], $pfx, $sect);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ralph( $argv[$i] );
