#!/bin/bash
[ $# = 0 ] && exit
[ $(which ffmpeg) ] || exit

while [ "$1" ]; do
	t1="${1%/}"
	dir="${t1%/*}"
	bas="${t1##*/}"
	tit="${bas%.*}"
	ext="${bas##*.}"
	shift

	png="$dir/$tit"
	[ -d "$png" ] || continue

	# input png @ 60 FPS , output animation @ 10 FPS
	case "$ext" in
		'gif')
			ffmpeg -y                     \
				-r 60  -i "$png"/%06d.png \
				-r 10  "$png".gif;;
		'apng')
			ffmpeg -y                     \
				-r 60  -i "$png"/%06d.png \
				-f apng -plays 0 -r 10  "$png".apng;;
		'webp')
			ffmpeg -y                     \
				-r 60  -i "$png"/%06d.png \
				-vcodec libwebp_anim -lossless 1 -loop 0 -r 10  "$png".webp;;
	esac
done

