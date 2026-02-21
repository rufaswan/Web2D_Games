<?php
require 'class-sh.inc';
sh::which('iconv');

$iconv = 'iconv'
	. ' --silent'
	. ' --from-code=utf-8'
	. '   --to-code=windows-31j//IGNORE'
	. ' <<< "%s"';

$json = "[\r\n";
// sjis is 3-byte utf-8
// from 800 to ffff
for ( $i=0x800; $i <= 0xffff; $i++ )
{
	$b1 = ($i >> 12) & 0x0f;  $b1 |= 0xe0;
	$b2 = ($i >>  6) & 0x3f;  $b2 |= 0x80;
	$b3 = ($i >>  0) & 0x3f;  $b3 |= 0x80;
	$utf = chr($b1) . chr($b2) . chr($b3);
	echo '.';

	$r = sh::exec($iconv, $utf);
	if ( empty($r) )
		continue;

	$json .= sprintf("  [ \"%s\" , \"%s\" ] ,\r\n", $utf, bin2hex($r));
} // for ( $i=0x800; $i <= 0xffff; $i++ )
echo "done\n";

$json .= "  0\r\n";
$json .= "]\r\n";
sh::save(__FILE__ .'.json', $json);


/*
echo    '\x41' = \x41
echo -e '\x41' = A
printf  '\x41' = A

// 0-9  824f-8258
// A-Z  8260-8279
// a-z  8281-829a
sjis 824f-8258 == full width 0-9
	== \uff10-\uff19

iconv -f sjis -t utf8 <<< $(printf '\x82\x50')
echo $? = 0
iconv -f sjis -t utf8 <<< $(printf '\x82\x5a')
echo $? = 1

sjis code
	81-9f  full width
		40-fc
	a1-df  half width
	e0-ef  full width
		40-fc
		ed40-eefc  nec/ibm extension
*/
