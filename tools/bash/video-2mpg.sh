#!/bin/bash
[ $(which ffmpeg) ] || exit

while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	# to join all .MPG into one file
	#   cat  1.mpg 2.mpg 3.mpg  >  123.mpg
	[ -f "$t1" ] || continue
	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'video/'* | 'audio/'*)
			nice -n 19  ffmpeg -y \
				-v 0      \
				-i "$t1"  \
				-qscale 0 \
				"$t1".mpg
			;;
	esac
done
