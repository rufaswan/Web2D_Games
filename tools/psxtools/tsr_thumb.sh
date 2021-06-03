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

echo "${0##*/}  PNG_FILE";
[ $# = 0 ] && exit

function thumbnail()
{
	# game  icon = 240x125
	# sheet icon = 148x125 = 74x62 * 200%
	sc='-scale 200%'
	(( $1 > 74 )) && sc=''
	(( $2 > 62 )) && sc=''
	mogrify -verbose \
		$sc \
		-define png:include-chunk=none,trns -strip \
		-background transparent \
		-gravity center \
		-extent 148x125 \
		thumb.png
}

png=$1
[ -f "$png" ] || exit
convert -verbose  "$png"  -trim -strip  thumb.png
thumbnail $(identify -format "%w %h %i"  thumb.png)
