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

// MB = 1024 * 1024
// mb = 1000 * 1000
$gp_work = sh::xprop();
define('MB10', 1 * 0.001 * 0.001);
define('MIN' , 1.0 / 60);
//////////////////////////////
function sort_by_time( $a, $b )
{
	// by time DESC ,  then by size DESC
	if ( $b['time'] === $a['time'] )
		return $b['size'] - $a['size'];
	else
		return $b['time'] - $a['time'];
}

function calc_remain_timesize( &$list )
{
	$sum = array(
		'time' => 0.0,
		'size' => 0.0,
	);
	foreach ( $list as $ent )
	{
		$sum['time'] += $ent['time'];
		$sum['size'] += $ent['size'];
	}
	return $sum;
}

function mpvplay( &$list, $type )
{
	if ( empty($list) )
		return;
	usort($list, 'sort_by_time');

	global $gp_work;
	while ( ! empty($list) )
	{
		// paused when moved to different workspace
		while ( sh::xprop() !== $gp_work )
			sleep(1);

		// process list
		$sum = calc_remain_timesize($list);
		$rem = sprintf('[%d] (rem=%d min|%d mb)', count($list), $sum['time'] * MIN, $sum['size'] * MB10);

		$ent = array_shift($list);

		$tmp = sprintf('%s/meme/%s/%s', sys_get_temp_dir(), $type, $ent['name']);
		$tit = sprintf('%s (%d min|%d mb)', $ent['name'], $ent['time'] * MIN, $ent['size'] * MB10);

		$mpv = 'mpv'
			. ' --quiet'
			. ' --really-quiet'
			. ' --window-maximized'
			. ' --geometry="+0+0"'
			. ' --title="%s %s"'
			. ' --script-opts="osc-visibility=always"'
			. ' --af="loudnorm=I=-14:TP=-1"'
			. ' "%s"';
		sh::exec($mpv, $rem, $tit, $ent['name']);

		// already moved = do nothing
		echo "$tit\n";
		if ( ! file_exists($tmp) )
			sh::move($ent['name'], $tmp);

		sleep(1);
	} // while ( ! empty($list) )
	return;
}

$video = array();
$media = array();
for ( $i=1; $i < $argc; $i++ )
{
	if ( ! is_file($argv[$i]) )
		continue;
	echo '.';

	$t = array(
		'name' => $argv[$i],
		'size' => filesize($argv[$i]),
		'time' => -1,
	);
	if ( $t['name'][0] === '-' )
		$t['name'] = './' . $t['name'];

	$dur = sh::ffprobe($t['name']);
	if ( $dur['stream'] > 1.0 )
	{
		// has video stream
		$t['time'] = $dur['stream'];
		$video[] = $t;
		continue;
	}

	if ( $dur['format'] > 1.0 )
	{
		// general media file
		$t['time'] = $dur['format'];
		$media[] = $t;
		continue;
	}
} // for ( $i=1; $i < $argc; $i++ )
echo "\n";

//echo "video\n"; print_r($video);
//echo "media\n"; print_r($media);
mpvplay($video, 'video');
mpvplay($media, 'media');

/*
format=duration      == ss.sssssssss       , for both video and audio files
stream=duration      == ss.sssssssss       , with select_streams , N/A for *.webm *.mkv
stream_tags=duration == hh:mm:ss.sssssssss , with select_streams

return     format  stream:v  stream:a  tags:v  tags:a
non-media  ''      ''        ''        ''      ''
image      N/A     N/A       ''        ''      ''
video      1.0     1.0       ''        ''      ''
audio      1.0     ''        1.0       ''      ''
*.webm     1.0     N/A       N/A       0:00    0:00
 */
