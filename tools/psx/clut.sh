#!/bin/bash
php="/tmp/clut2bmp.php"
[ -f "$php" ] || exit
export php

function clut2png
{
	[ -f "$1" ] || continue
	php.sh $php "$1"
	mogrify -format png -strip "$1".bmp
	rm "$1"
	rm "$1".bmp
}
export -f clut2png

find . -iname "*.clut"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.rgba"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.clut.*" | xargs  rename .clut.  .
find . -iname "*.rgba.*" | xargs  rename .rgba.  .
