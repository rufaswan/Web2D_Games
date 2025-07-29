#!/bin/bash

con=''
max=0
function imver {
	[ "$con" ] && return 0
	con=$(which "convert-im${1}.q${2}")
	let max=2**"$2"
	echo "convert=$con  max=$max"
}
for q in 64 32 16 8; do
	for im in 8 7 6 5 4; do
		imver  $im  $q
	done
done
[ "$con" ] || exit
##############################
[ $# = 0 ] && exit

while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	# invalid = dir/filename.png
	sep=$(grep '/' <<< "$t1")
	[ "$sep" ] && continue

	# image file only
	mime=$(file  --brief  --mime-type  "$t1" | grep 'image/')
	[ "$mime" ] || continue

	# convert return float
	#   perc=$(bc <<< "$mean * 1000 / $max")
	mean=$($con  "$t1"  -colorspace Gray  -format '%[mean]'  info:)
	let perc="${mean%.*}"*1000/"$max"
	echo "mean=$mean  perc=$perc"

	# update existing mean percentage
	check=$(grep '^[0-9][0-9][0-9] ' <<< "$t1")
	fn="$t1"
	[ "$check" ] && fn="${t1:4}"

	out=$(printf  '%03d %s'  $perc  "$fn")
	echo "[$#] '$t1' -> '$out'"
	mv -n  "$t1"  "$out"
done

<<'////'
All values will be returned in the range of 0 to quantumrange
(Q8=255, Q16=65535)

 64  16,384
128  32,768
192  49,152
256  65,536
////
