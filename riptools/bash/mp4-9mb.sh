#!/bin/bash
[ $(which ffprobe) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

# max = 2 MiB
let MAX_MP4=1000*1000*9
TMP_MP4=/tmp/$$.mp4

# $1  fname
function optmp4 {
	# actively optimize until resulf MP4 is under 9 MB"
	local scale=640
	while [ TRUE ]; do
		echo "scale = $scale"
		# 640x640\<  only shrinks larger than 640x640
		# 640x640\^  shorter side is 640
		ffmpeg -y    \
			-v  0    \
			-i  "$1" \
			-vcodec libx264 \
			-qscale 0       \
			-r 15 -g 150    \
			-vf "scale=\
					'if(gt(iw,ih),-1,if(gt(iw,$scale),$scale,-1))'\
					:'if(gt(ih,iw),-1,if(gt(ih,$scale),$scale,-1))'\
					:flags=lanczos" \
			-b:a 48k        \
			-ac 2 -ar 44100 \
			-max_muxing_queue_size 2048 \
			-map_metadata -1 \
			-map_chapters -1 \
			-fflags +bitexact -bitexact \
			-metadata:s:v:0  handler_name='' \
			-metadata:s:a:0  handler_name='' \
			$TMP_MP4

		local sz=$(wc -c < $TMP_MP4)
		if (( $sz < $MAX_MP4 )); then
			mv -vf  $TMP_MP4  "$1".mp4
			return
		fi

		# scale  640 600 560 520 480 440 400 360 320 280 240 200 160 120 80 40 0
		let scale-=40
		(( $scale < 1 )) && return
	done
}

ffprobe=(
	ffprobe
	-count_packets
	-loglevel        quiet
	-select_streams  v:0
	-show_entries    stream=nb_read_packets
	-print_format    default=nokey=1:noprint_wrappers=1
)

while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	# check if has video stream
	t1=$(realpath "$t1")
	cnt=$(${ffprobe[@]}  "$t1")
	[ "$cnt" ] || continue
	(( $cnt > 1 )) || continue

	echo "[$#][$cnt] $t1"
	optmp4  "$t1"
done

<<'////'

////
