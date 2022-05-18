<?php
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/index.php
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/bilinearwarp
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/BilinearImageWarping2.pdf
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/FourCornerImageWarp2.pdf

function bilinear( $name, $dst, $nx, $ny )
{
	echo "\n$name\n";
	list($x0,$y0,$x1,$y1,$x2,$y2,$x3,$y3) = $dst;

	// x = a0 + a1*u + a2*v + a3*u*v
	// y = b0 + b1*u + b2*v + b3*u*v

	// corner[0] 0,0 => 52,0
	//   52 = a0 + a1*0 + a2*0 + a3*0*0
	//   52 = a0
	//
	//    0 = b0 + b1*0 + b2*0 + b3*0*0
	//    0 = b0
	$a0 = $x0;
	$b0 = $y0;

	// corner[1] 100,0 => 228,46
	//   228    = a0 + a1*100 + a2*0 + a3*100*0
	//   228    = a0 + a1*100
	//   a1*100 = 228 - a0
	//   a1     = (228 - a0) / 100
	//
	//    46    = b0 + b1*100 + b2*0 + b3*100*0
	//    46    = b0 + b1*100
	//   b1*100 = 46 - b0
	//   b1     = (46 - b0) / 100
	$a1 = ($x1 - $a0) / $nx;
	$b1 = ($y1 - $b0) / $nx;

	// corner[3] 0,200 => 0,246
	//     0    = a0 + a1*0 + a2*200 + a3*0*200
	//     0    = a0 + a2*200
	//   a2*200 = 0 - a0
	//   a2     = (0 - a0) / 200
	//
	//   246    = b0 + b1*0 + b2*200 + b3*0*200
	//   246    = b0 + b2*200
	//   b2*200 = 246 - b0
	//   b2     = (246 - b0) / 200
	$a2 = ($x3 - $a0) / $ny;
	$b2 = ($y3 - $b0) / $ny;

	// corner[2] 100,200 => 255,229
	//   255        = a0 + a1*100 + a2*200 + a3*100*200
	//   a3*100*200 = 255 - (a0 + a1*100 + a2*200)
	//   a3         = (255 - (a0 + a1*100 + a2*200)) / (100*200)
	//
	//   229        = b0 + b1*100 + b2*200 + b3*100*200
	//   b3*100*200 = 229 - (b0 + b1*100 + b2*200)
	//   b3         = (229 - (b0 + b1*100 + b2*200)) / (100*200)
	$a3 = ($x2 - ($a0 + $a1*$nx + $a2*$ny)) / ($nx*$ny);
	$b3 = ($y2 - ($b0 + $b1*$nx + $b2*$ny)) / ($nx*$ny);

	printf("a0 %12.6f  a1 %12.6f  a2 %12.6f  a3 %12.6f\n", $a0, $a1, $a2, $a3);
	printf("b0 %12.6f  b1 %12.6f  b2 %12.6f  b3 %12.6f\n", $b0, $b1, $b2, $b3);

	$A  = ($b2 * $a3) - ($b3 * $a2);
	$C1 = ($b0 * $a1) - ($b1 * $a0);
	$B1 = ($b0 * $a3) - ($b3 * $a0) + ($b2 * $a1) - ($b1 * $a2);

	printf("A %12.6f  B1 %12.6f  C1 %12.6f\n", $A, $B1, $C1);

	for ( $i=0; $i < 8; $i += 2 )
	{
		$x = $dst[$i+0];
		$y = $dst[$i+1];

		// i,j = x,y  is,il = u,v
		$B = $B1 + ($b3 * $x) - ($a3 * $y);
		$C = $C1 + ($b1 * $x) - ($a1 * $y);

		$rt = ($B * $B) - (4 * $A * $C);
		$v  = (-$B + sqrt($rt)) / (2 * $A);
		$u  = ($x - $a0 - ($a2 * $v)) / ($a1 + ($a3 * $v));

		$iv = (int)$v;
		$iu = (int)$u;
		if ( $iv < 0 || $iv > $ny || $iu < 0 || $iu > $nx )
		{
			$v = (-$B - sqrt($rt)) / (2 * $A);
			$u = ($x - $a0 - ($a2 * $v)) / ($a1 + ($a3 * $v));
		}

		printf("B %12.6f  C %12.6f  rt %12.6f  ", $B, $C, $rt);
		printf("xy %4d,%4d  uv %12.6f,%12.6f\n", $x, $y, $u, $v);
	}
	return;
}

$nx = 360 - 1;
$ny = 640 - 1;

$dst = array(52,0 , 228,46 , 255,229 , 0,246);
bilinear('top-left 0,0', $dst, $nx, $ny);

// center = 133,130
$dst = array(-81,-130 , 95,-84 , 122,99 , -133,116);
bilinear('center 0,0', $dst, $nx, $ny);

$dst = array(95,-84 , -81,-130 , -133,116 , 122,99);
bilinear('hflip', $dst, $nx, $ny);

$dst = array(-133,116 , 122,99 , 95,-84 , -81,-130);
bilinear('vflip', $dst, $nx, $ny);

$dst = array(95,-84 , -81,-130 , 122,99 , -133,116);
bilinear('twist top', $dst, $nx, $ny);

$dst = array(-81,-130 , 122,99 , 95,-84 , -133,116);
bilinear('twist right', $dst, $nx, $ny);

/*
in : PNG 360 x 640
	x0,y0 =  52,  0 /  -81,-130
	x1,y1 = 228, 46 /   95, -84
	x2,y2 = 255,229 /  122,  99
	x3,y3 =   0,246 / -133, 116
	-        cx, cy =  133, 130

	a0=52
	a1= 0.490251
	a2=-0.0813772
	a3= 0.000344375

	b0= 0
	b1= 0.128134
	b2= 0.384977
	b3=-0.000274628

	A= 0.000110228
	B= 0.213443
	C=-6.66297
 */
