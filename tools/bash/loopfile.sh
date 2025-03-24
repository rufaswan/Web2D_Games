#!/bin/bash
echo "usage: ${0##*/}  'COMMAND'  FILE...
into
COMMAND FILE
COMMAND FILE
"

[ $# = 0 ] && exit
cmd="$1"
shift

while [ "$1" ]; do
	t1="$1"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	if [ "$cmd" ]; then
		echo "$cmd $t1"
		nice -n 19  $cmd  "$t1"
	fi

done

