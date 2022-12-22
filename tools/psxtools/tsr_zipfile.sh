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
[ $# = 0 ] && exit

ext='zip'
while [ "$1" ]; do
	t1="${1%/}"
	tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	loc="$PWD"

	# unpack zip file -> dir
	if [ -f "$t1" ]; then
		[ -d "$tit" ] && rm -vfr "$tit"
		unzip  -o  "$t1" -d "$tit"
	fi

	# pack dir -> zip file
	if [ -d "$t1" ]; then
		fn="$t1.$ext"
		[ -f "$fn" ] && rm -vf "$fn"

		zip  -j -0  "$fn"  "$t1"/*  -x '*.bak'
	fi

	case "$t1" in
		'-e')  ext="$1"; shift;;
	esac

done

<<'////'
.p2s  pcsx2 save state

////
