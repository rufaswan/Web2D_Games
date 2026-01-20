#!/bin/bash
[ $(which ffprobe) ] || exit
[ $(which ffmpeg ) ] || exit

s=2
size='300x200'
orin='3:2'
##############################
function setsize {
	if (( $2 > $1 )); then
		echo "$1 $2  set Portrait"
		let w=$s*100
		let h=$s*150
		orin='2:3'
	else
		echo "$1 $2  set Landscape"
		let w=$s*150
		let h=$s*100
		orin='3:2'
	fi
	size="${w}x${h}"
}
##############################

af=''
srt=''
fmt='mp4'
SECONDS=0
ffprobe=(
	ffprobe
	-loglevel        quiet
	-select_streams  v:0
	-show_entries    stream=width,height
	-print_format    default=nokey=1:noprint_wrappers=1
)
while [ "$1" ]; do
	t1=./"${1%/}"
	dir="${t1%/*}"
	bas="${t1##*/}"
	#tit="${bas%.*}"
	#ext="${bas##*.}"
	shift

	if [ -f "$t1" ]; then
		mime=$(file  --brief  --mime-type  "$t1" | grep 'video/')
		[ "$mime" ] || continue

		if [[ $fmt == 'mp4' ]]; then
			tmp="/tmp/$$.mp4"
			[ -f "$tmp" ] && rm -vf "$tmp"

			setsize $(${ffprobe[@]}  "$t1")
			echo "[$#] [mp4] s=$size  af=$af  srt=$srt"

			nice -n 19  ffmpeg -y \
				-v 0            \
				-i "$t1"        \
				$srt            \
				-s $size        \
				-aspect $orin   \
				-vcodec libx264 \
				-q:v 0          \
				-b:a 24k        \
				-r 15 -g 150    \
				-ac 1 -ar 44100 \
				$af             \
				-max_muxing_queue_size 2048 \
				"$tmp"
			[ -s "$tmp" ] || rm -v  "$tmp"  # remove 0 byte file
			[ -f "$tmp" ] && mv -vf "$tmp"  "$bas-$size".mp4
		fi
		if [[ $fmt == 'png' ]]; then
			tmp=$(tr -c '[0-9a-zA-Z]' \_ <<< "$t1")
			[ -d "$tmp" ] && rm -vfr "$tmp"
			mkdir -p "$tmp"

			setsize $(${ffprobe[@]}  "$t1")
			echo "[$#] [png] s=$size"

			#     99 =          1:39
			#    999 =         16:39
			#   9999 =       2:46:39
			#  99999 =   1d  3:46:39
			# 999999 =  11d 13:46:39
			nice -n 19  ffmpeg -y \
				-v 0          \
				-i "$t1"      \
				-s $size      \
				-aspect $orin \
				-qscale 0     \
				-r 1          \
				-an           \
				"$tmp"/%05d.png
		fi
	else
		case "${t1:2}" in
			'af' )   af='-af loudnorm=I=-14:TP=-1';;
			'srt')  srt='-map 0:v  -map 0:a  -map 0:s? -scodec copy';;
			'mp4')  fmt='mp4';;
			'png')  fmt='png';;
			*)
				let s=${t1:2}*1
				if (( $s < 1 )); then
					s=1
				fi
				;;
		esac
		continue
	fi
done
echo "[${0##*/}] total $SECONDS secs"

<<'////'
			-v quiet        \
		-b:a 48k  \

360p.mp4     92,331,319
150x100 24k  16,149,432 (17.4%)
300x200 24k  23,468,850 (25.4%)
300x200 48k  33,116,641 (38.8%)

copy.aac     52,890,137
24k c1 .aac  28,057,992
48k c1 .aac  53,256,260
q-1 c1 .ogg  32,610,243

no af       speed=46.1x  total  5 secs
w/loudnorm  speed=15.3x  total 13 secs
////
