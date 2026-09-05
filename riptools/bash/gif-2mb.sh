#!/bin/bash
[ $(which ffprobe) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

let MAX_10_GIF=1000*1000*2
let MAX_16_GIF=1024*1024*2
TMP_GIF=/tmp/$$.gif

# $1  fname
function optgif {
	# actively optimize until resulf GIF is under 2 MB"
	local scale=640
	while [ TRUE ]; do
		echo "scale = $scale"
		# 640x640\<  only shrinks larger than 640x640
		# 640x640\^  shorter side is 640
		ffmpeg -y    \
			-v  0    \
			-i  "$1" \
			-vf "scale=\
					'if(gt(iw,ih),-1,if(gt(iw,$scale),$scale,-1))'\
					:'if(gt(ih,iw),-1,if(gt(ih,$scale),$scale,-1))'\
					:flags=lanczos,\
				split[s0][s1];\
					[s0]palettegen=\
						stats_mode=full\
						:max_colors=15\
						:reserve_transparent=1\
						:transparency_color=magenta[p];\
					[s1][p]paletteuse=dither=none\
						:diff_mode=rectangle,\
				fps=10" \
			-map_metadata -1 \
			-map_chapters -1 \
			-fflags +bitexact -bitexact \
			$TMP_GIF

		local sz=$(wc -c < $TMP_GIF)
		if (( $sz < $MAX_10_GIF )); then
			mv -vf  $TMP_GIF  "$1".gif
			return
		fi

		# scale  640 600 560 520 480 440 400 360 320 280 240 200 160 120 80 40 0
		let scale-=40
		(( $scale < 144 )) && return
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
	optgif  "$t1"
done

<<'////'

////
