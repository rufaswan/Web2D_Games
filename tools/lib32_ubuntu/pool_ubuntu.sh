#!/bin/bash
[ $# = 0 ] && exit
tmp="/tmp/deb"
[ -f "$tmp" ] && rm -vf "$tmp"

while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	file="${t1##*/}"
	shift

	[[ "${t1:0:4}" == "pool" ]] || continue
	echo "[$#] $t1"
	[ -f "$file" ] && continue
	wget -q -O "$tmp"  "http://archive.ubuntu.com/ubuntu/$t1" \
		&& mv -vf "$tmp"  "$file"

	[ -f "$file" ] && continue
	wget -q -O "$tmp"  "http://old-releases.ubuntu.com/ubuntu/$t1" \
		&& mv -vf "$tmp"  "$file"

done

