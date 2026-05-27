<?php

function del_inline( &$line, $char )
{
	$cnt = strlen($char);
	while (1)
	{
		$p1 = strpos($line, $char);
		if ( $p1 === false )
			return;

		$p2 = strpos($line, $char, $p1+$cnt);
		if ( $p2 === false )
			return;

		$sub  = substr($line, 0, $p1);
		$sub .= substr($line, $p2+$cnt);
		$line = $sub;
	}
	return;
}

function mdfix( $fn )
{
	if ( strpos($fn,'.md') === false )
		return;

	$file = file($fn, FILE_IGNORE_NEW_LINES);
	if ( empty($file) )
		return;
	echo "= $fn\n";

	$ed = count($file);
	$st = 0;
	while ( $st < $ed )
	{
		$line = $file[$st];
			$st++;

		if ( empty($line) )
			continue;

		// code block
		if ( $line === '```' )
		{
			while ( $file[$st] != '```' )
				$st++;
			$st++;
			continue;
		}

		// hr -> h1
		if ( substr($line,0,3) === '---' )
		{
			if ( ! empty($file[$st-2]) )
				printf('%d  --- become h1'."\n", $st);
			continue;
		}

		// remove url
		while ( preg_match('|\[[^\]]+\]\(http[^\)]+\)|', $line, $m) )
			$line = str_replace($m[0], '', $line);

		del_inline($line, '`');
		del_inline($line, '$$');
		del_inline($line, '$');

		//echo $st .' = '. $line . "\n";
		$word = explode(' ', $line);
		foreach ( $word as $w )
		{
			if ( strpos($w,'_') !== false )
				printf('%d  %s'."\n", $st, $w);
			//if ( strpos($w,'()') !== false )
				//printf('%d  %s'."\n", $st, $w);
		} // foreach ( $word as $w )
	} // while ( $st < $ed )
}

foreach ( scandir(__DIR__) as $e )
{
	if ( $e[0] === '.' )
		continue;
	mdfix($e);
}
