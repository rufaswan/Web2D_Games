#!/bin/bash
[ $(which ffprobe) ] || exit
[ $(which ffmpeg ) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	t1=$(realpath "$t1")
	echo "[$#] $t1"

	#ffprobe  -v 0  -show_entries stream  "$t1"
	##############################
	video=$(ffprobe -v 0    \
		-select_streams  v       \
		-print_format    csv=p=0 \
		-show_entries    stream=index,width,height,r_frame_rate,bit_rate \
	"$t1")
	#=id,width,height,fps,bitrate
	for v in $video; do
		echo "video,$v"
		IFS=','  read -r  id  width  height  fps  bitrate <<< "$v"

		[[ "$fps"     == "N/A" ]] && fps=0
		[[ "$bitrate" == "N/A" ]] && bitrate=0

		# bitrate = width * height * fps * bpp
		bpp=$(bc <<< "scale=6; $bitrate / ($width * $height * $fps)")
		printf "  %'d = %'d * %'d * %s * %'f\n"  $bitrate  $width  $height  $fps  $bpp
	done
	#echo done
	##############################
	audio=$(ffprobe -v 0    \
		-select_streams  a       \
		-print_format    csv=p=0 \
		-show_entries    stream=index,sample_rate,channels,bit_rate:stream_tags=language \
	"$t1")
	#=id,sample_rate,channels,bitrate,lang
	for a in $audio; do
		echo "audio,$a"
		IFS=','  read -r  id  sample  channel  bitrate  lang <<< "$a"

		#continue
		loudnorm=$(ffmpeg \
			-i    "$t1"   \
			-map  0:$id   \
			-vn   -sn     \
			-af   ebur128 \
			-f    null    \
		- 2>&1 | grep -A 8 'Integrated loudness')
		echo "loudnorm,$id,$loudnorm"

		# -af loudnorm=I=-10.9:LRA=1:TP=-1
		#
		# Integrated loudness:
		#  I:         -10.9 LUFS  # loudnorm
		#  Threshold: -20.9 LUFS
		#
		# Loudness range:
		#  LRA:         1.0 LU    # loudnorm
		#  Threshold: -30.7 LUFS
		#  LRA low:   -11.3 LUFS
		#  LRA high:  -10.3 LUFS
		#
		# TruePeak = I + TP
		# = -10.9 + -1
		# = -11.9
	done
	#echo done
	##############################
	srt=$(ffprobe -v 0    \
		-select_streams  s       \
		-print_format    csv=p=0 \
		-show_entries    stream=index:stream_tags=language \
	"$t1")
	#=id,lang
	for s in $srt; do
		echo "srt,$s"
		IFS=','  read -r  id  lang <<< "$s"
	done
	#echo done
	##############################
done
