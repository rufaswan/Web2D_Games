#!/bin/bash
[ $(which convert) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

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

	# mean is 0.997 or 99.7% , return 997
	mean=$(convert  "$t1"  -colorspace Gray  -format '%[fx:trunc(mean*1000)]'  info:)
	echo "mean=$mean"

	# update existing mean percentage
	check=$(grep '^[0-9][0-9][0-9] ' <<< "$t1")
	fn="$t1"
	[ "$check" ] && fn="${t1:4}"

	out=$(printf  '%03d %s'  $mean  "$fn")
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
