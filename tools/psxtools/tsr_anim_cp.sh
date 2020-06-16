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
	dn=$(printf  "%s/anim_%d"  "$dest"  $id)
	if [ "$move" ]; then
		mv -vfr "$t1"  "$dn"
	else
		cp -vfr "$t1"  "$dn"
	fi
	let id++
done
