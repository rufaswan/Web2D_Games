#!/bin/bash
<<'////'
Ubuntu LTS
	pqrs tuvw xyza bcde fghi jklm nopq
	rstu vwxy zabc defg hijk lmno pqrs

precise  trusty  xenial  bionic  focal  j  n
r  v  z  d  h  l

p  t  x  b  f  j  n
r  v  z  d  h  l
////
dist="precise  trusty  xenial  bionic  focal"
#dist="dapper"

comp="main  multiverse  restricted  universe"
#comp="main"

arch="i386  amd64"
#arch="i386"

zipp="xz  bz2  gz"
locc="archive  old-releases"

tmp="/tmp/deb_pool.$$"
pwd="$PWD"
pool=''
deb=''

##############################
function loopzip
{
	for z in $zipp; do
		echo "zip $z"

		zt="$deb.$z"
		zp="$pwd/$deb.$z"

		done=''
		for l in $locc; do
			[ "$done" ] && continue

			wget -O "$zt" \
				"http://${l}.ubuntu.com/ubuntu/dists/${d}/${c}/binary-${a}/Packages.${z}"

			if [ -s "$zt" ]; then
				cp -vf  "$zt"  "$zp"

				case "$z" in
					'xz' ) unxz   -v --decompress "$zt";;
					'bz2') bzip2  -v --decompress "$zt";;
					'gz' ) gunzip -v --decompress "$zt";;
				esac

				return
			fi
		done
	done
	return;
}
##############################
function loopcomp
{
	for c in $comp; do
		echo "comp $c"

		deb="${d}_${c}_${a}_deb"
		loopzip
	done
	return;
}
##############################
function looparch
{
	for a in $arch; do
		echo "arch $a"

		pool="pool_${d}_${a}.lst"
		[ -f "$pwd"/"$pool"     ] && return
		[ -f "$pwd"/"$pool".zip ] && return
		loopcomp

		cat *main*  *multiverse*  *restricted*  *universe* | grep -i "filename: pool" | sort > "$pool"
		[ -s "$pool" ] || continue

		#zip  "$pool".zip  "$pool"
		cp -vf  "$pool"  "$pwd/$pool"

		rm -vfr "$tmp"/*
	done
	return;
}
##############################
function loopdist
{
	for d in $dist; do
		echo "dist $d"

		[ -d "$tmp" ] && rm -vfr "$tmp"
		mkdir -p "$tmp"

		cd "$tmp"
		looparch
		cd "$pwd"

		rm -vfr "$tmp"
	done
	return;
}
##############################
loopdist
