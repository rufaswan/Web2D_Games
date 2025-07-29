#!/bin/bash
real=$(realpath "$0")
d=$(dirname "$real")

function chmodfile {
	local mime=$(file  --brief  --mime-type  "$1")
	case "$mime" in
		'application/x-executable')
			chmod -c 755  "$1";;
		'application/x-sharedlib')
			chmod -c 755  "$1";;
		'text/x-shellscript')
			chmod -c 755  "$1";;
		*)
			chmod -c 644  "$1";;
	esac
}
export -f chmodfile

find "$d" -type f -print0 | xargs -0 -I {} bash  -c 'chmodfile "$@"' _ {}
find "$d" -type d -print0 | xargs -0 -I {} chmod -c 755 {}
