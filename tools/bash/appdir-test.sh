#!/bin/bash

# run from terminal/desktop check
[ -t 0 ] || exit

# make clean a working appdir / rox-app
dir='appdir_test'
[ -d $dir ] && rm -vfr $dir
[ -e $dir ] && exit

apprun='#!/bin/bash
export APP_DIR="$(dirname "$(readlink -f "$0")")"
#export LD_LIBRARY_PATH="$APP_DIR"/usr/lib:$LD_LIBRARY_PATH
exec  "$APP_DIR"/usr/bin/test.sh  "App Run"  "$@"'

testsh='#!/bin/bash
xterm -hold -e  echo  "Test Script"  "$@"'

desktop='[Desktop Entry]
Name=Test
Icon=icon
Terminal=true
Type=Application
Categories=Utility;'

icon='<?xml version="1.0"?>
<svg width="48px" height="48px"></svg>'

mkdir -p $dir/usr/bin
echo "$apprun"  > $dir/AppRun
echo "$testsh"  > $dir/usr/bin/test.sh
echo "$desktop" > $dir/test.desktop
echo "$icon"    > $dir/icon.svg
chmod +x $dir/AppRun
chmod +x $dir/usr/bin/test.sh
ln -s  icon.svg  $dir/.DirIcon

# run as appdir / rox-app
rox='/usr/local/apps/ROX-Filer/ROX-Filer'
if [ -x "$rox" ]; then
	echo "has ROX-Filer"
	"$rox"  $dir
fi

# run as appimage
if [ -x ./appimagetool ]; then
	echo "has appimagetoolr"
	export ARCH=$(uname -m)

	./appimagetool  $dir  $dir.AppImage
	chmod +x  $dir.AppImage
	./$dir.AppImage  "AppImage"
fi
