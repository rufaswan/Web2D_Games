#!/bin/bash
[ $# = 0 ] && exit
ROOT=$(dirname "$(realpath $0)")

run=''
if [[ "$1" == "-run" ]]; then
	run=1
	shift
fi
elf=$(realpath "$1")
[ -x "$elf" ] || exit
echo "== ELF = $elf"

libex=$(dirname "$elf")
. "$ROOT"/lib32_deb_env.sh

ldd -v "$elf" | grep -i 'not found' | sort
find "$PWD" -type f -iname "*.so"   -exec ldd -v {} \; | grep 'not found' | sort
find "$PWD" -type f -iname "*.so.*" -exec ldd -v {} \; | grep 'not found' | sort

# resolve dlopen() dlsym() driver symbols
export LD_DEBUG=files
export LD_BIND_NOW=1
[ $run ] && "$@"
