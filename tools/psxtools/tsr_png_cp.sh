#!/bin/bash
echo "${0##*/}  DEST_DIR  SRC_DIR...";
(( $# < 2 )) && exit

dest="$1"
mkdir -p "$dest"
shift

id=0
move=''
while [ "$1" ]; do
	t1="$1"
	shift

	case "$t1" in
		'-mv')  move=1;  continue;;
		'-cp')  move=''; continue;;
	esac

	[ -d "$t1" ] || continue
	[ -f "$t1/0000.png" ] || continue
	for f in "$t1"/0*.png; do
		fn=$(printf  "%s/%06d.png"  "$dest"  $id)
		if [ "$move" ]; then
			mv -vf "$f"  "$fn"
		else
			cp -vf "$f"  "$fn"
		fi
		let id++
	done
done
