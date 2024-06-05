#!/bin/bash
[ $# = 0 ] && exit

while [ "$1" ]; do
	t1="${1%/}"
	shift

	[ -f "$t1" ] || continue

	# FILE = dir/filename.mp4
	# FILE = -012345.mp4
	[[ "$(echo $t1 | grep '/')" ]] && continue
	[[ "${t1:0:1}" == '-' ]] && continue

	mkdir -p /tmp/new/meme
	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'video/'* | 'audio/'*)
			mpv  --quiet           \
				--really-quiet     \
				--window-maximized \
				--geometry="+0+0"  \
				--title="[$#] $t1" \
				--script-opts="osc-visibility=always" \
				--af="loudnorm=I=-14:TP=-1"           \
				"$t1"
			[ -f "$t1" ] && mv -n  "$t1"  /tmp/new/meme
			;;
	esac
done

<<'////'
				--autofit=100%     \
////
