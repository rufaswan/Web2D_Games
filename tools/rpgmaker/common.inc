<?php
require "../psxtools/common.inc";

function intv( &$file, &$st )
{
	$ret = 0;
	while(1)
	{
		$b1 = ord( $file[$st] );
			$st++;

		$ret <<= 7;
		$ret += ($b1 & 0x7f);

		if ( $b1 & 0x80 )
			continue;
		return $ret;
	}
	return $ret;
}
