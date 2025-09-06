#!/bin/bash
[ $(which ps) ] || exit

echo "start $0"
function cpu95kill {
	local cpu=${1%.*}  # float to int
	local mem=${2%.*}  # float to int
	local pid=$3
	local cmd=${4##*/}
	#echo "CPU = $cpu , MEM = $mem , PID = $pid , CMD = $cmd"

	# whitelist
	[[ "$cmd" == 'ffmpeg' ]] && return

	# [BUG] firefox-bin
	#   comm              cmd
	#   firefox-bin       ./firefox-bin
	#   Socket Process    $path/firefox-bin -contentproc ... socket
	#   Priviledged Cont  $path/firefox-bin -contentproc -childID 1 -isForBrowser ... tab
	#   WebExtensions     $path/firefox-bin -contentproc -childID 2 -isForBrowser ... tab
	#   Isolated Web Co   $path/firefox-bin -contentproc -childID 3 -isForBrowser ... tab
	#   Web Content       $path/firefox-bin -contentproc -childID 4 -isForBrowser ... tab
	#
	# = kill both process (by pid) and parent (by command)
	if (( $cpu > 95 )); then
		kill    -15 $pid
		killall -15 $cmd
		echo "[cpu > 95] $@"
		return
	fi
	if (( $mem > 95 )); then
		kill    -15 $pid
		killall -15 $cmd
		echo "[mem > 95] $@"
		return
	fi
}
export -f cpu95kill

ps=(
	ps
	-A
	--no-headers
	--format %cpu,%mem,pid,cmd
	--sort   %cpu
)
while [ '1' ]; do
	cpu95kill  $(${ps[@]} | tail -1)
	sleep 30
done

<<'////'
ps -C <command> --no-headers --format %cpu,%mem,pid,cmd,comm
ps -p <pid>     --no-headers --format %cpu,%mem,pid,cmd,comm
pidof <command>
////
