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

php="/tmp/clut2png.php"
[ -f "$php" ] || exit
export php

[ $# = 0 ] && f0="-V" || f0="-r"
export f0

function diricon
{
	[ -d "$1" ] || return
	cd "$1"
	[ -f '.DirIcon' ] && rm -vf '.DirIcon'

	tmp="/tmp/icon.png"
	[ -f "$tmp" ] && rm -vf "$tmp"

	for t1 in $(ls -1 * | sort $f0); do
		case "$t1" in
			*'.rgba' | *'.clut')
				php.sh  "$php"  "$t1"
				convert  "$t1.png" \
					-define png:include-chunk=none,trns \
					-define png:compression-filter=0 \
					-define png:compression-level=9 \
					-trim -strip \
					"$tmp" &> /dev/null
				mv -f  "$tmp"  '.DirIcon'
				rm "$t1.png"
				return;;

			*'.bmp'  | *'.png' )
				convert  "$t1" \
					-define png:include-chunk=none,trns \
					-define png:compression-filter=0 \
					-define png:compression-level=9 \
					-trim -strip \
					"$tmp" &> /dev/null
				mv -f  "$tmp"  '.DirIcon'
				return;;
		esac
	done
}
export -f diricon

find "$PWD" -type d | xargs -I {} bash -c 'diricon "$@"' _ {}
