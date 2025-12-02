#!/bin/bash
[ $(which convert ) ] || exit
[ $(which identify) ] || exit

function png64 {
	# png64 6 in.png out.png for dir/file
	# png64 8 in.png out.png for file
	opt=''
	(( $1 == 6 )) && opt='-colors  63  -alpha off'
	(( $1 == 8 )) && opt='-colors 255'

		#-colorspace RGB
	nice -n 19  convert -quiet \
		"$2"              \
		-interlace none   \
		+dither           \
		-resize 640x640\^ \
		$opt              \
		-strip            \
		-define png:include-chunk=none   \
		-define png:compression-filter=0 \
		-define png:compression-level=9  \
		"$3"
}

while [ "$1" ]; do
	t1="$(realpath "$1")"
	shift

	if [ -f "$t1" ]; then
		png64  8  "$t1"  "$t1".png
	fi
	if [ -d "$t1" ]; then
		BAK="$PWD"
		cd "$t1"

		echo "[$#] $t1"
		mkdir -p "$t1-64"

		for img in ./*.*; do
			mime=$(file  --brief  --mime-type  "$img" | grep 'image/')
			[ "$mime" ] || continue

			png64  6  "$img"  "$t1-64"/"$img".png
			echo -n .
		done

		echo "done"
		cd "$BAK"
	fi
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
