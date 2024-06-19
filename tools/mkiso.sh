#!/bin/bash
# -l   = 31 filename
# -J   = 64 UCS-2BE filename (UTF-16BE)
# -r   = (rationized) file modes timestamp , user/group id
#        255 filename
# -xa  = (rationized) Yellow Book , for mix CD-DA/data
# -udf = v1.02 DVD-Video , 2GB+ Large File Support
msg="
Create a normal iso (joliet + rock) (64 char)
usage: ${0##*/}   [OPTIONS]   DIR
options:
  -0   : ISO9660     (-l)
  -cd  : Unicode CD  (-l -J -r)      [default]
  -dvd : Unicode DVD (-l -J -r -udf)
  -psx : PSX CD      (-l -xa)
  -ps2 : PS2 CDVD    (-l -udf)
"

[ $# = 0 ] && { echo "$msg"; exit; }
nice='nice -n 19'

function get_mkiso {
	# mkisofs     from cdrecord/cdrtools , GPL->CDDL v2.01.01a09+
	# genisoimage from wodim   /cdrkit   , fork of cdrtools v2.01a38 GPL
	if [ $(which mkisofs) ]; then
		echo $(which mkisofs)
		return 0
	fi
	if [ $(which genisoimage) ]; then
		echo $(which genisoimage)
		return 0
	fi
	exit
}
mkiso=$(get_mkiso)
[ "$mkiso" ] || { echo "$msg"; exit; }

opt='-l -J -r'
while [ "$1" ]; do
	t1=./"${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	if [ -d "$t1" ]; then
		dir=$(readlink -f  "$t1")
		[ -f "$t1".iso ] && rm -vf "$t1".iso

		cmd="$mkiso  $opt  -o $t1.iso  $dir"
		echo "[$#] $cmd"
		$nice  $mkiso  $opt  -o "$t1".iso  "$dir"
	else
		case "$t1" in
			'0'|'-0')      opt='-l';;
			'dvd'|'-dvd')  opt='-l -J -r -udf';;
			'psx'|'-psx')  opt='-l -xa';;
			'ps2'|'-ps2')  opt='-l -udf';;
			*)       opt='-l -J -r';;
		esac
	fi
done
