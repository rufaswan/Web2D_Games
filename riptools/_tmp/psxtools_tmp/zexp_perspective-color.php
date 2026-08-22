<?php
// https://www.scratchapixel.com/lessons/3d-basic-rendering/rasterization-practical-implementation/perspective-correct-interpolation-vertex-attributes.html

require 'common.inc';

function edgefunc( $a, $b, $c )
{
	$b1 = ($c[0] - $a[0]) * ($b[1] - $a[1]);
	$b2 = ($c[1] - $a[1]) * ($b[0] - $a[0]);
	return $b1 - $b2;
}

function tri_div_z( &$a )
{
	$a[0] /= $a[2];
	$a[1] /= $a[2];
	return;
}

function tri_space( &$a, $w, $h )
{
	$a[0] = (1 + $a[0]) * 0.5 * $w;
	$a[1] = (1 + $a[1]) * 0.5 * $h;
	return;
}

function fog_div_z( &$a, $z )
{
	$a[0] /= $z;
	$a[1] /= $z;
	$a[2] /= $z;
	return;
}

function perspcolor( $perp, $canv, $tri, $fog )
{
	// project triangle onto the screen
	tri_div_z($tri[0]);
	tri_div_z($tri[1]);
	tri_div_z($tri[2]);
	// convert from screen space to NDC then raster (in one go)
	// normalized device coordinates (NDC)
	tri_space($tri[0] , $canv[0] , $canv[1]);
	tri_space($tri[1] , $canv[0] , $canv[1]);
	tri_space($tri[2] , $canv[0] , $canv[1]);

	if ( $perp )
	{
		// divide vertex-attribute by the vertex z-coordinate
		fog_div_z($fog[0] , $tri[0][2]);
		fog_div_z($fog[1] , $tri[1][2]);
		fog_div_z($fog[2] , $tri[2][2]);
		// pre-compute 1 over z
		$tri[0][2] = 1.0 / $tri[0][2];
		$tri[1][2] = 1.0 / $tri[1][2];
		$tri[2][2] = 1.0 / $tri[2][2];
	}

	$img = [
		'w'   => $canv[0],
		'h'   => $canv[1],
		'pix' => canvpix($canv[0] , $canv[1] , PIX_BLACK),
	];
	$area = edgefunc($tri[0] , $tri[1] , $tri[2]);

	for ( $y=0; $y < $canv[1]; $y++ )
	{
		for ( $x=0; $x < $canv[0]; $x++ )
		{
			$p = [
				$x + 0.5 ,
				$canv[1] - $y + 0.5 ,
				0
			];
			$w0 = edgefunc($tri[1] , $tri[2], $p);
			$w1 = edgefunc($tri[2] , $tri[0], $p);
			$w2 = edgefunc($tri[0] , $tri[1], $p);
			if ( $w0 >= 0 && $w1 >= 0 && $w2 >= 0 )
			{
				$w0 /= $area;
				$w1 /= $area;
				$w2 /= $area;
				$r = ($w0 * $fog[0][0]) + ($w1 * $fog[1][0]) + ($w2 * $fog[2][0]);
				$g = ($w0 * $fog[0][1]) + ($w1 * $fog[1][1]) + ($w2 * $fog[2][1]);
				$b = ($w0 * $fog[0][2]) + ($w1 * $fog[1][2]) + ($w2 * $fog[2][2]);
				if ( $perp )
				{
					$a = ($w0 * $tri[0][2]) + ($w1 * $tri[1][2]) + ($w2 * $tri[2][2]);
					$z = 1.0 / $a;
					// if we use perspective correct interpolation we need to
					// multiply the result of this interpolation by z, the depth
					// of the point on the 3D triangle that the pixel overlaps.
					$r *= $z;
					$g *= $z;
					$b *= $z;
				}

				$r = int_clamp($r*255, 0, 255);
				$g = int_clamp($g*255, 0, 255);
				$b = int_clamp($b*255, 0, 255);

				$dyy = $y * $canv[0];
				$dxx = ($dyy + $x) * 4;
				$img['pix'][$dxx+0] = chr($r);
				$img['pix'][$dxx+1] = chr($g);
				$img['pix'][$dxx+2] = chr($b);
			}
		} // for ( $x=0; $x < $canv[0]; $x++ )
	} // for ( $y=0; $y < $canv[1]; $y++ )

	$fn = sprintf('persp-%d.rgba', $perp);
	save_clutfile($fn, $img);
	return;
}

$tri = [
	[ 13, 34,114],
	[ 29,-15, 44],
	[-48,-10, 82],
];
$fog = [
	[0,0,1],
	[0,1,0],
	[1,0,0],
];
$canv = [512,512];

$perp = ( $argc === 1 ) ? 0 : 1;
perspcolor($perp, $canv, $tri, $fog);
