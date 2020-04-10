#!/bin/bash
[ $# = 0 ] && exit

function DIRMAKE
{
	while [ "$1" ]; do
		if [ -d "$1" ]; then
			mkdir -m 0777 -p "$PWD/${1:$LEN}"
			DIRMAKE "$1"/*
		else
			ln -s "$1"  "$PWD/${1:$LEN}"
		fi
		shift
	done
}

function DIRDEL
{
	while [ "$1" ]; do
		if [ -d "$1" ]; then
			DIRDEL "$1"/*
			rmdir  "$1"
		fi
		shift
	done
}

while [ "$1" ]; do
	t1="${1%/}"
	#tit="${t1%.*}"
	#ext="${t1##*.}"
	shift

	[ -d "$t1" ] || continue

	iso="$HOME/iso/$t1.iso"
	[ -f "$iso" ] || continue

	cd "$t1"
	tmp="/tmp/${t1}_iso"
	if [ "$(mount | grep $tmp)" ]; then
		echo "UNMOUNT $tmp"
		umount "$tmp"
		find "$PWD" -xtype l -delete -print
		DIRDEL "$PWD"/*
	else
		mkdir -p "$tmp"
		echo "MOUNT $iso -> $tmp"
		mount -v -t iso9660 -o ro,loop  "$iso"  "$tmp"
		let LEN=${#tmp}+1
		DIRMAKE "$tmp"/*
	fi
	cd ..

done

