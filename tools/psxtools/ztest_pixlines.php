<?php
require 'common.inc';
require 'class-pixlines.inc';

function pjoint( &$pix, $pos )
{
	if ( strpos($pos, ',') === false )
		return;

	$pos = explode(',', $pos);
	switch ( count($pos) )
	{
		case 2: // x,y
			return $pix->addpoint($pos);
		case 4: // x1,y1,x2,y2
			return $pix->addline($pos);
		case 8: // x1,y1,x2,y2,x3,y3,x4,y4
			return $pix->addquad($pos);
	} // switch ( count($pos) )
	return;
}

$pix = new PixLines;

$pix->new();
for ( $i=1; $i < $argc; $i++ )
	pjoint( $pix, $argv[$i] );

$img = $pix->draw();
save_clutfile('pixlines.clut', $img);

/*
116,-109,77,-41,86,-71,51,-136
70,-49,254,-33,101,-116,95,-57
*/
