#!/bin/bash

im=$(which identify)
[ "$im" ] || exit

fmt=(
	a b c d e f g h i j k l m n o p q r s t u v w x y z
	A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
)
[ $# = 0 ] && exit

while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	mime=$(file  --brief  --mime-type  "$t1" | grep 'image/')
	[ "$mime" ] || continue

	echo "[$#] FILE : ${t1:2}"
	for f in "${fmt[@]}"; do
		res=$(identify  -format "%$f"  "$t1" 2> /dev/null)
		[ "$res" ] && echo "  %$f : $res"
	done
done

# FILE : /tmp/h0305i07.png
# %a : %a
# %b : 58076B
# %c :
# %d : /tmp
# %e : png
# %f : h0305i07.png
# %g : 512x240+0+0
# %h : 240
# %i : /tmp/h0305i07.png
# %j : %j
# %k : 257
# %l :
# %m : PNG
# %n : 1
# %o :
# %p : 0
# %q : 16
# %r : DirectClassRGB
# %s : 0
# %t : h0305i07
# %u :
# %v : %v
# %w : 512
# %x : 72
# %Undefined :
# %y : 72
# %Undefined :
# %z : 8
# %A : False
# %B : %B
# %C : Zip
# %D : Undefined
# %E : %E
# %F : %F
# %G : 512x240
# %H : 240
# %I : %I
# %J : %J
# %K : %K
# %L : %L
# %M : /tmp/h0305i07.png
# %N : %N
# %O : +0+0
# %P : 512x240
# %Q : 0
# %R : %R
# %S : 2147483647
# %T : 0
# %U : %U
# %V : %V
# %W : 512
# %X : +0
# %Y : +0
# %Z :
