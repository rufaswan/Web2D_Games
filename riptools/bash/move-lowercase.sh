#!/bin/bash

# $1  ent
function lowercase {
	local low=$(tr '[A-Z]' '[a-z]' <<< "$1")
	[[ "$1" == "$low" ]] && return

	mkdir -p $(dirname "$low")
	mv -vf "$1"  "$low"
}
export -f lowercase

while [ "$1" ]; do
	t1="$1"
	shift

	if [ -f "$t1" ]; then
		lowercase "$t1"
	fi
	if [ -d "$t1" ]; then
		find  "$t1"  -type f  -exec \
			bash -c 'lowercase "$@"' -- {} \;
		find "$t1"  -empty  -delete  -print
	fi
done
