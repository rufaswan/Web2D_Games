#!/bin/bash
ROOT=$(dirname "$(realpath $0)")

base="/opt/wine-stable"
wine="$base/bin/wine"
[ -x "$wine" ] || exit
echo "== WINE = $wine"

dll=(
	winemenubuilder.exe=d   # Disable Desktop Icons
	mshtml=d    # Disable Gecko
	mscoree=d   # Disable Mono
	quartz=n,b  # MPEG-1 system streams
	dsound=n,b  # Touhou Vorbis DLL
)
export WINEDLLOVERRIDES=$(IFS=\;; echo "${dll[*]}")
echo "== WINEDLLOVERRIDES = $dll"

export  WINEDEBUG="-all,err+all"
export   WINEARCH="win32"
export WINEPREFIX="/tmp/$WINEARCH"
export       HOME="/tmp/home-dummy"

libex="$base/lib"
. "$ROOT"/lib32_deb_env.sh

ldd -v "$base"/bin/* | grep -i 'not found' | sort
find "$base" -type f -iname "*.so"   -exec ldd -v {} \; | grep 'not found' | sort
find "$PWD"  -type f -iname "*.so"   -exec ldd -v {} \; | grep 'not found' | sort
find "$PWD"  -type f -iname "*.so.*" -exec ldd -v {} \; | grep 'not found' | sort

[ -d "$HOME" ] || "$base"/bin/winecfg

# resolve dlopen() dlsym() driver symbols
export LD_DEBUG=files
export LD_BIND_NOW=1
[ $# = 0 ] || "$wine" "$@"
