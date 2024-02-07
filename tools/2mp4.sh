#!/bin/bash
[ $(which ffmpeg)  ] || exit
nice='nice -n 19'

while [ "$1" ]; do
	t1="${1%/}"
	#dir="${t1%/*}"
	#bas="${t1##*/}"
	#tit="${bas%.*}"
	#ext="${bas##*.}"
	shift

	[ -f "$t1" ] || continue
	$nice  ffmpeg -y    \
		-i "$t1"        \
		-vcodec libx264 \
		-q:v 0          \
		-b:a 24k        \
		-r 15 -g 150    \
		-ac 1 -ar 44100 \
		"$t1".mp4
done
