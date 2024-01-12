#!/bin/bash
[ $# = 0 ] && exit
[ $(which ffmpeg) ] || exit

while [ "$1" ]; do
	t1="${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	[ -d "$tit" ] || continue

	# input png @ 60 FPS , output animation @ 10 FPS
	case "$ext" in
		'gif')
			ffmpeg -y                     \
				-r 60  -i "$tit"/%06d.png \
				-r 10  "$tit".gif;;
		'apng')
			ffmpeg -y                     \
				-r 60  -i "$tit"/%06d.png \
				-f apng -plays 0 -r 10  "$tit".apng;;
		'webp')
			ffmpeg -y                     \
				-r 60  -i "$tit"/%06d.png \
				-vcodec libwebp_anim -lossless 1 -loop 0 -r 10  "$tit".webp;;
	esac
done

