#!/bin/bash
[ $(which ffprobe) ] || exit
[ $(which ffmpeg ) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."

VIDEO_DURATION=0

AUDIO_CHANNEL=0
AUDIO_BITRATE=0
AUDIO_FILTER=''

# $1  channels
function set_audio {
	if (( $1 == 0 )); then
		AUDIO_CHANNEL=0
		AUDIO_BITRATE=0
		AUDIO_FILTER='-an'
		return
	fi
	if (( $1 == 1 )); then
		AUDIO_CHANNEL=1
		AUDIO_BITRATE=44100
	else
		AUDIO_CHANNEL=2
		AUDIO_BITRATE=88200
	fi
	AUDIO_FILTER="-c:a aac  -b:a ${AUDIO_BITRATE}  -ac ${AUDIO_CHANNEL}  -ar 44100"
}

# $1  realpath mp4
function audio_info {
	local info=$(ffprobe -v 0    \
		-select_streams  a:0     \
		-print_format    csv=p=0 \
		-show_entries    stream=sample_rate,channels \
	"$1")
	#=sample_rate,channels
	echo "audio,$info"

	set_audio  0
	[ "$info" ] || return
	local sample
	IFS=','  read -r  sample  AUDIO_CHANNEL <<< "$info"

	set_audio  $AUDIO_CHANNEL
}
##############################
VIDEO_WIDTH=0
VIDEO_HEIGHT=0
VIDEO_FPS=15
VIDEO_BITRATE=0
VIDEO_FILTER=''

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
	VIDEO_FPS=15
	VIDEO_BITRATE=0
	VIDEO_FILTER='-vn'
	[ $VIDEO_DURATION ] || return

	if (( $(bc <<< "$VIDEO_DURATION > 0") )); then
		local info=$(ffprobe -v 0    \
			-select_streams  v:0     \
			-print_format    csv=p=0 \
			-show_entries    stream=width,height,r_frame_rate \
		"$1")
		#=width,height,fps
		echo "video,$info"

		[ "$info" ] || return
		IFS=','  read -r  VIDEO_WIDTH  VIDEO_HEIGHT  VIDEO_FPS <<< "$info"

		VIDEO_FPS=$(bc <<< "scale=0; $VIDEO_FPS / 1")
		if (( $VIDEO_FPS > 15 )); then
			VIDEO_FPS=15
		fi
	fi
}
##############################
TARGET_SIZE=$(( 10 << 20 ))
TMP_MP4=/tmp/$$.mp4

