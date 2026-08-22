#!/bin/bash
[ $(which oggenc ) ] || exit
[ $(which ffmpeg ) ] || exit
[ $(which ffprobe) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  AUDIO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

ffprobe=(
	ffprobe
	-count_packets
	-loglevel        quiet
	-select_streams  a:0
	-show_entries    stream=nb_read_packets
	-print_format    default=nokey=1:noprint_wrappers=1
)
while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	# check if has audio stream
	t1=$(realpath "$t1")
	cnt=$(${ffprobe[@]}  "$t1")
	[ "$cnt" ] || continue
	(( $cnt > 1 )) || continue

	echo "[$cnt] $t1"
	ffmpeg -v 0  \
		-i "$t1" \
		-f wav   \
		- | oggenc \
			--quality  -1      \
			--resample 44100   \
			--discard-comments \
			-  --output="$t1".ogg
done
