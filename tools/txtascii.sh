#!/bin/bash
[ $(which iconv) ] || exit
[ $# = 0 ] && exit

tmp="/tmp/txt.$$"
while [ "$1" ]; do
	t1="${1%/}"
	shift

	[ -f "$t1" ] || continue
	mime=$(file  --brief  --mime-type  "$t1")
	case "$mime" in
		'text/'*)
			[ -f "$tmp" ] && rm "$tmp"
			iconv                  \
				-f utf-8           \
				-t ascii//TRANSLIT \
				"$t1"              \
				-o "$tmp"          \
				&&  mv -f  "$tmp"  "$t1"
			;;
	esac

done
