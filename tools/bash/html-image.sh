#!/bin/bash
[ $# = 0 ] && exit

function loopent {
	local fil=()
	local dir=()
	local mime=''
	local i=0
	for i in "$1"/*; do
		if [ -f "$i" ]; then
			mime=$(file  --brief  --mime-type  "$i" | grep 'image/')
			[ "$mime" ] && fil+=("$i")
			continue
		fi

		if [ -d "$i" ]; then
			dir+=("$i")
			continue
		fi
	done

	# natural sort by 0-9 then 10-99 then 100-999 ...
	IFS=$'\n'
	fil=( $(sort --version-sort <<< ${fil[*]}) )
	dir=( $(sort --version-sort <<< ${dir[*]}) )
	unset IFS

	local f=''
	if (( ${#fil[@]} > 0 )); then
		echo "<h1>$1</h1>"
		for f in "${fil[@]}"; do
			echo "<img src='$f' title='$f'>"
		done
		echo "<hr>"
	fi

	local d=''
	if (( ${#dir[@]} > 0 )); then
		for d in "${dir[@]}"; do
			loopent "$d"
		done
	fi
}

echo '<style>
body {
	text-align       : center;
	background-color : #111;
	color            : #eee;
}
img {
	max-width : 100%;
}
</style>'

while [ "$1" ]; do
	t1=./"${1%/}"
	shift

	[ -d "$t1" ] || continue
	loopent "$t1"
done
