<?php
function deb2pool( $dir, $pool )
{
	$dir  = rtrim($dir, '/\\');
	$pool = file($pool);
	if ( empty($pool) )
		return;

	$txt = '';
	foreach ( scandir($dir) as $deb )
	{
		if ( $deb[0] === '.' )
			continue;

		$done = false;
		foreach ( $pool as $url )
		{
			// Filename: pool/main/a/aalib/libaa1_1.4p5-41_i386.deb
			if ( strpos($url, $deb) !== false )
			{
				$txt .= substr($url, 10);
				$done = true;
				break;
			}
		}

		if ( ! $done )
			$txt .= "ERROR $deb\n";
	}

	echo $txt;
	file_put_contents("$dir.txt", $txt);
	return;
}

$err = <<<_ERR
{$argv[0]}  DEB_DIR  POOL.TXT

_ERR;

if ( $argc != 3 )  exit($err);
if ( ! is_dir ($argv[1]) )  exit($err);
if ( ! is_file($argv[2]) )  exit($err);
deb2pool($argv[1], $argv[2]);
