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
let MAX_10_MP4=1000*1000*9
let MAX_16_MP4=1024*1024*9

SECONDS=0
while [ "$1" ]; do
	t1="$1"
	shift

	[ -f "$t1" ] || continue

	ORIG_SIZE=$(wc -c < "$t1")
	TARG_SIZE=$MAX_10_MP4

	video_info  "$t1"
	audio_info  "$t1"
	(( $VIDEO_WIDTH  < 1 )) && continue
	(( $VIDEO_HEIGHT < 1 )) && continue

	echo "[$#] ${t1} (s=${VIDEO_WIDTH}x${VIDEO_HEIGHT} ac=${AUDIO_CHANNEL} , dur=${VIDEO_DURATION})"

	# allocate a part target size for audio
	AUDIO_SIZE=0
	if (( $AUDIO_BITRATE > 1 )); then
		AUDIO_SIZE=$(bc <<< "scale=0;($AUDIO_BITRATE * $VIDEO_DURATION) / 8")
	fi
	TARG_SIZE=$(bc <<< "scale=0;($TARG_SIZE - $AUDIO_SIZE) / 1")
	echo "target = $TARG_SIZE + $AUDIO_SIZE"

	# reduce video resolution to increase bitrate
	# = better quality
	while [ '1' ]; do
		VIDEO_BITRATE=$(bc <<< "scale=0;($VIDEO_WIDTH * $VIDEO_HEIGHT * $VIDEO_FPS * 0.055) / 1")
		VIDEO_SIZE=$(bc <<< "scale=0;($VIDEO_BITRATE * $VIDEO_DURATION) / 8")
		if (( $VIDEO_SIZE > $TARG_SIZE )); then
			VIDEO_WIDTH=$(bc <<< "scale=0;($VIDEO_WIDTH * 0.95) / 4 * 4")
			VIDEO_HEIGHT=$(bc <<< "scale=0;($VIDEO_HEIGHT * 0.95) / 4 * 4")
		else
			break
		fi
	done

	# add 5% container overhead on top video + audio data
	FINAL_SIZE=$(bc <<< "scale=0;(($VIDEO_SIZE + $AUDIO_SIZE) * 1.05) / 1")
	echo "${ORIG_SIZE} -> ${FINAL_SIZE} (s=${VIDEO_WIDTH}x${VIDEO_HEIGHT})"
	if (( $ORIG_SIZE < $FINAL_SIZE )); then
		echo "[SKIP] larger than original"
		continue
	fi

	# global frame for seek back/forward
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
1280x720
-r $((60/2)) = -r 30 = 460x248
-r $((60/4)) = -r 15 = 640x352
	mono = 712x392


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
////
