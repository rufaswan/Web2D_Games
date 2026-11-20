#!/bin/bash
[ $(which mpv   ) ] || exit
[ $(which ffmpeg) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

echo "@usage: AUDIO_FILE"

[ -f "$1" ] || exit
ogg=$(realpath "$1")


# detect audio level LUFS
# TARGET = -14 LUFS (youtube)
# https://en.wikipedia.org/wiki/EBU_R_128
LUFS=$(ffmpeg    \
	-i   "$ogg"  \
	-vn  -sn     \
	-af  ebur128 \
	-f   null    \
- 2>&1 | awk '/I:/ {val=$2} END {print val}')
[ "$LUFS" ] || exit

# return float can be -.5 instead -0.5
# force return int + round()
DIFF=$(bc <<< "scale=0; (-14.5 - $LUFS) / 1")
echo "LUFS   -14 = $LUFS + $DIFF"


# How to Guarantee 100% Zero Disk I/O
mpv=(
	$(which mpv)
	--no-config
	--no-input-default-bindings
	--no-input-terminal
	--no-video
	--af="volume=${DIFF}dB"

	# Increases the audio output buffer to 500 ms (default is ~0.2 s), giving the sound card enough cushion to absorb the seek latency without running dry or clicking.
	--audio-buffer=0.5

	# Forces mpv to enable the demuxer cache even on local files and buffer up to 60 seconds.
	--cache=yes
	--cache-secs=60

	# Expands the memory pool so even huge audio files fit entirely in RAM.
	--demuxer-max-bytes=$(( 150 << 20 ))

	# Prevents mpv from throwing away the beginning of the file while it plays toward the end. Set this higher than your audio file's total size.
	--demuxer-max-back-bytes=$(( 150 << 20 ))

	# Forces mpv to seek to the exact sample timestamp of ab-loop-a rather than snapping to the nearest container packet or keyframe.
	--hr-seek=always

	# Explicitly forbids mpv from writing temporary cache files back to your hard drive.
	--no-cache-on-disk

	# keeps the process running forever
	--loop-file=inf
)

if [ -f "$ogg".loop ]; then
	# Source the text file variables directly into the Bash context
	source <(tr -d '\r' < "$ogg".loop)
	printf "\033]0;%s\007"   "[bgmloop] ${ogg##*/}"
	echo "LOOP_START = $LOOP_START"
	echo "LOOP_END   = $LOOP_END"

	# Force mpv to use an A-B repeat sequence:
	# --ab-loop-a dictates where the jump reset returns to
	# --ab-loop-b specifies the physical edge boundary that triggers the reset
	${mpv[@]} \
		--ab-loop-a="$LOOP_START" \
		--ab-loop-b="$LOOP_END"   \
		"$ogg"
else
	printf "\033]0;%s\007"   "[bgm] ${ogg##*/}"
	${mpv[@]} \
		--ab-loop-a=no \
		--ab-loop-b=no \
		"$ogg"
fi
