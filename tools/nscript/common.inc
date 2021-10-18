<?php
require "../psxtools/common.inc";

function str2big( &$str, $pos, $byte )
{
	$s = substr($str, $pos, $byte);
	$s = strrev($s);
	return ordint($s);
}

