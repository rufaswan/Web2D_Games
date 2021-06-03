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

php=""
png=''
if [ -f "/tmp/clut2png.php" ]; then
	php="/tmp/clut2png.php"
	png=1
elif [ -f "/tmp/clut2bmp.php" ]; then
	php="/tmp/clut2bmp.php"
	png=''
fi
[ "$php" ] || exit
export php png

function clut2png
{
	[ -f "$1" ] || continue
	php.sh  "$php"  "$1"

	if [ ! "$png" ]; then
		convert "$1.bmp" \
			-define png:include-chunk=none,trns \
			-define png:compression-filter=0 \
			-define png:compression-level=9 \
			-strip \
			"$1".png &> /dev/null
		rm "$1".bmp
	fi

	rm "$1"
}
export -f clut2png

find . -iname "*.clut"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.rgba"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.clut.*" | xargs  rename .clut.  .
find . -iname "*.rgba.*" | xargs  rename .rgba.  .

<<'////'
https://imagemagick.org/script/defines.php
-define
	png:format=[png8 png24 png32 png48 png64 png00]

	png:exclude-chunk
	png:include-chunk
		bKGD,cHRM,gAMA,iCCP,oFFs,pHYs,sRGB,tEXt,tRNS,vpAg,zTXt
		any other are ignored
		-strip
			EXIF,iCCP,iTXt,sRGB,tEXt,zCCP,zTXt,date

	png:compression-filter=[0 1 2 3 4 5 6 7 8 9]
		0-4 = none sub up average paeth
	png:compression-level=[0 1 2 3 4 5 6 7 8 9]
	png:compression-strategy=[0 1 2 3 4]
////
