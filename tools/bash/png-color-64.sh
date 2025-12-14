#!/bin/bash
[ $(which convert ) ] || exit
[ $(which identify) ] || exit

function png64 {
	mime=$(file  --brief  --mime-type  "$2" | grep 'image/')
	[ "$mime" ] || return

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
			png64  6  "$img"  "$t1-64"/"$img".png
			echo -n .
		done

		echo "done"
		cd "$BAK"
	fi
done
