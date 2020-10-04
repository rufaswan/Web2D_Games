#!/bin/bash
php="/tmp/clut2bmp.php"
[ -f "$php" ] || exit
export php

[ $# = 0 ] && f0="head" || f0="tail"
export f0

function diricon
{
	[ -d "$1" ] || return
	cd "$1"
	[ -f '.DirIcon' ] && rm -vf '.DirIcon'

	png=""
	for t1 in clut rgba; do
		t2=$(ls -1 *.$t1 | $f0 -1)
		[ -f "$t2" ] && png="$t2"
	done
	[ "$png" ] || return
	php.sh  "$php"  "$png"

	tmp="/tmp/icon.png"
	[ -f "$tmp" ] && rm -vf "$tmp"
	convert  "$png.bmp" \
		-define png:include-chunk=none,trns -trim -strip \
		"$tmp" &> /dev/null
	mv -f  "$tmp"  '.DirIcon'
	rm "$png.bmp"
}
export -f diricon

find "$PWD" -type d | xargs -I {} bash -c 'diricon "$@"' _ {}
