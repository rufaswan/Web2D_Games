#!/bin/bash
[ $(which geany) ] || exit

REAL=$(realpath "$0")
 dir=$(dirname "$REAL")
geany &
sleep 1

geany "$dir"/*.adoc "$dir"/*/*.adoc
