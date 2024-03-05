#!/bin/bash
# required mame 0.161+
if [ ! -f scripts/target/mame/tiny.lua ]; then
	echo 'No Genie Makefile support. Exit'
	exit
fi
##############################
make='make  REGENIE=1'
make="$make  NO_USE_PULSEAUDIO=1"
make="$make  USE_TAPTUN=0"
make="$make  USE_PCAP=0"
make="$make  USE_QTDEBUG=0"
make="$make  DEBUG=0  SYMBOLS=0"
make="$make  USE_BUNDLED_LIB_SDL2=1"

# mame 0.246+
if [ -f src/mame/neogeo/neogeo.cpp ]; then
	echo 'DETECT mame 0.246+'
	src="src/mame/neogeo/neogeo.cpp"
	src="$src,src/mame/capcom/cps2.cpp"
	src="$src,src/mame/capcom/cps3.cpp"

	make clean
	$make  SUBTARGET=capsnk  SOURCES=$src
	exit
fi

# mame 0.168 - 0.245
if [ -f src/mame/drivers/neogeo.cpp ]; then
	echo 'DETECT mame 0.168+'
	src="src/mame/drivers/neogeo.cpp"
	src="$src,src/mame/drivers/cps2.cpp"
	src="$src,src/mame/drivers/cps3.cpp"

	make clean
	$make  SUBTARGET=capsnk  SOURCES=$src
	exit
fi

# mame 0.121 - 0.167
if [ -f src/mame/drivers/neogeo.c ]; then
	echo 'DETECT mame 0.121+'
	src="src/mame/drivers/neogeo.c"
	src="$src,src/mame/drivers/cps2.c"
	src="$src,src/mame/drivers/cps3.c"

	make clean
	$make  SUBTARGET=capsnk  SOURCES=$src
	exit
fi

<<'////'
0121-0167  src/mame/drivers/cps2.c    src/mame/drivers/neogeo.c
0168-0245  src/mame/drivers/cps2.cpp  src/mame/drivers/neogeo.cpp
0246-0262  src/mame/capcom/cps2.cpp   src/mame/neogeo/neogeo.cpp

0161-0262  scripts/target/mame/tiny.lua
0161-0262  QT_HOME=/usr/lib64/qt48/
0172-0262  3rdparty/SDL2
////
