#!/bin/bash
<<'////'
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
////
clut2bmp="/tmp/clut2bmp.php"
root="$PWD"

function clutphp
{
	case "$1" in
		'#')
			return;;
		'G0')
			low=$2
			let up=$low/256
			fn=$(printf "$root/%03d/%05d.clut" $up $low)
			[ -f "$fn" ] || return
			php.sh $clut2bmp "$fn"
			rm "$fn"
			;;
		'G1')
			low=$2
			let up=$low/256
			fn=$(printf "$root/%03d/%05d.clut" $up $low)
			[ -f "$fn" ] || return
			php.sh $clut2bmp "$fn" "$3"
			rm "$fn"
			;;
	esac
}

{ cat "g01.txt"; echo; } | while read -r i; do
	[ "$i" ] && echo "$i" || continue
	clutphp $i
done
mogrify -format png -strip */*.bmp
rm */*.bmp
rename .clut.  .  */*.png
