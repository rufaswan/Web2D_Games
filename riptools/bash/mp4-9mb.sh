#!/bin/bash
[ $(which ffprobe) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage : ${0##*/}  VIDEO_FILE..."

VIDEO_DURATION=0

AUDIO_CHANNEL=0
AUDIO_BITRATE=0
AUDIO_FILTER=''

function audio_info {
	local info=$(ffprobe -v 0  \
		-select_streams  a:0   \
		-print_format    csv   \
		-show_entries    stream=channels \
	"$1")
	#=channels
	echo "$info"

	AUDIO_CHANNEL=0
	AUDIO_BITRATE=0
	AUDIO_FILTER='-an'
	[ "$info" ] || return
	local arr=( $(tr ',' ' ' <<< "$info") )

	#if (( ${arr[1]} > 1 )); then
		#AUDIO_CHANNEL=2
		#AUDIO_BITRATE=88200
		#AUDIO_FILTER="-c:a aac  -b:a ${AUDIO_BITRATE}  -ac ${AUDIO_CHANNEL}  -ar 44100"
	#else
		AUDIO_CHANNEL=1
		AUDIO_BITRATE=44100
		AUDIO_FILTER="-c:a aac  -b:a ${AUDIO_BITRATE}  -ac ${AUDIO_CHANNEL}  -ar 44100"
	#fi
}
##############################
VIDEO_WIDTH=0
VIDEO_HEIGHT=0
VIDEO_FPS=15
VIDEO_BITRATE=0
VIDEO_FILTER=''

function video_info {
	local info=$(ffprobe -v 0  \
		-select_streams  v:0   \
		-print_format    csv   \
		-show_entries    stream=width,height,duration \
	"$1")
	#=width,height,duration
	echo "$info"

	VIDEO_DURATION=0
	VIDEO_WIDTH=0
	VIDEO_HEIGHT=0
	VIDEO_BITRATE=0
	VIDEO_FILTER='-vn'
	[ "$info" ] || return
	local arr=( $(tr ',' ' ' <<< "$info") )

	VIDEO_WIDTH=$(bc <<< "scale=0;(${arr[1]} / 4) * 4" )
	VIDEO_HEIGHT=$(bc <<< "scale=0;(${arr[2]} / 4) * 4" )
	VIDEO_DURATION=${arr[3]}

	if [[ "$VIDEO_DURATION" == 'N/A' ]]; then
		VIDEO_DURATION=$(ffprobe -v 0  \
			-select_streams  v:0       \
			-show_entries    packet=pts_time \
			-read_intervals  99999%+#1000    \
			-print_format    default=noprint_wrappers=1:nokey=1 \
		"$1" | tail -1)
	fi
}
##############################
MB_PER_MIN=$(bc <<< "scale=0; 1024*1024 * 8 / 60")
TARGET_BIT=$(bc <<< "scale=0; 2.25 * $MB_PER_MIN")

SECONDS=0
while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	BIT_PER_SEC=$TARGET_BIT

	video_info  "$t1"
	audio_info  "$t1"
	(( $VIDEO_WIDTH  < 1 )) && continue
	(( $VIDEO_HEIGHT < 1 )) && continue

	echo "[$#] ${t1} (s=${VIDEO_WIDTH}x${VIDEO_HEIGHT} ac=${AUDIO_CHANNEL} , dur=${VIDEO_DURATION})"

	# allocate a part target size for audio
	if (( $AUDIO_BITRATE > 1 )); then
		BIT_PER_SEC=$(( $BIT_PER_SEC - $AUDIO_BITRATE ))
	fi
	echo "bit/sec = $BIT_PER_SEC + $AUDIO_BITRATE"

	# reduce video resolution to increase bitrate
	# = better quality
	scale=100
	bakw=$VIDEO_WIDTH
	bakh=$VIDEO_HEIGHT
	while [ TRUE ]; do
		VIDEO_BITRATE=$(bc <<< "scale=0;($VIDEO_WIDTH * $VIDEO_HEIGHT * $VIDEO_FPS * 0.055) / 1")
		if (( $VIDEO_BITRATE > $BIT_PER_SEC )); then
			let scale--;
			VIDEO_WIDTH=$(bc <<< "scale=0;($bakw * $scale * 0.01) / 4 * 4")
			VIDEO_HEIGHT=$(bc <<< "scale=0;($bakh * $scale * 0.01) / 4 * 4")
		else
			break
		fi
	done

	VIDEO_SIZE=$(bc <<< "scale=0;($VIDEO_BITRATE * $VIDEO_DURATION) / 8")
	AUDIO_SIZE=$(bc <<< "scale=0;($AUDIO_BITRATE * $VIDEO_DURATION) / 8")
	FULL_SIZE=$(( $VIDEO_SIZE + $AUDIO_SIZE ))
	echo "estimate = $VIDEO_SIZE + $AUDIO_SIZE = $FULL_SIZE"

	# global frame for seek back/forward
	# in +/- 10 sec
	let GFPS=$VIDEO_FPS*10
	VIDEO_FILTER="-c:v libx264  -b:v ${VIDEO_BITRATE}  -s ${VIDEO_WIDTH}x${VIDEO_HEIGHT}  -r ${VIDEO_FPS}  -g ${GFPS}"

	echo "VIDEO_FILTER=$VIDEO_FILTER"
	echo "AUDIO_FILTER=$AUDIO_FILTER"

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
		-map_metadata -1 \
		-map_chapters -1 \
		-fflags +bitexact -bitexact \
		-metadata:s:v:0  handler_name='' \
		-metadata:s:a:0  handler_name='' \
		"$t1".mp4

	rm -vf  ffmpeg2pass-0.log  ffmpeg2pass-0.log.mbtree
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
////
