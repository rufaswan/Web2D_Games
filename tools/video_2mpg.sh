#!/bin/bash
[ $(which ffmpeg)  ] || exit
nice='nice -n 19'

while [ "$1" ]; do
	t1="${1%/}"
	shift

	# to join all .MPG into one file
	#   cat  1.mpg 2.mpg 3.mpg  >  123.mpg
	[ -f "$t1" ] || continue
	$nice  ffmpeg -y \
		-i "$t1"  \
		-qscale 0 \
		"$t1".mpg
done
