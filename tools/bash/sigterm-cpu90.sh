#!/bin/bash
[ $(which pidof) ] || exit
[ $(which ps   ) ] || exit

core=$(cat /proc/cpuinfo | grep processor | wc -l)
let MAXCPU=$core*90
echo "MAXCPU = $MAXCPU"
export MAXCPU

function cpu90kill {
	local cpu=${1%.*}  # float to int
	local mem=${2%.*}  # float to int
	local pid=$3
	local com=$4
	#echo "CPU = $cpu , MEM = $mem , PID = $pid , COMM = $com"

	# CPU MEM
	#   0   0  skip
	#   0 100  kill
	# 100   0  kill
	# 100 100  kill
	if (( $cpu < $MAXCPU && $mem < 90 )); then
		return
	fi

	# [BUG] firefox-bin
	#   comm              cmd
	#   firefox-bin       ./firefox-bin
	#   Socket Process    $path/firefox-bin -contentproc ... socket
	#   Priviledged Cont  $path/firefox-bin -contentproc -childID 1 -isForBrowser ... tab
	#   WebExtensions     $path/firefox-bin -contentproc -childID 2 -isForBrowser ... tab
	#   Isolated Web Co   $path/firefox-bin -contentproc -childID 3 -isForBrowser ... tab
	#   Web Content       $path/firefox-bin -contentproc -childID 4 -isForBrowser ... tab
	echo "[cpu90kill] $@"
	kill    -15 $pid
	killall -15 $com
}
export -f cpu90kill

ps=(
	ps
	-A
	--no-headers
	--format %cpu,%mem,pid,comm
	--sort   %cpu
)
while [ '1' ]; do
	cpu90kill  $(${ps[@]} | tail -1)
	sleep 15
done

<<'////'
ps -C <command> --no-headers --format %cpu,%mem,pid,cmd,comm
ps -p <pid>     --no-headers --format %cpu,%mem,pid,cmd,comm
pidof <command>
////
