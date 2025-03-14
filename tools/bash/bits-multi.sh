#!/bin/bash

# sll   a3, t0, 1 // 10
# addu  a3, t0    // 11
# sll   a3, 2     // 1100
# subu  a3, t0    // 1011
# sll   a3, 3     // 1011000
# subu  a3, t0    // 1010111
# sll   a3, 1     // 10101110
# ==> a3 = t0 * 0xae
#
# 1+2-3-1 = n * ae

while [ "$1" ]; do
	t1="$1"
	shift

	len=${#t1}
	pos=0
	val=1
	while (( $pos < $len )); do
		c=${t1:$pos:1}
		let pos++

		case $c in
			# shift left logical
			'0')  let val=$val*2**0;;
			'1')  let val=$val*2**1;;
			'2')  let val=$val*2**2;;
			'3')  let val=$val*2**3;;
			'4')  let val=$val*2**4;;
			'5')  let val=$val*2**5;;
			'6')  let val=$val*2**6;;
			'7')  let val=$val*2**7;;
			'8')  let val=$val*2**8;;
			'9')  let val=$val*2**9;;
			'a')  let val=$val*2**10;;
			'b')  let val=$val*2**11;;
			'c')  let val=$val*2**12;;
			'd')  let val=$val*2**13;;
			'e')  let val=$val*2**14;;
			'f')  let val=$val*2**15;;

			# add or subtract
			'+')  let val=$val+1;;
			'-')  let val=$val-1;;

			# invalid
			*)    exit;;
		esac
	done

	printf "%s = n * %x\n"  $t1  $val
done
