#!/bin/bash
[ $(which ffmpeg) ] || exit
nice='nice -n 19'

ifps=60
while [ "$1" ]; do
	t1=./"${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	if [ -d "$tit" ]; then
		# input png @ 60 FPS , output animation @ 10 FPS
		case "$ext" in
			'gif')
				$nice  ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-r 10  "$tit".gif;;
			'apng')
				$nice  ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-f apng  -pix_fmt rgba       \
					-plays 0                     \
					-r 10  "$tit".apng;;
			'webp')
				$nice  ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-vcodec libwebp_anim  -pix_fmt bgra \
					-lossless 1  -loop 0         \
					-r 10  "$tit".webp;;
			'mov')
				$nice  ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-vcodec qtrle  -pix_fmt argb \
					-r 10  "$tit".mov;;
		esac
	else
		let ifps=${t1:2}*1
		if (( $ifps < 1 )); then
			ifps=60
		fi
	fi
done
