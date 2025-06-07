#!/bin/bash
[ $(which convert ) ] || exit
[ $(which identify) ] || exit

while [ "$1" ]; do
	t1="$(realpath "$1")"
	shift

	[ -d "$t1" ] || continue
	BAK="$PWD"
	cd "$t1"

	echo "[$#] $t1"
	mkdir -p "$t1-64"

	for img in ./*.*; do
		mime=$(file  --brief  --mime-type  "$img" | grep 'image/')
		[ "$mime" ] || continue

		fmt=$(identify  -format "%k"  "$img")
		if (( $fmt > 99999 )); then
			# full color page , 6-bpp
			clr='RGB'
			bpp=63
		else
			# b&w or mono color page , 3-bpp
			clr='Gray'
			bpp=7
		fi

		#base="${img%.*}"
		nice -n 19  convert -quiet \
			"$img"            \
			-alpha off        \
			-interlace none   \
			+dither           \
			-resize 640x640\^ \
			-colorspace $clr  \
			-colors $bpp      \
			-strip            \
			-define png:include-chunk=none   \
			-define png:compression-filter=0 \
			-define png:compression-level=9  \
			"$t1-64"/"$img".png
		echo -n .
	done

	echo "done"
	cd "$BAK"
done

<<'////'
original  1058x1500  62,520,331
resized    903x1280  48,665,380 (77.8%)

http://www.imagemagick.org/script/command-line-processing.php#geometry
	widthxheight    Maximum values of width and height given, aspect ratio preserved.
	widthxheight\^  Minimum values of width and height given, aspect ratio preserved.
	widthxheight\!  Width and height emphatically given, original aspect ratio ignored.
	widthxheight\>  Shrinks  an image with dimension(s) larger  than the corresponding width and/or height argument(s).
	widthxheight\<  Enlarges an image with dimension(s) smaller than the corresponding width and/or height argument(s).

	image = 400x400
	300x200   -> 200x200
	300x200\^ -> 300x300
	300x200\! -> 300x200 , exact
	300x200\> -> 200x200 ,  larger=shrink
	300x200\< -> 400x400 , smaller=no change
////
