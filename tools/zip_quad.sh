#!/bin/bash
[ $# = 0 ] && exit

png=()
while [ "$1" ]; do
	t1="${1%/}"
	shift

	case "$t1" in
		*'.png')
			if [ $(which mogrify) ]; then
				mogrify -strip      \
					-interlace none \
					-define png:include-chunk=none,trns \
					-define png:compression-filter=0    \
					-define png:compression-level=9     \
					"$t1"
			fi
			png+=("$t1")
			echo "added PNG = $png"
			;;
		*'.prediv.quad')
			# skipped
			;;
		*'.quad')
			# -0 store only
			# -D do not add dir
			# -X do not add file ext attr
			echo "added QUAD = $t1"
			zip -0 -D -X "$t1.zip"  "${png[@]}"  "$t1"
			png=()
			;;
	esac
done

