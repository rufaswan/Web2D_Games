#!/bin/bash
# !! This script is for 32-bit only !!
#
# https://forum.winehq.org/viewtopic.php?t=24127
# 32-bit prefixes are for 32-bit programs and should only be used with 32-bit wine.
# 64-bit prefixes are for 64-bit programs and should only be used with 64-bit wine.
#
# /bin/wine   = for 32-bit EXE + DLL only
# /bin/wine64 = for 64-bit EXE + DLL only

# limited to half of all RAM
# ulimit is measured in KB
kb=$(free -k | grep -i "mem" | awk '{ print $2 }') # mem total
let kb=$kb/2
ulimit -Sv $kb
echo "MEM LIMIT : $kb"

# Nice-scale goes from -20 (greedy) to 19 (nice)
# greedy takes and demand more CPU
# nice   gives and wait   for  CPU
nice="nice -n 19"

# detect wine
WINE=$(which wine)
[ "$WINE" ] || { echo "WINE (32-bit) not installed."; exit; }
WINE="$nice $WINE"

# look for 32-bit LIB for WINE
export    LD_LIBRARY_PATH=$HOME/opt/lib32:/usr/lib32:/lib32:"$PWD"
export LIBGL_DRIVERS_PATH=/usr/lib/i386-linux-gnu/dri:/usr/lib32/dri:/lib32/dri:"$PWD"

# wiki.winehq.org/Debug_Channels
#export WINEDEBUG="err+all,warn-all,fixme-all,trace-all"
#export WINEDEBUG="-all,err+all"
export WINEDEBUG="-all,err+module"

# wiki.winehq.org/Wine_User%27s_Guide#DLL_Overrides
dll="winemenubuilder.exe=d"   # Disable Desktop Icons
dll="$dll;mshtml=d"    # Disable Gecko
dll="$dll;mscoree=d"   # Disable Mono
dll="$dll;quartz=n,b"  # MPEG-1 system streams
dll="$dll;dsound=n,b"  # Touhou Vorbis DLL
#dll="$dll;wininet=d;winhttp=d" # Disconnect from Internet
export WINEDLLOVERRIDES="$dll"

# wiki.winehq.org/FAQ
export   WINEARCH="win32"
export WINEPREFIX="/tmp/$WINEARCH"
export       HOME="/tmp/home-dummy"

# wineconsole cmd.bat
# wine cmd /c cmd.bat
#winecmd="$nice wineconsole"
winecmd="$nice wine cmd /c"

# virtual desktop setting
  deskid=$(date +"%s")
desksize="900x600"
    desk="explorer /desktop=$WINEARCH-$deskid,$desksize"
########################################
function mouselock()
{
	echo "Mouse Lock"
	cat << _REG  > /tmp/wine.reg
REGEDIT4

[HKEY_CURRENT_USER\\Software\\Wine\\DirectInput]
"MouseWarpOverride"="force"

_REG
	regedit /tmp/mouselock.reg
	rm /tmp/mouselock.reg
}
########################################

if [ ! -d "$WINEPREFIX" ]; then
	echo "New HOME = $HOME"
	mkdir -p "$HOME"
	winecfg
	#fonts="$WINEPREFIX/drive_c/windows/Fonts/"
	#ln -s $(find /usr/share/fonts -iname "*.ttf")  "$fonts"
	#ln -s $(find /usr/share/wine  -iname "*.ttf")  "$fonts"
fi
if [ $# = 0 ]; then
	winecfg
	wineserver -k
	exit
fi

while [ "$1" ]; do
	t1="${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	case "$t1" in

		*".exe" | *".EXE")  $WINE $desk "$t1" "$@"; shift $#;;
		*".reg" | *".REG")  regedit "$t1";;
		*".msi" | *".MSI")  msiexec /i "$t1";;
		*".bat" | *".BAT")  $winecmd "$t1";;

		"-k" | "-kill")   wineserver -k;;
		"-h" | "-help")   $WINE --help;;
		"-V" | "-ver")    $WINE --version;;

		"-desk" | "-size" | "-s")
			desksize="$1"
			desk="explorer /desktop=wine-$deskid,$desksize"
			shift;;
		"-nodesk" | "-quiet" | "-q")
			desk="";;

		"-path")
			case "$1" in
				*'/'*)   winepath --windows "$1";; #  dos2unix
				*'\\'*)  winepath --unix    "$1";; # unix2dos
			esac
			shift;;

		"-cmd" | "-bat")     $winecmd "$@"; shift $#;;
		"-reg" | "regedit")  regedit;;
		"-txt" | "notepad")  notepad;;

		"-sjis")
			sjis="/usr/share/i18n/charmaps/SHIFT_JIS.gz"
			if [ -f "$sjis" ]; then
				mkdir -p /tmp/sjisdef
				localedef -c -f SHIFT_JIS -i ja_JP /tmp/sjisdef/ja_JP.SJIS
				export LOCPATH=/tmp/sjisdef
				export LANG=ja_JP.SJIS
			else
				echo "NOT FOUND : $sjis"
				echo "REQUIRED : locales_*_all.deb"
			fi
			;;

		"-cfg")     winecfg;;
		"-file")    winefile;;
		"-server")  wineserver;;
		"-boot")    wineboot -h;;
		"-msi")     msiexec /h;;
		"-uninst")  uninstaller;;
		"-ctrl")    control;;

		"-mouselock")   mouselock;;

		*)  $WINE $desk "$t1";;

	esac
done

