<?php
declare( strict_types=1 );

require 'tool.inc';

function usage( array &$option ) : void
{
	print_r($option);
}

tool::usage($argv, 'usage');

/**
 * @desc To test tool::usage()
 *
 * @type  STRING  $genre
 *   none
 *   action
 *   adventure
 *   roleplay
 *   novel
 *   simulation
 *
 * @arg   $genre  $list
 * @arg   FILE    $input
 */
