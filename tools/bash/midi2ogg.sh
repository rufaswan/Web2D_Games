#!/bin/bash
[ $(which fluidsynth) ] || exit
[ $(which oggenc    ) ] || exit

[ $# = 0 ] && exit

sf2=''
while [ "$1" ]; do
	t1=./"${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	if [ -f "$t1" ]; then
		case "$ext" in
			'sf2' | 'SF2')
				sf2="$t1"
				;;
			'mid' | 'midi' | 'MID' | 'MIDI')
				[ "$sf2" ] || continue

				fluidsynth       \
					-nli         \
					-F "$t1".wav \
					"$sf2"       \
					"$t1"
				oggenc               \
					--quality -1     \
					--resample 44100 \
					"$t1".wav
				rm -vf  "$t1".wav
				;;
		esac
	fi
done

<<'////'
Default Windows MIDI Soundfont by Roland / Microsoft Corporation
	http://musical-artifacts.com/artifacts/713
	C:\Windows\System32\Drivers\GM.DLS

.mid  audio/midi
.sf2  application/x-riff
////
