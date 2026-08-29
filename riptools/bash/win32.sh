#!/bin/bash
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$
free=$(free -b | awk '/^Mem:/{print $2}')
prlimit  --rss=$(($free/8)):$(($free/4))  --pid $$
########################################
# !! This script is for 32-bit only !!
#
# https://forum.winehq.org/viewtopic.php?t=24127
# 32-bit prefixes are for 32-bit programs and should only be used with 32-bit wine.
# 64-bit prefixes are for 64-bit programs and should only be used with 64-bit wine.
#
# /bin/wine   = for 32-bit EXE + DLL only
# /bin/wine64 = for 64-bit EXE + DLL only
########################################
function w32_vdesksize {
	local dim=( $(xrandr | grep current | tr -c '[0-9a-zA-Z]' ' ') )
	local w=300
	local h=200

	# Screen 0  minimum 320 x 240  current 1024 x 768  maximum 4096 x 4096
	if [[ ${dim[6]} == 'current' ]]; then
		let w=${dim[7]}*3/4
		let h=${dim[9]}*3/4
	fi

	printf '%dx%d'  $w  $h
}
function w32_mouselock {
	local reg="$HOME/mouselock.reg"
	echo 'Mouse Lock'
	cat << _REG  > "$reg"
REGEDIT4

[HKEY_CURRENT_USER\\Software\\Wine\\DirectInput]
"MouseWarpOverride"="force"

_REG
	regedit  "$reg"
}
function w32_langloc {
	local sjis="/usr/share/i18n/charmaps/$1.gz"
	if [ -f "$sjis" ]; then
		mkdir -p "$HOME/sjisdef"
		# -c  --force
		# -f  --charmap=FILE
		# -i  --inputfile=FILE
		localedef             \
			--force           \
			--charmap=$1      \
			--inputfile=ja_JP \
			"$HOME/sjisdef/ja_JP.$1"
		export LOCPATH="$HOME/sjisdef"
		export    LANG="ja_JP.$1"
	else
		echo "NOT FOUND : $sjis"
		echo "REQUIRED  : locales_\*_all.deb"
	fi
}
function w32_symblink {
	[ -e "$1" ] || return 1  # no target
	[ -e "$2" ] && return 1  # existed
	ln -s  "$1"  "$2"
}
########################################
[ $(which wine) ] || { echo 'WINE (32-bit) not installed.'; exit; }

# look for 32-bit LIB for WINE
export    LD_LIBRARY_PATH=$HOME/opt/lib32:/usr/lib32:/lib32:"$PWD"
export LIBGL_DRIVERS_PATH=/usr/lib/i386-linux-gnu/dri:/usr/lib32/dri:/lib32/dri:"$PWD"

# wiki.winehq.org/Debug_Channels
#export WINEDEBUG='err+all,warn-all,fixme-all,trace-all'
#export WINEDEBUG='-all,err+all'
export WINEDEBUG='-all,err+module'

# wiki.winehq.org/Wine_User%27s_Guide#DLL_Overrides
dll=(
	winemenubuilder.exe=d
	mshtml=d    # Disable Gecko
	mscoree=d   # Disable Mono
	quartz=d    # MPEG-1 system streams
	dsound=n,b  # Touhou Vorbis DLL
	#wininet=d  # Disconnect from Internet
	#winhttp=d  # Disconnect from Internet
)
#export WINEDLLOVERRIDES=$(tr ' ' \; <<< "${dll[*]}")
export WINEDLLOVERRIDES=$(IFS=\;; echo "${dll[*]}")

# wiki.winehq.org/FAQ
export   WINEARCH='win32'
export       HOME="/tmp/$WINEARCH-home"
export WINEPREFIX="$HOME/prefix"

# wine  wineconsole  cmd.bat
# wine  cmd /c       cmd.bat
#winecmd='wineconsole'
winecmd='cmd /c'

# is gdb debug
winedbg=''

# virtual desktop setting
  deskid=$(date +%s)
desksize=$(w32_vdesksize)
    desk="explorer /desktop=$WINEARCH-$deskid,$desksize"
########################################
if [ ! -d "$WINEPREFIX" ]; then
	echo "New HOME = $HOME"
	mkdir -p $WINEPREFIX
	winecfg
fi
USER=$(whoami)
w32_symblink  "$WINEPREFIX/drive_c/users/$USER/Local Settings/Application Data"  "$HOME/appdata_xp"
w32_symblink  "$WINEPREFIX/drive_c/users/$USER/AppData"                          "$HOME/appdata_vista"

if [ $# = 0 ]; then
	echo "            HOME : $HOME"
	echo "       WINEDEBUG : $WINEDEBUG"
	echo "WINEDLLOVERRIDES : $WINEDLLOVERRIDES"
	winecfg
	wineserver -k
else
	while [ "$1" ]; do
		t1="${1%/}"
		shift

		# handle file
		if [ -f "$t1" ]; then
			BAKDIR="$PWD"
			cd "$(dirname -- "$t1")"

			# sp handle = ./-filename.exe
			t1=./"$(basename -- "$t1")"

			tit="${t1%.*}"
			ext="${t1##*.}"
			case "$ext" in
				'exe' | 'EXE')  wine  $winedbg  $desk  "$t1" "$@"; shift $#;;
				'bat' | 'BAT')  wine  $winecmd         "$t1" "$@"; shift $#;;
				'reg' | 'REG')  regedit     "$t1";;
				'msi' | 'MSI')  msiexec /i  "$t1";;
			esac

			cd "$BAKDIR"
			continue
		fi

		# update virtual desktop size
		if [ $(grep [0-9]x[0-9] <<< "$t1")]; then
			desk="explorer /desktop=$WINEARCH-$deskid,$t1"
			continue
		fi

		# left-trim -
		# so -h -help h help are the same
		[[ "${t1:0:1}" == '-' ]] && t1="${t1:1}"

		# parse options
		case "$t1" in
			'k' | 'kill')   wineserver -k;;
			'h' | 'help')   wine --help;;
			'V' | 'ver' )   wine --version;;

			# SHIFT_JIS
			# WINDOWS-31J
			# SHIFT_JISX0213
			# EUC-JP
			# EUC-JP-MS
			# EUC-JISX0213
			# UTF-8
			'jp' | 'sjis')  w32_langloc  WINDOWS-31J;;
			'eucjp'      )  w32_langloc  EUC-JP-MS;;

			'dbg'  )  winedbg='winedbg';;
			'x'    )  desk='';;

			'reg' | 'regedit')  regedit;;
			'txt' | 'notepad')  notepad;;
			'cfg'   )  winecfg;;
			'file'  )  winefile;;
			'server')  wineserver;;
			'boot'  )  wineboot -h;;
			'msi'   )  msiexec /h;;
			'uninst')  uninstaller;;
			'ctrl'  )  control;;

			'path')
				case "$1" in
					*'/'*)   winepath --windows "$1";; #  dos2unix
					*'\\'*)  winepath --unix    "$1";; # unix2dos
				esac
				shift;;

			'mouselock')   w32_mouselock;;

			*)  shift $#;;
		esac
	done
fi
