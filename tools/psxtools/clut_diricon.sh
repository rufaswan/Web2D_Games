#!/bin/bash
<<'////'
[license]
[/license]
////

php="/tmp/clut2bmp.php"
[ -f "$php" ] || exit
export php

[ $# = 0 ] && f0="-V" || f0="-r"
export f0

function diricon
{
	[ -d "$1" ] || return
	cd "$1"
	[ -f '.DirIcon' ] && rm -vf '.DirIcon'

	tmp="/tmp/icon.png"
	[ -f "$tmp" ] && rm -vf "$tmp"

	for t1 in $(ls -1 * | sort $f0); do
		case "$t1" in
			*'.rgba' | *'.clut')
				php.sh  "$php"  "$t1"
				convert  "$t1.bmp" \
					-define png:include-chunk=none,trns \
					-define png:compression-filter=0 \
					-define png:compression-level=9 \
					-trim -strip \
					"$tmp" &> /dev/null
				mv -f  "$tmp"  '.DirIcon'
				rm "$t1.bmp"
				return;;

			*'.bmp'  | *'.png' )
				convert  "$t1" \
					-define png:include-chunk=none,trns \
					-define png:compression-filter=0 \
					-define png:compression-level=9 \
					-trim -strip \
					"$tmp" &> /dev/null
				mv -f  "$tmp"  '.DirIcon'
				return;;
		esac
	done
}
export -f diricon

find "$PWD" -type d | xargs -I {} bash -c 'diricon "$@"' _ {}
