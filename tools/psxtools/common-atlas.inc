<?php
/*
[license]
[/license]
 */
// Accept a list of CLUT/RGBA data and create an atlas map

function sort_atlas_by_size( $a, $b )
{
	// sort
	if ( $a['h'] == $b['h'] )
	{
		$sa = $a['w'] * $a['h'];
		$sb = $b['w'] * $b['h'];
		// sort total pixels DESC
		return ( $sa < $sb );
	}
	else
		// sort height DESC
		return ( $a['h'] < $b['h'] );
}

function MAPTREE_DEF( $x, $y, $w, $h )
{
	return array(
		'child' => array('',''),
		'x' => $x,
		'y' => $y,
		'w' => $w + 2,
		'h' => $h + 2,
	);
}

function maptree_add( &$tree, &$img, $x, $y, $w, $h )
{
	// img has 1px padding
	if ( ($img['w']+2) > $w || ($img['h']+2) > $h )
		return array(-1,-1);

	if ( empty($tree) )
	{
		$tree = MAPTREE_DEF($x, $y, $img['w'], $img['h']);
		$img['x'] = $x + 1;
		$img['y'] = $y + 1;
		trace("add map %s [%x x %x] to %x , %x\n", $img['id'], $img['w'], $img['h'], $img['x'], $img['y']);
		return array($x+1, $y+1);
	}
	else
	{
		$func = __FUNCTION__;
		// child 0 is right direction
		list($rx,$ry) = $func($tree['child'][0], $img,
			$x + $tree['w'],
			$y,
			$w - $tree['w'],
			$tree['h']
		);
		// if success added
		if ( $rx != -1 && $ry != -1 )
			return array($x,$y);

		// child 1 is bottom direction
		return $func($tree['child'][1], $img,
			$x,
			$y + $tree['h'],
			$w,
			$h - $tree['h']
		);
	}

	return array(-1,-1);
}

function atlasmap( &$list )
{
	usort($list, "sort_atlas_by_size");
	$cw = 1;
	$ch = 1;

	$tree = '';
	$is_done = false;
	while ( ! $is_done )
	{
		$tree = '';
		$is_done = true;
		foreach ( $list as $k => $v )
		{
			list($rx,$ry) = maptree_add($tree, $list[$k], 0, 0, $cw, $ch);
			if ( $rx == -1 || $ry == -1 )
			{
				$is_done = false;
				break;
			}

		} // foreach ( $list as $k => $v )

		if ( ! $is_done )
		{
			$tree = '';
			if ( $cw > $ch )
				$ch <<= 1;
			else
				$cw <<= 1;
			trace("== atlas resize %x x %x\n", $cw, $ch);
		}
	} // while ( ! $is_done )

	$ind = array();
	foreach ( $list as $k => $v )
		$ind[ $v['id'] ] = $k;

	trace("atlasmap() = %x x %x\n", $cw, $ch);
	return array($ind, $cw, $ch);
}
