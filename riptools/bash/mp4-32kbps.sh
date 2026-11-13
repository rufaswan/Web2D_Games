#!/bin/bash
[ $(which ffprobe) ] || exit
[ $(which ffmpeg ) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."

VIDEO_DURATION=0
VIDEO_BPP='0.055' # for x264

AUDIO_CHANNEL=0
AUDIO_BITRATE=0
AUDIO_FILTER=''

function audio_info {
	local info=$(ffprobe -v 0    \
		-select_streams  a:0     \
		-print_format    csv=p=0 \
		-show_entries    stream=sample_rate,channels \
	"$1")
	#=sample_rate,channels
	echo "audio,$info"

	AUDIO_CHANNEL=0
	AUDIO_BITRATE=0
	AUDIO_FILTER='-an'
	local sample
	[ "$info" ] || return
	IFS=','  read -r  sample  AUDIO_CHANNEL <<< "$info"

	if (( $AUDIO_CHANNEL >= 2 )); then
		AUDIO_CHANNEL=2
		AUDIO_BITRATE=88200
		AUDIO_FILTER="-c:a aac  -b:a ${AUDIO_BITRATE}  -ac ${AUDIO_CHANNEL}  -ar 44100"
	else
		AUDIO_CHANNEL=1
		AUDIO_BITRATE=44100
		AUDIO_FILTER="-c:a aac  -b:a ${AUDIO_BITRATE}  -ac ${AUDIO_CHANNEL}  -ar 44100"
	fi
}
##############################
VIDEO_WIDTH=0
VIDEO_HEIGHT=0
VIDEO_FPS=15
VIDEO_BITRATE=0
VIDEO_FILTER=''

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
#        32 kbyte per sec
# =   1,920 kbyte per min
# = 262,144 bit   per sec
TARGET_BIT=$(( 32 * 1024 * 8 ))

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

	# reduce video resolution to increase bitrate
	# = better quality
	scale=100
	bakw=$VIDEO_WIDTH
	bakh=$VIDEO_HEIGHT
	while [ TRUE ]; do
		 VIDEO_WIDTH=$(bc <<< "scale=0;($bakw * $scale * 0.01) / 4 * 4")
		VIDEO_HEIGHT=$(bc <<< "scale=0;($bakh * $scale * 0.01) / 4 * 4")

		# bitrate = width * height * fps * bpp
		# fps = 15 , bpp = 0.055
		# -> bitrate = width * height * 0.825
		# so for fps 1 to 20
		# -> bitrate = width * height
		VIDEO_BITRATE=$(( $VIDEO_WIDTH * $VIDEO_HEIGHT ))
		if (( $VIDEO_BITRATE > $TARGET_BIT )); then
			(( scale-- ))
		else
			break
		fi
	done
	echo "bit/sec = $VIDEO_BITRATE + $AUDIO_BITRATE"

	VIDEO_SIZE=$(bc <<< "scale=0;($VIDEO_BITRATE * $VIDEO_DURATION) / 8")
	AUDIO_SIZE=$(bc <<< "scale=0;($AUDIO_BITRATE * $VIDEO_DURATION) / 8")
	FULL_SIZE=$(( $VIDEO_SIZE + $AUDIO_SIZE ))
	echo "size   = $VIDEO_SIZE + $AUDIO_SIZE = $FULL_SIZE"

	ORIG_SIZE=$(wc -c < "$t1")
	if (( $ORIG_SIZE < $FULL_SIZE )); then
		echo "[skip] result larger than original"
		continue
	fi

	# global frame for seek back/forward
	# in +/- 10 sec
	VIDEO_FILTER="-c:v libx264  -b:v ${VIDEO_BITRATE}  -s ${VIDEO_WIDTH}x${VIDEO_HEIGHT}  -r ${VIDEO_FPS}  -g $(( ${VIDEO_FPS} * 10 ))"

	echo "VIDEO_FILTER=$VIDEO_FILTER"
	echo "AUDIO_FILTER=$AUDIO_FILTER"

	# in case leftover from previous round
	rm -vf  ffmpeg2pass-0.log  ffmpeg2pass-0.log.mbtree

	# -f null = profile main , convert failed
	# -f mp4  = profile high , matched -pass 2
	echo "pass 1"
    ffmpeg -y  -v 0   \
		-i "$t1"      \
		$VIDEO_FILTER \
		-preset slow  \
		-pass 1       \
		-an           \
		-f mp4        \
		/dev/null

	echo "pass 2"
    ffmpeg -y  -v 0   \
		-i "$t1"      \
		$VIDEO_FILTER \
		-preset slow  \
		-pass 2       \
		$AUDIO_FILTER \
		-max_muxing_queue_size 2048 \
		"$t1".mp4

	# cleanup for next round
	rm -vf  ffmpeg2pass-0.log  ffmpeg2pass-0.log.mbtree

	# result
	RESULT_SIZE=$(wc -c < "$t1".mp4)
	echo "off    by = "$(bc -l <<< "scale=2;(($RESULT_SIZE / $FULL_SIZE) - 1) * 100")" %"
	echo "shrink by = "$(bc -l <<< "scale=2;(($RESULT_SIZE / $ORIG_SIZE) - 1) * 100")" %"
done
echo "[${0##*/}] total $SECONDS secs"

<<'////'
## The Codec Efficiency Gap

When you compare an old MPEG-4 (Part 2) video (like Xvid or DivX) to an H.264 (MP4) video, you are looking at a 2x to 3x difference in compression efficiency.

Your estimate is exactly right: to get the same visual quality as a 300 kbps H.264 video, an MPEG-2 or MPEG-4 encoder requires roughly 600 kbps to 900 kbps.

Here is how the target BPP Constant scales across different generations of video technology to achieve the exact same visual quality:

| Codec Generation | Common File Extension / Name | Required BPP Constant (for identical quality) | Target Bitrate for 360p @ 24fps |
|---|---|---|---|
| MPEG-2 (Old DVDs) | .mpg / .vob | 0.150 BPP | ~830 kbps |
| MPEG-4 (Legacy Mobile) | .avi / .mp4 (legacy) | 0.110 BPP | ~600 kbps |
| H.264 / AVC (Modern Default) | .mp4 / .mkv | 0.055 BPP | ~300 kbps |
| H.265 / HEVC / VP9 | .mp4 / .webm | 0.028 BPP | ~150 kbps |
| AV1 (Next-Gen) | .mkv / .webm | 0.019 BPP | ~100 kbps |

## The Universal Codec BPP Master List

To hit the exact same target visual quality as a 0.055 H.264 video, you scale the BPP constant based on the efficiency of the codec's mathematical tools: [4]

| Codec Generation | Common Encoders / Formats | BPP Constant (Medium Motion Baseline) | 360p @ 24fps Bitrate |
|---|---|---|---|
| MPEG-1 (Ancient VCDs) | mpeg1video | 0.250 BPP | 1,380 kbps |
| MPEG-2 (DVDs / Broadcasters) | mpeg2video | 0.150 BPP | 830 kbps |
| MPEG-4 Part 2 (Old Mobile) | mpeg4, xvid, divx | 0.110 BPP | 600 kbps |
| H.264 / AVC (Modern Default) | libx264 | 0.055 BPP | 300 kbps |
| H.265 / HEVC (Ultra HD / HDR) | libx265 | 0.030 BPP | 165 kbps |
| VP9 (YouTube HD Pipeline) | libvpx-vp9 | 0.030 BPP | 165 kbps |
| AV1 (Next-Gen Royalty-Free) | libsvtav1 | 0.020 BPP | 110 kbps |

## The "Transparency Thresholds" for Common Audio Codecs

The bitrates below are listed for standard Stereo (2 channels) audio at a 44.1kHz / 48kHz sample rate.

| Audio Codec | Extension / Container | Equivalent "0.055" Baseline (Acceptable for Memes) | The Transparency Threshold (CD Quality) | Codec Generation & Efficiency |
|---|---|---|---|---|
| Opus | .ogg, .webm, .mkv | 32 kbps | 96 - 128 kbps | Next-Gen (Modern King): Exceptionally efficient. At just 32k stereo, it sounds better than a 96k MP3. |
| AAC (LC) | .m4a, .mp4 | 64 kbps | 128 - 160 kbps | Industry Standard: Default for Apple, Discord, and streaming. High efficiency and near-universal compatibility. |
| Ogg Vorbis | .ogg | 64 kbps | 160 kbps | Mid-Gen Open Source: Noticeably more efficient than MP3, but surpassed by Opus and modern AAC encoders. |
| MP3 | .mp3 | 96 kbps | 192 - 256 kbps | Legacy (Old): Introduced in 1993. It requires a massive amount of data to avoid sounding "watery" or metallic. |


tests
video   = 360 * 640 * 24 * 0.055 = 304128 bit = 38,016 byte
audio/1 = 44100 bit =  5,512 byte
audio/2 = 88200 bit = 11,025 byte

-     1s      60s=1m     3600s=1h
v     38,016  2,280,960  136,857,600
v+a1  43,528  2,611,710  156,702,600
v+a2  49,041  2,942,460  176,547,600

-     300s=5m     600s=10m
v     11,404,800  22,809,600
v+a1  13,058,550  26,117,100
v+a2  14,712,300  29,424,600


mbpm  bitps    /x264/fps  9:16
1     139,810  105,916    27x  243x432
1.25  174,762  132,395    30x  270x480
1.5   209,715  158,875    33x  297x528
1.75  244,667  185,354    35x  315x560
2     279,620  211,833    38x  342x608
2.25  314,572  238,312    40x  360x640
2.5   349,525  264,791    42x  378x672
2.75  384,477  291,271    44x  396x704
3     419,430  317,750    46x  414x736


youtube
under 3 min
3  - 20 min
over 20 min
////
