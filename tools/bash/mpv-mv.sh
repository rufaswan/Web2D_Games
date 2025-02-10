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
			sleep 0.5
		done

		# file renamed/moved before watching
		if [ -f "$t1" ]; then
			sz=$(wc -c < ./"$t1")
			let mb=$sz/1000/1000

			nice -n 19  mpv        \
				--quiet            \
				--really-quiet     \
				--window-maximized \
				--geometry="+0+0"  \
				--title="[$#] ${t1:2} ($mb mib)"      \
				--script-opts="osc-visibility=always" \
				--af="loudnorm=I=-14:TP=-1"           \
				"$t1"

			# to prevent accident fast-forward on next video
			sleep 0.5
		fi

		# file renamed/moved during watching
		if [ -f "$t1" ]; then
			mkdir -p      /tmp/new/meme/
			mv -n  "$t1"  /tmp/new/meme/
		fi
	done
}

vid=()
snd=()
sort='--sort=size'
while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	[ -d "$t1" ] && continue

	if [ -f "$t1" ]; then
		mime=$(file  --brief  --mime-type  "$t1")
		case "$mime" in
			'video/'*)  vid+=("$t1");;
			'audio/'*)  snd+=("$t1");;
			*)  echo "skip [$mime] ${t1:2}";;
		esac
	else
		case "${t1:2}" in
			'x')  sort='';;
			'r')  sort='-Sr';;  # --sort=size  --reverse
			*)    sort='-S ';;  # --sort=size
		esac
	fi
done

# play by filesize DESC
# ERROR = cannot sort list when list is empty
#         $(ls  ) === $(ls *)
if (( ${#vid[@]} > 0 )); then
	echo "video sort=$sort"
	if [ "$sort" ]; then
		IFS=$'\n'
		vid=( $(ls  -1  $sort  "${vid[@]}") )
		unset IFS
	fi
	mpvplay  "${vid[@]}"
fi

if (( ${#snd[@]} > 0 )); then
	mpvplay  "${snd[@]}"
fi
