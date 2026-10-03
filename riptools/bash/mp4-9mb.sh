#!/bin/bash
[ $(which ffprobe) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

let MAX_10_MP4=1000*1000*9
let MAX_16_MP4=1024*1024*9
TMP_MP4=/tmp/$$.mp4

# $1  fname
function optmp4 {
<<'////'
	local TMP_PAL="$1".png
	if [ ! -f "$TMP_PAL" ]; then
		ffmpeg -y  -v 0  -i "$1"  \
			-vf "palettegen=stats_mode=full:max_colors=255" \
			"$TMP_PAL"
	fi

			-i "$1"       \
			-i "$TMP_PAL" \
			-i "$TMP_OGG" \
			-filter_complex "[0:v]scale=\
					'if(gt(iw,ih),-16,if(gt(iw,$scale),$scale,-16))'\
					:'if(gt(ih,iw),-16,if(gt(ih,$scale),$scale,-16))',\
				fps=30[v0];\
				[v0][1:v]paletteuse=\
					dither=none\
					:diff_mode=rectangle[v1]" \
			-map "[v1]" \
			-map 2:a:0  \
////
	# video reduce number of color
	# audio lowest quality
	local TMP_PAL="$1".png
	local TMP_OGG="$1".ogg

	if [ ! -f "$TMP_PAL" ]; then
		ffmpeg -y  -v 0  -i "$1"  \
			-vf "palettegen=stats_mode=full:max_colors=255" \
			"$TMP_PAL"
	fi
	if [ ! -f "$TMP_OGG" ]; then
		ffmpeg -y  -v 0  -i "$1"  -f wav  -  \
			| oggenc  --quality -1  --resample 44100  -  \
			--output="$TMP_OGG"
	fi

	# actively optimize until resulf MP4 is under 9 MB"
	local scale=160
	while [ TRUE ]; do
		echo "scale = $scale"
		# 640x640\<  only shrinks larger than 640x640
		# 640x640\^  shorter side is 640
		ffmpeg -y         \
			-i "$1"       \
			-i "$TMP_PAL" \
			-i "$TMP_OGG" \
			-filter_complex "[0:v]scale=\
					'if(gt(iw,ih),-16,if(gt(iw,$scale),$scale,-16))'\
					:'if(gt(ih,iw),-16,if(gt(ih,$scale),$scale,-16))',\
				fps=30[v0];\
				[v0][1:v]paletteuse=\
					dither=none\
					:diff_mode=rectangle[v1]" \
			-map "[v1]" \
			-map 2:a:0  \
			-c:v libx264  -q:v 0 \
			-c:a aac      -q:a 0 \
			-pix_fmt yuv420p   \
			-tune    animation \
			-max_muxing_queue_size 2048 \
			-map_metadata -1 \
			-map_chapters -1 \
			-fflags +bitexact -bitexact \
			-metadata:s:v:0  handler_name='' \
			-metadata:s:a:0  handler_name='' \
			$TMP_MP4

		local sz=$(wc -c < $TMP_MP4)
		if (( $sz < $MAX_10_MP4 && $sz > 1 )); then
			mv -vf  $TMP_MP4  "$1".$scale.mp4
			return
		fi
		echo "[>9mb] $sz"

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
	optmp4  "$t1"
done

<<'////'
fps  15    24    30
v    2283  2228  2252
v    4250  4449  4553
= 2x fps = +0.1x filesize max
////
