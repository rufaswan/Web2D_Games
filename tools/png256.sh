#!/bin/bash

usage="
usage: ${0##*/}  [bpp]  IMG_FILE...
bpp:
  1  to   2 colors palette PNG
  2  to   4 colors palette PNG
  3  to   8 colors palette PNG
  4  to  16 colors palette PNG
  5  to  32 colors palette PNG
  6  to  64 colors palette PNG
  7  to 128 colors palette PNG
  8  to 256 colors palette PNG [default]
"

[ $# = 0 ] && { echo "$usage"; exit; }
color=255

while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	# bKGD (background color chunk) = +1 color
	case "$t1" in
		'1')  color=1;   continue;;
		'2')  color=3;   continue;;
		'3')  color=7;   continue;;
		'4')  color=15;  continue;;
		'5')  color=31;  continue;;
		'6')  color=63;  continue;;
		'7')  color=127; continue;;
		'8')  color=255; continue;;
	esac

	[ -f "$t1" ] || continue
	convert -verbose    \
		"$t1"           \
		+dither         \
		-colors $color  \
		-density 72x72  \
		-interlace none \
		-strip          \
		-define png:include-chunk=none,trns \
		-define png:compression-filter=0    \
		-define png:compression-level=9     \
		"$t1".png

done

<<'////'
identify -format '%A'  $img
identify -format '%[opaque]'  $img
convert $img -channel A -threshold 0    $img
convert $img -channel A -threshold 254  $img
convert $img -channel A -threshold 1%   $img
convert $img -channel A -threshold 99%  $img
convert $img -channel A -separate       $img-alpha

Galzoo GA (2265 png)
8bit 118,625,999
7bit  95,568,905 (+19.43%)
6bit  74,115,932 (+37.52%)
5bit  59,546,019 (+49.80%)
4bit  44,597,554 (+62.40%)
3bit (+%)
2bit (+%)
1bit (+%)

JPG to PNG
  7-bpp PNG > JPG > 6-bpp PNG
  colored  6-bpp
  gray     3-bpp
////
