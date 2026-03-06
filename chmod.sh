#!/bin/bash
function chmodfile {
	local ext="${1##*.}"
	[[ "$ext" == sh ]] && return
	chmod -c 644 "$1"
}
export -f chmodfile

function chmodsh {
	local ext="${1##*.}"
	[[ "$ext" == sh ]] || return
	chmod -c 755 "$1"
}
export -f chmodsh

function chmodent {
	[ -e "$1" ] || return
	if [ -d "$1" ]; then
		# BUG chmod -c UPDATE file already 644 to 644
		# -> use find -not -perm 644 -exec chmod
		find "$1" -type d -not -perm 755 -print0 \
			| xargs -0 -I {} chmod -c 755 {}
		find "$1" -type f -not -perm 644 -print0 \
			| xargs -0 -I {} bash -c 'chmodfile "$@"' _ {}
		find "$1" -type f -not -perm 755 -print0 \
			| xargs -0 -I {} bash -c 'chmodsh "$@"' _ {}
		return
	fi
	chmodfile "$1"
}

REAL=$(realpath "$0")
 ENT=$(dirname "$REAL")
chmodent "$ENT"
