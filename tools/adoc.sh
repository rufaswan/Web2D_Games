#!/bin/bash
adoc=$(which asciidoctor)
[ "$adoc" ] || exit

[ $# = 0 ] && exit
while [ "$1" ]; do
	t1="${1%/}"
	ext="${t1##*.}"
	shift

	[[ "$ext" == 'adoc' ]] || continue
	"$adoc"                    \
		-a stylesheet\!        \
		-a last-update-label\! \
		"$t1"

done

