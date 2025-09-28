#!/bin/bash
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
		let w=${dim[7]}*9/10
		let h=${dim[9]}*9/10
	fi

	printf '%dx%d'  $w  $h
}
function w32_mouselock {
	local reg='/tmp/mouselock.reg'
	echo 'Mouse Lock'
	cat << _REG  > $reg
REGEDIT4

[HKEY_CURRENT_USER\\Software\\Wine\\DirectInput]
"MouseWarpOverride"="force"

_REG
	regedit  $reg
	rm       $reg
}
function w32_langloc {
	local sjis="/usr/share/i18n/charmaps/$1.gz"
	if [ -f "$sjis" ]; then
		mkdir -p '/tmp/sjisdef'
		# -c  --force
		# -f  --charmap=FILE
		# -i  --inputfile=FILE
		localedef             \
			--force           \
			--charmap=$1      \
			--inputfile=ja_JP \
			"/tmp/sjisdef/ja_JP.$1"
		export LOCPATH="/tmp/sjisdef"
		export    LANG="ja_JP.$1"
	else
		echo "NOT FOUND : $sjis"
		echo "REQUIRED  : locales_*_all.deb"
	fi
}
function w32_symblink {
	[ -e "$1" ] || return 1  # no target
	[ -e "$2" ] && return 1  # existed
	ln -s  "$1"  "$2"
}
########################################
# Nice-scale goes from -20 (greedy) to 19 (nice)
# greedy takes and demand more CPU
# nice   gives and wait   for  CPU
[ $(which wine) ] || { echo 'WINE (32-bit) not installed.'; exit; }
nice='nice -n 19'

 BAK_LIB="$LD_LIBRARY_PATH"
  BAK_GL="$LIBGL_DRIVERS_PATH"
BAK_HOME="$HOME"

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
export WINEPREFIX="/tmp/$WINEARCH"
export       HOME="/tmp/home-$WINEARCH"

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
	mkdir -p "$HOME"
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
				'exe' | 'EXE')  $nice  wine  $winedbg  $desk  "$t1" "$@"; shift $#;;
				'bat' | 'BAT')  $nice  wine  $winecmd         "$t1" "$@"; shift $#;;
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

		[[ "${t1:0:1}" == '-' ]] && t1="${t1:1}"

		# parse options
		case "$t1" in
			'k' | 'kill')   wineserver -k;;
			'h' | 'help')   wine --help;;
			'V' | 'ver' )   wine --version;;

			'sjis' )  w32_langloc  SHIFT_JIS;;
			'ms932')  w32_langloc  WINDOWS-31J;;
			'euc'  )  w32_langloc  EUC-JP;;
			'dbg'  )  winedbg='winedbg';;
			'desk' )  desk='';;

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

export    LD_LIBRARY_PATH="$BAK_LIB"
export LIBGL_DRIVERS_PATH="$BAK_GL"
export               HOME="$BAK_HOME"
