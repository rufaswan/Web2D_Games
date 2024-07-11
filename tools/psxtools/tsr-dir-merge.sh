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

echo "${0##*/}  DEST_DIR  [-mv/-cp]  SRC_DIR...";
(( $# < 2 )) && exit

dest="$1"
mkdir -p "$dest"
shift

id=0
move=''
while [ "$1" ]; do
	t1="$1"
	shift

	case "$t1" in
		'-mv')  move=1;  continue;;
		'-cp')  move=''; continue;;
	esac

	[ -d "$t1" ] || continue
	#[ -f "$t1/0000.rgba" ] || continue
	for f in "$t1"/0*.rgba; do
		fn=$(printf  "%s/%04d.rgba"  "$dest"  $id)
		if [ "$move" ]; then
			mv -vf  "$f"  "$fn"
		else
			cp -vf  "$f"  "$fn"
		fi
		let id++
	done
done
