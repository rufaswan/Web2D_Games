#!/bin/bash
[ -t 0 ] && xterm='' || xterm='xterm -e'
nice='nice -n 19'

[ $(which zip) ] || exit
zip=(zip zip -r -y -9)
date=''

bakdir="$PWD"
SECONDS=0
while [ "$1" ]; do
	t1="${1%/}"
	shift

	if [ -e "$t1" ]; then
		d="$date"
		if [[ "$date" == 'new' ]]; then
			d='-'$(find "$t1" -type f -printf "%TY%Tm%Td-%TH%TM\n" | sort | tail -1)
		fi

		# zip generated at PWD
		base=$(basename "$t1")
		name=$(printf '%s%s.%s'  "$base"  "$d"  "${zip[0]}")
		echo "[$#] $zip $t1"

		if [ -f "$t1" ]; then
			$xterm  $nice  "${zip[@]:1}"  "$name"  "$t1"
		fi
		if [ -d "$t1" ]; then
			case ${zip[1]} in
				'mkisofs')
					$xterm  $nice  "${zip[@]:1}"  "$name"  "$t1"
					;;
				'mksquashfs')
					$xterm  $nice  "${zip[@]:1}"  "$t1"  "$name"
					;;
				*)
					cd "$t1"
					$xterm  $nice  "${zip[@]:1}"  "$bakdir"/"$name"  *
					cd "$bakdir"
					;;
			esac
		fi
	else
		case "$t1" in
			'zip')  [ $(which zip  ) ] && zip=(zip zip   -r -y -9);;
			'7z' )  [ $(which 7z   ) ] && zip=(7z  7z  a -r -y -w/tmp);;
			'rar')  [ $(which rar  ) ] && zip=(rar rar a -r -y);;

			'gz' )  [ $(which gzip ) ] && zip=(tar.gz  tar -v --gunzip -cf);;
			'bz2')  [ $(which bzip2) ] && zip=(tar.bz2 tar -v --bzip2  -cf);;
			'xz' )  [ $(which xz   ) ] && zip=(tar.xz  tar -v --xz     -cf);;

			'iso')  [ $(which mkisofs) ] && zip=(iso     mkisofs -l -J -r -o);;
			'psx')  [ $(which mkisofs) ] && zip=(psx.iso mkisofs -l -xa   -o);;
			'dvd')  [ $(which mkisofs) ] && zip=(dvd.iso mkisofs -l -udf  -o);;

			'sfs')  [ $(which mksquashfs) ] && zip=(sfs mksquashfs);;

			'date')  date='-'$(date +"%Y%m%d-%H%M");;
			'new' )  date=$t1;;
		esac
		echo "zip=$zip  date=$date"
	fi
done
echo "[${0##*/}] total $SECONDS secs"