SECONDS=0
while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	video_info  "$t1"
	(( $VIDEO_WIDTH  < 1 )) && continue
	(( $VIDEO_HEIGHT < 1 )) && continue
	audio_info  "$t1"

	echo "[$#] ${t1} (s=${VIDEO_WIDTH}x${VIDEO_HEIGHT} ac=${AUDIO_CHANNEL} , dur=${VIDEO_DURATION})"
	TARGET_BITRATE=$(bc <<< "scale=0; ($TARGET_SIZE / $VIDEO_DURATION * 8) / 1")

	# for > 4 min video, audio will have more bitrate then video
	# downmix stereo into mono so video can get more bitrate
	if (( $AUDIO_CHANNEL > 1 )); then
		vbps=$(bc <<< "scale=0; $TARGET_BITRATE - $AUDIO_BITRATE")
		if (( $(( 44100 * 4 )) > $vbps )); then
			echo "downmix stereo to mono"
			set_audio  1
		fi
	fi

	# reduce video resolution to increase bitrate
	# = better quality
	scale=100
	bakw=$VIDEO_WIDTH
	bakh=$VIDEO_HEIGHT

	# 5 sec test run
	#   25% =  7+ sec
	#   50% = 11+ sec
	#   75% = 21+ sec
	TARG_TEST_SIZE=$(bc <<< "scale=0; $TARGET_BITRATE * 5 / 8")
	echo "[5 sec] target=$TARG_TEST_SIZE"
	for perc in 25 50 75; do
		seek=$(bc <<< "scale=0; $VIDEO_DURATION * $perc / 100")
		if (( $(bc <<< "$VIDEO_DURATION > ($seek + 5)") )); then
			while [ TRUE ]; do
				 VIDEO_WIDTH=$(bc <<< "scale=0;($bakw * $scale * 0.01) / 4 * 4")
				VIDEO_HEIGHT=$(bc <<< "scale=0;($bakh * $scale * 0.01) / 4 * 4")

				# global frame for seek back/forward
				# in +/- 10 sec
				VIDEO_FILTER="-c:v libx264  -q:v 0  -s ${VIDEO_WIDTH}x${VIDEO_HEIGHT}  -r ${VIDEO_FPS}  -g $(( ${VIDEO_FPS} * 10 ))"

				echo "VIDEO_FILTER=$VIDEO_FILTER"

				ffmpeg -y  -v 0   \
					-i   "$t1" \
					$VIDEO_FILTER \
					-an  -sn   \
					-ss  $seek \
					-t   5     \
					-max_muxing_queue_size 2048 \
					$TMP_MP4

				diff=$(( $TARG_TEST_SIZE - $(stat -c%s $TMP_MP4) ))
				if (( $diff < 0 )); then
					echo "[$diff] [5 sec] result larger than original"
					(( scale-- ))
					continue
				else
					break
				fi
			done
		fi
	done

	echo "== start ffmpeg =="
	while [ TRUE ]; do
		 VIDEO_WIDTH=$(bc <<< "scale=0;($bakw * $scale * 0.01) / 4 * 4")
		VIDEO_HEIGHT=$(bc <<< "scale=0;($bakh * $scale * 0.01) / 4 * 4")

		# global frame for seek back/forward
		# in +/- 10 sec
		VIDEO_FILTER="-c:v libx264  -q:v 0  -s ${VIDEO_WIDTH}x${VIDEO_HEIGHT}  -r ${VIDEO_FPS}  -g $(( ${VIDEO_FPS} * 10 ))"

		echo "VIDEO_FILTER=$VIDEO_FILTER"
		echo "AUDIO_FILTER=$AUDIO_FILTER"

		ffmpeg -y  -v 0   \
			-i "$t1"      \
			$VIDEO_FILTER \
			$AUDIO_FILTER \
			-max_muxing_queue_size 2048 \
			$TMP_MP4

		diff=$(( $TARGET_SIZE - $(stat -c%s $TMP_MP4) ))
		if (( $diff < 0 )); then
			echo "[$diff] result larger than original"
			(( scale-- ))
			continue
		else
			echo "[$diff] done"
			mv -vf  $TMP_MP4  "$t1".mp4
			touch  --reference="$t1"  "$t1".mp4
			break
		fi
	done
done
echo "[${0##*/}] total $SECONDS secs"

<<'////'
1920x1080 , 10:34=634.083333 sec , 506,656,420 byte
	=   799,037 byte/sec
	= 6,392,300  bit/sec
$((10 << 20)) = stereo 228x128 , 10,406,774 byte
	=    16,412 byte/sec
	=   131,298  bit/sec = 43,098 + 88200
$((10 **  7)) = stereo 192x108 ,  9,743,572 byte
	=    15,366 byte/sec
	=   122.931  bit/sec = 34,731 + 88200

$((10 << 20)) = mono 304x172 , 8,625,770 byte
	=   13,603 byte/sec
	=  108,824  bit/sec = 64,724 + 44100

youtube 360p
640x360 , 3:19=199.875 sec , 17,994,253 byte
	=  90,027 byte/sec
	= 720,220  bit/sec
$((10 << 20)) = stereo 368x208 , 10,470,247 byte
	=  52,383 byte/sec
	= 419,071  bit/sec = 330,871 + 88200


$((10 << 20)) = 10,485,760 byte
1 min =  60 sec = 1,398,096 bit/sec = 1,309,896 + 88200
2 min = 120 sec =   699,048 bit/sec =   610,848 + 88200
3 min = 180 sec =   466,032 bit/sec =   377,832 + 88200
4 min = 240 sec =   349,520 bit/sec =   261,320 + 88200
5 min = 300 sec =   279,616 bit/sec =   191,416 + 88200
->mono
6 min = 360 sec =   233,016 bit/sec =   144,816 + 88200
7 min = 420 sec =   199,728 bit/sec =   111,528 + 88200
8 min = 480 sec =   174,760 bit/sec =    86,560 + 88200
9 min = 540 sec =   155,344 bit/sec =    67,144 + 88200
////
