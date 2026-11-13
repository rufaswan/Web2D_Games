#!/bin/bash
[ $(which mpv   ) ] || exit
[ $(which ffmpeg) ] || exit
echo "@usage: AUDIO_FILE"

[ -f "$1" ] || exit
ogg=$(realpath "$1")

# detect audio level LUFS
# TARGET = -14 LUFS (youtube)
LUFS=$(ffmpeg    \
	-i   "$ogg"  \
	-vn  -sn     \
	-af  ebur128 \
	-f   null    \
- 2>&1 | awk '/I:/ {val=$2} END {print val}')
[ "$LUFS" ] || exit

DIFF=$(bc <<< "scale=0; (-14.5 - $LUFS) / 1")
echo "LUFS   -14 = $LUFS + $DIFF"

# How to Guarantee 100% Zero Disk I/O
# --cache=yes: Forces the caching engine to stay active even for local audio files.
# --demuxer-max-bytes=500MiB: Expands the memory pool so even huge audio files fit entirely in RAM.
# --cache-on-disk=no: Explicitly forbids mpv from writing temporary cache files back to your hard drive.
mpv=(
	$(which mpv)
	--cache=yes
	--demuxer-max-bytes=$(( 150 << 20 ))
	--no-cache-on-disk
	--no-config
	--no-video
	--af="volume=${DIFF}dB"
	--loop-file=inf
)

if [ -f "$ogg".loop ]; then
	# Source the text file variables directly into the Bash context
	source <(tr -d '\r' < "$ogg".loop)
	printf "\033]0;%s\007"   "[bgmloop] ${ogg##*/}"
	echo "LOOP_START = $LOOP_START"
	echo "LOOP_END   = $LOOP_END"

	# Force mpv to use an A-B repeat sequence:
	# --loop-file=inf keeps the process running forever
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
