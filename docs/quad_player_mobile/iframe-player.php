<?php
if ( empty($_GET) )
	exit();
//////////////////////////////
function json_files( &$list, $dir, $preg )
{
	$func = __FUNCTION__;
	foreach ( scandir(__DIR__ . "/$dir") as $fn )
	{
		if ( $fn[0] === '.' )
			continue;
		$full = __DIR__ . "/$dir/$fn";

		if ( is_dir($full) )
		{
			$func($list, "$dir/$fn", $preg);
			continue;
		}

		if ( is_file($full) )
		{
			if ( preg_match($preg, $fn) )
				$list[] = "$dir/$fn";
			continue;
		}
	}
	return;
}

if ( isset($_GET['quad']) )
{
	$list = array();
	json_files($list, '.', '|\.quad$|i');
	echo json_encode($list);
	exit();
}
if ( isset($_GET['png']) )
{
	$list = array();
	json_files($list, '.', '|\.png$|i');
	echo json_encode($list);
	exit();
}
//////////////////////////////
