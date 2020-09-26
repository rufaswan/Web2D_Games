#!/bin/bash
php="/tmp/clut2bmp.php"
[ -f "$php" ] || exit
export php

function diricon
{
	[ -d "$1" ] || return
	cd "$1"
	png=""
	[ -f "0000.clut" ] && png="0000.clut"
	[ -f "0000.rgba" ] && png="0000.rgba"
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
