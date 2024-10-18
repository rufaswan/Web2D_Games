#!/bin/bash
[ $# = 0 ] && exit
# Default Windows MIDI Soundfont by Roland / Microsoft Corporation
# musical-artifacts.com/artifacts/713
# C:\Windows\System32\Drivers\GM.DLS
sf2="/tmp/win98_gm.sf2"

while [ "$1" ]; do
	t1=./"${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	[ -e "$sf2" ] || continue
	[ -f "$t1"  ] || continue
	fluidsynth        \
		-nli          \
		-F "$tit".wav \
		"$sf2"        \
		"$t1"

	oggenc               \
		-q -1            \
		--resample 44100 \
		"$tit".wav

	rm -vf  "$tit".wav
done
