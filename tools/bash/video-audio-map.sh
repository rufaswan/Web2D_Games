#!/bin/bash
[ $(which ffmpeg) ] || exit

while [ "$1" ]; do
	t1="${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	# to merge video and audio track to MKV file
	[ -f "$tit".video ] || continue
	[ -f "$tit".audio ] || continue
	nice -n 19  ffmpeg -y \
		-v 0            \
		-i "$tit".video \
		-i "$tit".audio \
		-vcodec copy    \
		-acodec copy    \
		-map 0:v:0      \
		-map 1:a:0      \
		"$tit".mkv
done
