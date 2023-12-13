#!/bin/bash
[ $# = 0 ] && exit

s=2
size=''
orin=''
function setsize
{
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

size='300x200'
orin='3:2'
af='-af loudnorm=I=-14:TP=-1'
SECONDS=0
while [ "$1" ]; do
	t1="${1%/}"
	dir="${t1%/*}"
	bas="${t1##*/}"
	tit="${bas%.*}"
	ext="${bas##*.}"
	shift

	tmp="/tmp/$$.mp4"
	if [ -f "$t1" ]; then
		[ -f "$tmp" ] && rm -vf "$tmp"
		t2=$(ffprobe  -v error  -select_streams v:0  -show_entries stream=width,height  -of csv=s=,:p=0  "$t1")
		setsize $(echo "$t2" | tr ','  ' ')

		nice -n 19  ffmpeg -y -i "$t1"  \
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
		[ -f "$tmp" ] && mv -vf "$tmp"  "$bas-$size".mp4
	else
		case "$t1" in
			'-af')  af='';;
			'+af')  af='-af loudnorm=I=-14:TP=-1';;
			*)
				let s=$t1*1
				if (( $s < 1 )); then
					s=1
				fi
				;;
		esac
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
