#!/bin/bash
[ $# = 0 ] && exit

function thumbnail()
{
	# 148x125 = 74x62 * 200%
	cp -vf  $3  thumb.png
	(( $1 > 74 )) && return
	(( $2 > 62 )) && return
	convert -verbose -scale 200% -strip  $3  thumb.png
}

png=$(printf "%06d.png"  $1)
[ -f "$png" ] || exit
thumbnail $(identify -format "%w %h %i"  "$png")

#mogrify -verbose -strip -trim +repage  0*.png
montage -verbose -strip \
	-geometry '1x1+3+3<' \
	-background none \
	-bordercolor none \
	-frame 2 \
	-gravity center  \
	0*.png  sheet.png

<<'////'
# imagemagick.org/script/command-line-processing.php
s%       scale wxh by s percent
sw%xsh%  scale w by sw percent and h by sh percent
w        auto h
xh       auto w
wxh      max auto wxh , 640x480 => 100x200  = 100x75
wxh^     min auto wxh , 640x480 => 100x200^ = 267x200
wxh!     exact wxh
wxh>     shrink  if larger  than wxh , 640x480 => 100x200> = 100x75
wxh<     enlarge if smaller than wxh , 640x480 => 100x200< = 640x480

# imagemagick.org/script/command-line-options.php?#scale
'-scale' == '-resize' with '-filter box'
////
