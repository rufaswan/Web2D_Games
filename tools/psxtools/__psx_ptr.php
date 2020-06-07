<?php
require "common.inc";

function ptr( $fname )
{
	return;
}

for ( $i=1; $i < $argc; $i++ )
	ptr( $argv[$i] );
