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
declare( strict_types=1 );

define('CHUNK_SIZE', 150 << 20);
class file_ent
{
	public $path = '';
	public $size = -1;
	public $add = [0,0,0,0,0,0,0,0];
	public $xor = [0,0,0,0,0,0,0,0];
	public $is_link = false;
} // class file_ent

function cmp_ent( file_ent &$a,  file_ent &$b ) : bool
{
	// if $a or $b already converted to link
	if ( $a->is_link )  return false;
	if ( $b->is_link )  return false;

	if ( $a->size !== $b->size )
		return false;
	for ( $i=0; $i < 8; $i++ )
	{
		if ( $a->add[$i] !== $b->add[$i] )
			return false;
		if ( $a->xor[$i] !== $b->xor[$i] )
			return false;
	} // for ( $i=0; $i < 8; $i++ )

	return true;
}

function make_rel_link( file_ent &$a,  file_ent &$b ) : void
{
	// $b is a duplicate
	// delete $b and make a relative link to $a
	printf("[dup] %s -> %s\n", $a->path, $b->path);

	// $a = /dir/sub1/a.bin
	// $b = /dir/sub2/b.bin
	// ln -s  ../sub1/a.bin  b.bin
	$ap = explode('/', $a->path);
	$bp = explode('/', $b->path);
	while ( $ap[0] === $bp[0] )
	{
		array_shift($ap);
		array_shift($bp);
	} // while ( $ap[0] === $bp[0] )

	$rel = [];
	while ( count($bp) >= 2 )
	{
		array_shift($bp);
		$rel[] = '..';
	} // while ( count($bp) > 0 )
	if ( empty($rel) )
		$rel[] = '.';

	while ( ! empty($ap) )
		$rel[] = array_shift($ap);

	$path = implode('/', $rel);
	unlink($b->path);
	symlink($path, $b->path);
	$b->is_link = true;
}
//////////////////////////////
function cmplist_link( array &$list ) : void
{
	while ( count($list) > 1 )
	{
		$a = array_shift($list);
		foreach ( $list as $b )
		{
			if ( ! cmp_ent($a, $b) )
				continue;
			make_rel_link($a, $b);
		} // foreach ( $list as $b )
	} // while ( count($list) > 1 )
}

function cmplink( array &$argv ) : void
{
	$list = [];
	$argc = count($argv);
	for ( $i=1; $i < $argc; $i++ )
	{
		if ( is_link($argv[$i]) )
			continue;
		if ( ! is_file($argv[$i]) )
			continue;

		$ent = new file_ent;
		$ent->path = str_replace('\\', '/', realpath($argv[$i]));
		$ent->size = filesize($argv[$i]);

		$fp  = fopen($argv[$i], 'rb');
		$pos = 0;
		while ( $pos < $ent->size )
		{
			fseek($fp, $pos, SEEK_SET);
			$sub = fread($fp, CHUNK_SIZE);
				$pos += CHUNK_SIZE;

			$len = strlen($sub);
			for ( $j=0; $j < $len; $j++ )
			{
				$c = ord($sub[$j]);
				$n = $j & 7;
				$ent->add[$n] += $c;  $ent->add[$n] &= 0xff;
				$ent->xor[$n] ^= $c;
			} // for ( $i=0; $i < $len; $i++ )
		} // while ( $pos < $ent->size )
		fclose($fp);

		$list[] = $ent;
		echo '.';
	} // for ( $i=1; $i < $argc; $i++ )
	echo "list\n";

	cmplist_link($list);
}
cmplink($argv);

/*
function cmpfile( &$list, $act )
{
	foreach ( $list as $lk => $lv )
	{
		$cnt = count($lv);
		if ( $cnt < 2 )
			continue;

		for ( $i=0; $i < ($cnt-1); $i++ )
		{
			if ( ! file_exists($lv[$i]) )
				continue;
			if ( is_link($lv[$i]) )
				continue;

			for ( $j=$i+1; $j < $cnt; $j++ )
			{
				if ( ! file_exists($lv[$j]) )
					continue;
				if ( is_link($lv[$j]) )
					continue;

				$b1 = file_get_contents($lv[$i]);
				$b2 = file_get_contents($lv[$j]);
				if ( $b1 == $b2 )
				{
					printf("SAME %s == %s\n", $lv[$i], $lv[$j]);
					switch ( $act )
					{
						case 'rm':
							unlink ($lv[$j]);
							break;
						case 'ln':
							unlink ($lv[$j]);
							symlink($lv[$i], $lv[$j]);
							break;
					} // switch ( $act )
				}
				else
					echo "-\n";
					//printf("DIFF %s != %s\n", $lv[$i], $lv[$j]);
			} // for ( $j=$i+1; $j < $cnt; $j++ )
		} // for ( $i=0; $i < ($cnt-1); $i++ )
	} // foreach ( $list as $lk => $lv )

	return;
}

printf("%s  [-rm/-ln]  FILE  FILE...\n", $argv[0]);
if ( $argc < 3 )  exit();

$list = [];
$act  = '';
for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt == '-rm' )  { $act = 'rm'; continue; }
	if ( $opt == '-ln' )  { $act = 'ln'; continue; }
	if ( is_dir ($opt) )  continue;
	if ( is_link($opt) )  continue;

	$sz = filesize($opt);
	if ( ! isset($list[$sz]) )
		$list[$sz] = [];
	$list[$sz][] = $opt;
}
cmpfile($list, $act);
*/
