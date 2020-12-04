#!/bin/bash
<<'////'
[license]
[/license]
////

[ -f "opening.bnr" ] || exit
ROOT="$PWD"

cd "chara"
	mv  aobouzu00.ftx        Aobouzu00.ftx
	mv  Genin_tensoku00.ftx  genin_tensoku00.ftx
	mv  Jinkurou.ftx         Jinkurou_Rest.ftx
	mv  oooni.ftx            oooni00.ftx

	cp  Yukinojo00.ftx   Yukinojo00_B.ftx
	cp  Samurai_A00.ftx  Samurai_A00_B.ftx
	cp  Samurai_A01.ftx  Samurai_A01_B.ftx
	cp  Samurai_A02.ftx  Samurai_A02_B.ftx
	cp  Samurai_B00.ftx  Samurai_B00_B.ftx

	cp  Momohime.ftx  Momohime_Battle.ftx
	cp  Momohime.ftx  Momohime_Rest.ftx
	for t1 in Momohime_Effect_*.mbs; do
		[ -f "$t1" ] || continue
		tit="${t1%.*}"
		ext="${t1##*.}"
		cp  Momohime_Effect.ftx  "$tit".ftx
	done
	for t1 in Momohime_Katana_*.mbs; do
		[ -f "$t1" ] || continue
		tit="${t1%.*}"
		ext="${t1##*.}"
		cp  Momohime_Katana.ftx  "$tit".ftx
	done

	cp  Kisuke.ftx  Kisuke_Battle.ftx
	cp  Kisuke.ftx  Kisuke_Rest.ftx
	for t1 in Kisuke_Effect_*.mbs; do
		[ -f "$t1" ] || continue
		tit="${t1%.*}"
		ext="${t1##*.}"
		cp  Kisuke_Effect.ftx  "$tit".ftx
	done
	for t1 in Kisuke_Katana_*.mbs; do
		[ -f "$t1" ] || continue
		tit="${t1%.*}"
		ext="${t1##*.}"
		cp  Kisuke_Katana.ftx  "$tit".ftx
	done
cd "$ROOT"

cd "DRM_chara"
	mv  aobouzu_drm00.ftx  Aobouzu_drm00.ftx
cd "$ROOT"
