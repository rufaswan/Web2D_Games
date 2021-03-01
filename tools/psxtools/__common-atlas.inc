<?php
/*
[license]
[/license]
 */
// Accept a list of CLUT/RGBA data and create an atlas map

function atlasmap( $list )
{
	$size = array();
	$sum  = 0;
	foreach ( $list as $k => $v )
	{
		$s = $v['w'] * $v['h'];
		if ( $s <= strlen($v['pix']) && ! isset($v['pal']) )
		{
			php_warning("atlasmap() list[%d] is CLUT without palette", $k);
			$list[$k]['cc']  = 0x100;
			$list[$k]['pal'] = grayclut(0x100);
		}

		if ( ! isset($size[$s]) )
			$size[$s] = array();
		$size[$s][] = $k;
		$sum += $s;
	}
	ksort($size);
	$ceil = int_ceil_pow2( sqrt($sum) );
	trace("atlasmap() , sum %x pixels , ceil %x x %x", $ceil);
	$w = $ceil;
	$h = $ceil;

	return;
}
