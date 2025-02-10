#!/bin/bash
[ $(which ffmpeg)  ] || exit
##############################
function time2sec {
	var=( $(echo $1 | tr ':' ' ') )
	case ${#var[@]} in
		3)  echo "${var[2]}+(${var[1]}*60)+(${var[0]}*60*60)" | bc;;
		2)  echo "${var[1]}+(${var[0]}*60)"                   | bc;;
		1)  echo "${var[0]}";;
		*)  echo 0;;
	esac
	return 0;
}
##############################
echo "${0##*/}  VIDEO  START  END  [START  END]..."

(( $# < 3 )) && exit
t1=./"$1"
shift

# arg[1] is video/audio file
[ -f "$t1" ] || exit
mime=$(file  --brief  --mime-type  "$t1")
case "$mime" in
	'video/'* | 'audio/'*)
		video="$t1"
		tit="${video%.*}"
		ext="${video##*.}"
		;;
	*)  exit;;
esac

while [ "$2" ]; do
	fr=$(time2sec $1)
	to=$(time2sec $2)
	shift 2

	let dur=$to-$fr
	(( $dur < 1 )) && continue
	echo ">>> from $fr to $to ($dur sec)"

	# http://trac.ffmpeg.org/wiki/Seeking
	# -ss before -i
	#    The input will be parsed by keyframe, which is very fast.
	# -ss after  -i
	#    The input is decoded (and discarded) until it reaches the position indicated by "-ss".
	#    This will be done relatively slow, frame-by-frame.
	nice -n 19  ffmpeg -y \
		-v 0         \
		-ss $fr      \
		-i "$video"  \
		-vcodec copy \
		-acodec copy \
		-t $dur      \
		"$tit-$fr.$ext"
done
