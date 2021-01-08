#!/bin/bash
<<'////'
[license]
[/license]
////
function cpftx
{
	[ -f "$1" ] || return
	ftx="$1"
	shift

	for t1 in "$@"; do
		[ -f "$t1" ] || continue
		tit="${t1%.*}"
		ext="${t1##*.}"
		cp  "$ftx"  "$tit".ftx
	done
}

mv  aobouzu00.ftx        Aobouzu00.ftx
mv  aobouzu_drm00.ftx    Aobouzu_drm00.ftx
mv  Genin_tensoku00.ftx  genin_tensoku00.ftx
mv  Jinkurou.ftx         Jinkurou_Rest.ftx
mv  oooni.ftx            oooni00.ftx

cp  Yukinojo00.ftx   Yukinojo00_B.ftx
cp  Samurai_A00.ftx  Samurai_A00_B.ftx
cp  Samurai_A01.ftx  Samurai_A01_B.ftx
cp  Samurai_A02.ftx  Samurai_A02_B.ftx
cp  Samurai_B00.ftx  Samurai_B00_B.ftx

cpftx  Momohime.ftx         Momohime_Battle.mbs  Momohime_Rest.mbs
cpftx  Momohime_Effect.ftx  Momohime_Effect_*.mbs
cpftx  Momohime_Katana.ftx  Momohime_Katana_*.mbs
cpftx  Kisuke.ftx           Kisuke_Battle.mbs    Kisuke_Rest.mbs
cpftx  Kisuke_Effect.ftx    Kisuke_Effect_*.mbs
cpftx  Kisuke_Katana.ftx    Kisuke_Katana_*.mbs
