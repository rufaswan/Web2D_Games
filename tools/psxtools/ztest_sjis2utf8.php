<?php
require 'common.inc';
require 'common-guest.inc';

// 0-9  8250-8258
// A-Z  8260-8279
// a-z  8281-829a

$txt = '';
for ( $b1 = 0x80; $b1 < 0x100; $b1++ )
{
	$by1 = chr($b1);
	if ( $b1 < 0xa1 || $b1 > 0xdf )
	{
		for ( $b2 = 0x40; $b2 < 0x100; $b2++ )
		{
			$by2 = chr($b2);
			$sjis = $by1 . $by2;

			// 0=OK , 1=ERROR
			$utf8 = php_exec(0, 'echo "%s" | iconv  --silent  -f cp932 -t utf8  -', $sjis);
			if ( empty($utf8) )
				continue;

			//$cmd = sprintf('echo "%s" | iconv  --silent  -f cp932 -t ascii//TRANSLIT  -', $sjis);
			$log = sprintf('%s = %s // %s', bin2hex($sjis), utf8_binhex($utf8), $utf8);
			$txt .= "$log\n";
		} // for ( $b2 = 0x40; $b2 < 0x100; $b2++ )
	}
	else
	{
		// 0=OK , 1=ERROR
		$utf8 = php_exec(0, 'echo "%s" | iconv  --silent  -f cp932 -t utf8  -', $by1);
		if ( empty($utf8) )
			continue;

		$log = sprintf('%s = %s // %s', bin2hex($by1), utf8_binhex($utf8), $utf8);
		$txt .= "$log\n";
	}
} // for ( $b1 = 0x80; $b1 < 0x100; $b1++ )

file_put_contents('sjis2utf8.txt', $txt);
