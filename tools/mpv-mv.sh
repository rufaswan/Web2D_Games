#!/bin/bash
[ $(which mpv)   ] || exit
[ $(which xprop) ] || exit
[ $# = 0 ] && exit

xprop='xprop  -root  -notype _NET_CURRENT_DESKTOP'
ws="$($xprop)"
function mpvplay {
	while [ "$1" ]; do
		t1="$1"
		shift

		# pause when moved to another workspace
		while [[ "$($xprop)" != "$ws" ]]; do
			sleep 1
		done

		sz=$(wc -c < ./"$t1")
		let mb=$sz/1000/1000

		mpv  --quiet           \
			--really-quiet     \
			--window-maximized \
			--geometry="+0+0"  \
			--title="[$#] ${t1:2} ($mb MB)"       \
			--script-opts="osc-visibility=always" \
			--af="loudnorm=I=-14:TP=-1"           \
			"$t1"
		if [ -f "$t1" ]; then
			mkdir -p /tmp/new/meme
			mv -n  "$t1"  /tmp/new/meme
		fi
	done
}

vid=()
snd=()
while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	[ -f "$t1" ] || continue

	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'video/'*)  vid+=("$t1");;
		'audio/'*)  snd+=("$t1");;
		*)  echo "skip [$mime] ${t1:2}";;
	esac
done

# play by filesize DESC
# ERROR = cannot sort list when list is empty
#         it is the same as $(ls *)
if (( ${#vid[@]} > 0 )); then
	IFS=$'\n'
	vid=( $(ls --sort=size -1  "${vid[@]}") )
	unset IFS
	mpvplay  "${vid[@]}"
fi
if (( ${#snd[@]} > 0 )); then
	IFS=$'\n'
	snd=( $(ls --sort=size -1  "${snd[@]}") )
	unset IFS
	mpvplay  "${snd[@]}"
fi
