#!/bin/bash
[ $(which ffprobe) ] || exit
[ $(which ffmpeg ) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."
[ $# = 0 ] && exit
[ -t 0 ] && xt='' || xt='xterm -e'

VIDEO_SCALE=2
VIDEO_WIDTH=0
VIDEO_HEIGHT=0

VIDEO_BPP='0.055' # for x264
VIDEO_FPS=15
VIDEO_BITRATE=0
VIDEO_ASPECT='3:2'
VIDEO_WXH='300x200'

# $1  realpath mp4
function video_info {
	VIDEO_DURATION=$(ffprobe -v 0  \
		-select_streams  v:0       \
		-show_entries    packet=pts_time \
		-read_intervals  99999%+#1000    \
		-print_format    default=noprint_wrappers=1:nokey=1 \
	"$1" | tail -1)

	VIDEO_WIDTH=0
	VIDEO_HEIGHT=0
	[ $VIDEO_DURATION ] || return

	if (( $(bc <<< "$VIDEO_DURATION > 0") )); then
		local info=$(ffprobe -v 0    \
			-select_streams  v:0     \
			-print_format    csv=p=0 \
			-show_entries    stream=width,height \
		"$1")
		#=width,height
		echo "video,$info"
		[ "$info" ] || return

		IFS=','  read -r  VIDEO_WIDTH  VIDEO_HEIGHT <<< "$info"

		if (( $VIDEO_HEIGHT > $VIDEO_WIDTH )); then
			echo "$VIDEO_WIDTH $VIDEO_HEIGHT  set Portrait"
			 VIDEO_WIDTH=$(( $VIDEO_SCALE * 100 ))
			VIDEO_HEIGHT=$(( $VIDEO_SCALE * 150 ))
			VIDEO_ASPECT='2:3'
		else
			echo "$VIDEO_WIDTH $VIDEO_HEIGHT  set Landscape"
			 VIDEO_WIDTH=$(( $VIDEO_SCALE * 150 ))
			VIDEO_HEIGHT=$(( $VIDEO_SCALE * 100 ))
			VIDEO_ASPECT='3:2'
		fi

		# bitrate = width * height * fps * bpp
		# fps = 15 , bpp = 0.055
		# -> bitrate = width * height * 0.825
		# so for fps 1 to 20
		# -> bitrate = width * height
		VIDEO_BITRATE=$(( $VIDEO_WIDTH * $VIDEO_HEIGHT ))
		VIDEO_WXH="${VIDEO_WIDTH}x${VIDEO_HEIGHT}"
		#echo "    VIDEO_WXH=$VIDEO_WXH"
		#echo "VIDEO_BITRATE=$VIDEO_BITRATE"
	fi

}
##############################
AUDIO_FILTER='-an'

# $1  realpath mp4
function audio_info {
	local info=$(ffprobe -v 0    \
		-select_streams  a:0     \
		-print_format    csv=p=0 \
		-show_entries    stream=sample_rate,channels \
	"$1")
	#=sample,channels
	echo "audio,$info"

	AUDIO_FILTER='-an'
	[ "$info" ] || return

	AUDIO_FILTER="-c:a aac  -b:a 24k  -ac 1  -ar 44100"
}
##############################
# $1  realpath mp4
# $2  basename mp4
function srt_rip {
	local info=$(ffprobe -v 0     \
		-select_streams  s        \
		-print_format    csv=p=0  \
		-show_entries    stream=index:stream_tags=language \
	"$1")
	#=index,language
	echo "srt=$info"
	[ "$info" ] || return

	local sub
	local index
	local lang
	for sub in $info; do
		IFS=','  read -r  index  lang <<< "$sub"
		ffmpeg -y  -v 0 \
			-i    "$1"  \
			-vn   -an   \
			-map  0:$index \
			-c:s  srt   \
			"$2-$lang-$index".srt
	done
}
##############################
AUDIO_LOUDNORM=''
RESULT_FORMAT='mp4'
SECONDS=0
while [ "$1" ]; do
	t1="$1"
	shift

	if [ -f "$t1" ]; then
		t1=$(realpath "$t1")
		video_info  "$t1"
		(( $VIDEO_WIDTH  < 1 )) && continue
		(( $VIDEO_HEIGHT < 1 )) && continue

		base=$(basename "$t1")
		case $RESULT_FORMAT in
			'mp4')
				echo "[$#] [mp4] s=$VIDEO_WXH  af=$AUDIO_LOUDNORM"
				audio_info  "$t1"
				srt_rip     "$t1"  "$base"

				TMP_MP4="/tmp/$$.mp4"
				rm -vf "$TMP_MP4"

				ffmpeg -y  -v 0           \
					-i      "$t1"         \
					-q:v    0             \
					-c:v    libx264       \
					-s      $VIDEO_WXH    \
					-aspect $VIDEO_ASPECT \
					-r      $VIDEO_FPS            \
					-g      $(( VIDEO_FPS * 10 )) \
					$AUDIO_FILTER         \
					$AUDIO_LOUDNORM       \
					-max_muxing_queue_size 2048 \
					"$TMP_MP4"
				[ -s "$TMP_MP4" ] || rm -v  "$TMP_MP4"  # remove 0 byte file
				[ -f "$TMP_MP4" ] && mv -vf "$TMP_MP4"  "$base-$VIDEO_WXH".mp4
				;;

			'jpg')
				echo "[$#] [png] s=$VIDEO_WXH"

				SHOT_DIR=$(tr -c '[0-9a-zA-Z]' \_ <<< "$base")
				[ -d "$SHOT_DIR" ] && rm -vfr "$SHOT_DIR"
				mkdir -p "$SHOT_DIR"

				#     99 =          1:39
				#    999 =         16:39
				#   9999 =       2:46:39
				#  99999 =   1d  3:46:39
				# 999999 =  11d 13:46:39
				ffmpeg -y  -v 0           \
					-i      "$t1"         \
					-s      $VIDEO_WXH    \
					-aspect $VIDEO_ASPECT \
					-q:v    0             \
					-r      1             \
					-an     \
					"$SHOT_DIR"/%05d.jpg
				;;
		esac
	else
		case "$t1" in
			'af' )  AUDIO_LOUDNORM='-af loudnorm=I=-14:TP=-1';;
			'mp4')  RESULT_FORMAT='mp4';;
			'jpg')  RESULT_FORMAT='jpg';;
			*)
				VIDEO_SCALE=$(bc <<< "scale=0; $t1 / 1")
				if (( $VIDEO_SCALE < 1 )); then
					VIDEO_SCALE=1
				fi
				;;
		esac
	fi
done
echo "[${0##*/}] total $SECONDS secs"

<<'////'
			-v quiet        \
		-b:a 48k  \

360p.mp4     92,331,319
150x100 24k  16,149,432 (17.4%)
300x200 24k  23,468,850 (25.4%)
300x200 48k  33,116,641 (38.8%)

copy.aac     52,890,137
24k c1 .aac  28,057,992
48k c1 .aac  53,256,260
q-1 c1 .ogg  32,610,243

no af       speed=46.1x  total  5 secs
w/loudnorm  speed=15.3x  total 13 secs


q:v 0 = 230 kbit/s
1x = 100 * 150 * 15 * 0.055
   = 12,375 bit/s
2x = 200 * 300 * 15 * 0.055
   = 49,500 bit/s


youtube
144p
= 256 * 144 * 30 * bpp = 56 kbit/s
=      1,105,920 * bpp = 57,344 bit/s
bpp = 0.051851

360p
= 360 * 640 * 23.98 * bpp = 410 kbit/s
=         5,524,992 * bpp = 419,840 bit/s
bpp = 0.075989
////
