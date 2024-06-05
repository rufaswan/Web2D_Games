#!/bin/bash
[ $(which ffmpeg)  ] || exit
nice='nice -n 19'
##############################
sec=0
function time_calc {
	case $# in
		3) sec=$(echo "($1*60*60)+($2*60)+$3" | bc);;
		2) sec=$(echo "($1*60)+$2" | bc);;
		1) sec=$1;;
		*) sec=0;;
	esac
}

function time2sec {
	var=$(echo $1 | tr ':' ' ')
	time_calc $var
}
##############################
echo "${0##*/}  VIDEO  START  END  [START  END]..."

(( $# < 3 )) && exit
[ -f "$1" ] || exit
	video="$1"
	tit="${video%.*}"
	ext="${video##*.}"
	shift

while [ "$1" ]; do
	time2sec  $1 ; fr=$sec
	time2sec  $2 ; to=$sec
	shift 2

	let dur=$to-$fr
	echo ">>> from $fr to $to ($dur sec)"
	$nice  ffmpeg -y \
		-v quiet     \
		-ss $fr      \
		-i "$video"  \
		-vcodec copy \
		-acodec copy \
		-t $dur      \
		"$tit-$fr.$ext"
done
