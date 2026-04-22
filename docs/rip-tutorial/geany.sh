#!/bin/bash
[ $(which geany) ] || exit

REAL=$(realpath "$0")
 dir=$(dirname "$REAL")

if [ $(pidof geany) ]; then
	echo -n
else
	geany &
	sleep 1
fi

geany "$dir"/*.adoc "$dir"/*/*.adoc
