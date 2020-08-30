#!/bin/bash
php=""
png=''
if [ -f "/tmp/clut2png.php" ]; then
	php="/tmp/clut2png.php"
	png=1
elif [ -f "/tmp/clut2bmp.php" ]; then
	php="/tmp/clut2bmp.php"
	png=''
fi
[ "$php" ] || exit
export php png

function clut2png
{
	[ -f "$1" ] || continue
	php.sh  "$php"  "$1"

	if [ ! "$png" ]; then
		convert "$1.bmp" \
			-define png:include-chunk=none,trns -strip \
			"$1".png &> /dev/null
		rm "$1".bmp
	fi

	rm "$1"
}
export -f clut2png

find . -iname "*.clut"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.rgba"   | xargs  -I {} bash -c 'clut2png "$@"' _ {}
find . -iname "*.clut.*" | xargs  rename .clut.  .
find . -iname "*.rgba.*" | xargs  rename .rgba.  .
