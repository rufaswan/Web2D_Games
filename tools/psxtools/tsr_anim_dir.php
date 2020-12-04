<?php
/*
[license]
[/license]
 */

function animtxt( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$anim = "$dir/anim.txt";
	if ( ! file_exists($anim) )
		return;

	foreach ( file($anim) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;

		list($sub,$seq) = explode('=', $line);
		@mkdir("$dir/$sub", 0755, true);

		foreach ( explode(',', $seq) as $k => $v )
		{
			list($no,$fps) = explode('-', $v);
			if ( $fps == 0 )
				continue;

			$frn = sprintf("$dir/%04d.png", $no);
			$ton = sprintf("$dir/$sub/%04d.png", $k);
			echo "COPY $frn -> $ton\n";
			copy($frn, $ton);
		} // foreach ( explode(',', $seq) as $k => $v )

	} // foreach ( file($anim) as $line )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	animtxt( $argv[$i] );
