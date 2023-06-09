#!/bin/bash
[ -x "./configure" ] || exit

ROOT="$PWD"
tmp="/tmp/php-$$"

# build barebone php exe
if [ ! -d "$tmp" ]; then
	./configure  --prefix="$tmp"  --disable-all
	make clean

	make && make install
	make clean
fi

# loop all extension dirs
for ext in "$ROOT"/ext/*; do
	[ -d "$ext" ] || continue
	cd "$ext"
	[ -f "done" ] && continue

	echo "BUILD $PWD"
	ln -s config*.m4 config.m4

	"$tmp"/bin/phpize
	./configure  --prefix="$tmp"  --with-php-config="$tmp"/bin/php-config
	make clean

	make && make install && touch "done"
	make clean
done

# strip all debug symbols
find "$tmp" -type f -exec strip {} \;
