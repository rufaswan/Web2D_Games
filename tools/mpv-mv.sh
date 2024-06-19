#!/bin/bash
[ $(which mpv)   ] || exit
[ $(which xprop) ] || exit
[ $# = 0 ] && exit

ws="$(xprop -root -notype _NET_CURRENT_DESKTOP)"
while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	[ -f "$t1" ] || continue
	# pause when moved to another workspace
	while [[ "$(xprop -root -notype _NET_CURRENT_DESKTOP)" != "$ws" ]]; do
		sleep 1
	done

	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'video/'* | 'audio/'*)
			mpv  --quiet           \
				--really-quiet     \
				--window-maximized \
				--geometry="+0+0"  \
				--title="[$#] ${t1:2}" \
				--script-opts="osc-visibility=always" \
				--af="loudnorm=I=-14:TP=-1"           \
				"$t1"
			if [ -f "$t1" ]; then
				mkdir -p /tmp/new/meme
				mv -n  "$t1"  /tmp/new/meme
			fi
			;;
		*)  echo "skip [$mime] ${t1:2}";;
	esac
done

<<'////'
				--autofit=100%     \
////
