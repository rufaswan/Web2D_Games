<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'class-sh.inc';
sh::which('xprop');
sh::which('ffprobe');
sh::which('mpv');
sh::which('sleep');

$gp_work = sh::xprop();
//////////////////////////////
function sort_by_time( $a, $b )
{
	// by time DESC ,  then by size DESC
	if ( $b['time'] === $a['time'] )
		return $b['size'] - $a['size'];
	else
		return $b['time'] - $a['time'];
}

function mpvplay( &$list, $type )
{
	if ( empty($list) )
		return;
	usort($list, 'sort_by_time');

	global $gp_work;
	$sh = sh::which('mpv');
	while ( ! empty($list) )
	{
		// paused when moved to different workspace
		while ( sh::xprop() !== $gp_work )
			sh::exec('sleep 0.5');

		// process list
		$ent = array_shift($list);

		$mib = $ent['size'] * 0.001 * 0.001;
		$tmp = sprintf('%s/meme/%s/%s', sys_get_temp_dir(), $type, $ent['name']);
		$tit = sprintf('[%d] %s (%d mib)', count($list), $ent['name'], $mib);

		$mpv = '%s'
			. ' --quiet'
			. ' --really-quiet'
			. ' --window-maximized'
			. ' --geometry="+0+0"'
			. ' --title="%s"'
			. ' --script-opts="osc-visibility=always"'
			. ' --af="loudnorm=I=-14:TP=-1"'
			. ' "%s"';
		echo "$tit\n";
		sh::exec($mpv, $sh, $tit, $ent['name']);
		sh::exec('sleep 0.5');

		if ( ! file_exists($tmp) )
			sh::move($ent['name'], $tmp);
	} // while ( ! empty($list) )
	return;
}

$media = array();
$video = array();
$audio = array();
$ffcmd = 'ffprobe'
	. ' -loglevel        quiet'
	. ' -select_streams  %s'
	. ' -show_entries    stream=duration'
	. ' -print_format    default=nokey=1:noprint_wrappers=1'
	. ' "%s"';
for ( $i=1; $i < $argc; $i++ )
{
	if ( ! is_file($argv[$i]) )
		continue;

	$t = array(
		'name' => $argv[$i],
		'size' => filesize($argv[$i]),
		'time' => -1,
	);
	if ( $t['name'][0] === '-' )
		$t['name'] = './' . $t['name'];

	$vdur = 10.0 * sh::exec($ffcmd, 'v:0', $t['name']);
	$adur = 10.0 * sh::exec($ffcmd, 'a:0', $t['name']);
	if ( $vdur > 1.0 && $adur > 1.0 )
	{
		$t['time'] = $vdur + $adur;
		$media[] = $t;
		continue;
	}

	if ( $vdur > 1.0 )
	{
		$t['time'] = $vdur;
		$video[] = $t;
		continue;
	}

	if ( $adur > 1.0 )
	{
		$t['time'] = $adur;
		$audio[] = $t;
		continue;
	}
} // for ( $i=1; $i < $argc; $i++ )

mpvplay($media, 'media');
mpvplay($video, 'video');
mpvplay($audio, 'audio');
