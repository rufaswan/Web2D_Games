#!/bin/bash
[ $(which iconv) ] || exit
[ $# = 0 ] && exit

while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	mime=$(file  --brief  --mime-type  "$t1" | grep 'text/')
	[ "$mime" ] || continue

	txt="$(cat "$t1" | iconv  -f utf-8  -t ascii//TRANSLIT)"
	[ "$txt" ] && echo "$txt" > "$t1"
done
