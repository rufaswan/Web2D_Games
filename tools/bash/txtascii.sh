#!/bin/bash
[ $(which iconv) ] || exit
[ $# = 0 ] && exit

while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	[ -f "$t1" ] || continue
	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'text/'*)
			txt="$(cat "$t1" | iconv  -f utf-8  -t ascii//TRANSLIT)"
			[ "$txt" ] && echo "$txt" > "$t1"
			;;
	esac
done
