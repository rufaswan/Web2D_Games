#!/bin/bash
<<'////'
[license]
[/license]
////

echo "${0##*/}  PNG_FILE";
[ $# = 0 ] && exit

function thumbnail()
{
	# game  icon = 240x125
	# sheet icon = 148x125 = 74x62 * 200%
	sc='-scale 200%'
	(( $1 > 74 )) && sc=''
	(( $2 > 62 )) && sc=''
	mogrify -verbose \
		$sc \
		-define png:include-chunk=none,trns -strip \
		-background transparent \
		-gravity center \
		-extent 148x125 \
		thumb.png
}

png=$1
[ -f "$png" ] || exit
convert -verbose  "$png"  -trim -strip  thumb.png
thumbnail $(identify -format "%w %h %i"  thumb.png)
