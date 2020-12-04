#!/bin/bash
<<'////'
[license]
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
