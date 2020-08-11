#!/bin/bash
echo "${0##*/}  DEST_DIR  [-mv/-cp]  SRC_DIR...";
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
	#[ -f "$t1/0000.rgba" ] || continue
	for f in "$t1"/0*.rgba; do
		fn=$(printf  "%s/%04d.rgba"  "$dest"  $id)
		if [ "$move" ]; then
			mv -vf  "$f"  "$fn"
		else
			cp -vf  "$f"  "$fn"
		fi
		let id++
	done
done
