#!/bin/bash
[ $(which asciidoctor) ] || exit
nice='nice -n 19'

[ $# = 0 ] && exit
while [ "$1" ]; do
	t1="${1%/}"
	ext="${t1##*.}"
	shift

	[[ "$ext" == 'adoc' ]] || continue
	$nice  asciidoctor         \
		-a stylesheet\!        \
		-a last-update-label\! \
		"$t1"

done

