<?php
declare( strict_types=1 );

require 'tool.inc';

function testdir( string $dir ) : void
{
	if ( ! is_dir($dir) )
		return;
	$real = realpath($dir);
	tool::trace('real', $real);

	$list = [];
	tool::scan($list, $real);

	$base = tool::real2rel_path($list);
	tool::trace('base', $base);
	print_r($list);
}

for ( $i=1; $i < $argc; $i++ )
	testdir( $argv[$i] );
