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

echo "${0##*/}  THUMB_ID";
[ $# = 0 ] && exit

function thumbnail()
{
	# game  icon = 240x125
	# sheet icon = 148x125 = 74x62 * 200%
	sc='-scale 200%'
	(( $1 > 74 )) && sc=''
	(( $2 > 62 )) && sc=''
	mogrify -verbose                        \
		$sc                                 \
		-define png:include-chunk=none,trns \
		-strip                              \
		-background transparent             \
		-gravity center                     \
		-extent 148x125                     \
		thumb.png
}

if [ "$(ls -1 *.clut | tail)" ]; then
	rename  .clut  .rgba  *.clut  *.clut.png
fi

png=$(printf "%04d.rgba.png"  $1)
[ -f "$png" ] || exit
convert -verbose  "$png"  -trim -strip  thumb.png
thumbnail $(identify -format "%w %h %i"  thumb.png)

montage -verbose -strip \
	-tile 10x20         \
	-geometry '1x1<'    \
	-background  none   \
	-bordercolor none   \
	-gravity center     \
	[0123456789]*.rgba.png  sheet.png

<<'////'
#mogrify -verbose -strip -trim +repage  0*.png
	-geometry '1x1+3+3<' \
	-frame 2 \
# imagemagick.org/script/command-line-processing.php
s%       scale wxh by s percent
sw%xsh%  scale w by sw percent and h by sh percent
w        auto h
xh       auto w
wxh      max auto wxh , 640x480 => 100x200  = 100x75
wxh^     min auto wxh , 640x480 => 100x200^ = 267x200
wxh!     exact wxh
wxh>     shrink  if larger  than wxh , 640x480 => 100x200> = 100x75
wxh<     enlarge if smaller than wxh , 640x480 => 100x200< = 640x480

# imagemagick.org/script/command-line-options.php?#scale
'-scale' == '-resize' with '-filter box'
////
