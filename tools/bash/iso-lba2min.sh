
#!/bin/bash

function lba2min {
	local hex=$(tr '[a-f]' '[A-F]' <<< "$1")
	local lba=$(bc <<< "obase=10;ibase=16;$hex")
	let lba+=150

	local f=$(bc <<< "$lba % 75")
	let lba=$lba/75

	local s=$(bc <<< "$lba % 60")
	let lba=$lba/60

	local m=$lba
	printf  '%02d:%02d:%02d'  $m  $s  $f
	return
}

function min2lba {
	local lba=0
	case $# in
		3)  lba=$(bc <<< "$1*60*75 + $2*75 + $3");;
		2)  lba=$(bc <<< "           $1*75 + $2");;
		1)  lba=$(bc <<< "                   $1");;
	esac
	let lba-=150
	(( $lba < 1 )) && { echo 0; return; }

	printf  '%x'  $lba
	return
}
export -f  lba2min  min2lba

while [ "$1" ]; do
	t1=( $(tr -c '[0-9a-fA-F]'  \  <<< "$1") )
	shift
	#echo "[$#] ${t1[@]}"

	# lba     0 -> min 00:02:00
	# lba 2902b -> min 37:21:54
	# lba 6dd39 -> min 99:59:74
	#     max = 6dd3a*800 =   921,292,800 =  ~878 MB
	#           6dd3a*930 = 1,058,047,200 = ~1009 MB
	case ${#t1[@]} in
		1)
			vmin=$(lba2min  ${t1[0]})
			echo "${t1[0]} -> $vmin"
			;;
		*)
			vlba=$(min2lba  ${t1[@]})
			echo "$vlba -> ${t1[@]}"
			;;
	esac
done

<<'////'
////
