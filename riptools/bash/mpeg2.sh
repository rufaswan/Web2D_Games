#!/bin/bash
[ $(which ffmpeg) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

# can join all .MPG into one file
#   cat  1.mpg 2.mpg 3.mpg  >  123.mpg
while [ "$1" ]; do
	t1=$(realpath "$1")
	shift

	[ -f "$t1" ] || continue
	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'video/'* | 'audio/'*)
			$xt  ffmpeg -y \
				-v 0      \
				-i "$t1"  \
				-qscale 0 \
				"$t1".mpg
			;;
	esac
done
