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

mkiso=''
function get_mkiso()
{
	[ "$mkiso" ] || mkiso=$(which $1)
}
get_mkiso  mkisofs
get_mkiso  genisoimage
[ "$mkiso" ] || { echo "$msg"; exit; }

opt='-l -J -r'
while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	if [ -d "$t1" ]; then
		dir=$(readlink -f "$t1")
		[ -f "$t1.iso" ] && rm -vf "$t1.iso"

		cmd="$mkiso  $opt  -o $t1.iso  $dir"
		echo "[$#] $cmd"
		$nice  $mkiso  $opt  -o "$t1".iso  "$dir"
	else
		case "$t1" in
			'-0')    opt='-l';;
			'-dvd')  opt='-l -J -r -udf';;
			'-psx')  opt='-l -xa';;
			'-ps2')  opt='-l -udf';;
			*)       opt='-l -J -r';;
		esac
	fi
done

