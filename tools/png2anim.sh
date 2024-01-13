#!/bin/bash
[ $# = 0 ] && exit
[ $(which ffmpeg) ] || exit

ifps=60
while [ "$1" ]; do
	t1="${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	if [ -d "$tit" ]; then
		# input png @ 60 FPS , output animation @ 10 FPS
		case "$ext" in
			'gif')
				ffmpeg -y                     \
					-r $ifps  -i "$tit"/%06d.png \
					-r 10  "$tit".gif;;
			'apng')
				ffmpeg -y                     \
					-r $ifps  -i "$tit"/%06d.png \
					-f apng -plays 0 -r 10  "$tit".apng;;
			'webp')
				ffmpeg -y                     \
					-r $ifps  -i "$tit"/%06d.png \
					-vcodec libwebp_anim -lossless 1 -loop 0 -r 10  "$tit".webp;;
		esac
	else
		let ifps=$t1*1
		if (( $ifps < 1 )); then
			$ifps=60
		fi
	fi
done

