#!/bin/bash
<<'////'
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
////

php='/tmp/quad_vanillaware_FMBP_FMBS.php'
[ -f "$php" ] || exit
for sys in ps2 ps3 ps4 psp vit nds wii swi; do
	for game in grim odin kuma mura gran drag sent; do
		echo "== $sys  $game"
		php.sh  "$php"  "${sys}_${game}"  "${sys} ${game}"*.mbs
		php.sh  "$php"  "${sys}_${game}"  "${sys} ${game}"*.mbp
	done
done
