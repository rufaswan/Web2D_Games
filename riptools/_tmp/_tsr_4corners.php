<?php
/*
[license]
[/license]
 */
( exec('which identify') ) ? '' : trigger_error('command identify not found', E_USER_ERROR);
( exec('which convert' ) ) ? '' : trigger_error('command convert not found' , E_USER_ERROR);
//////////////////////////////
// https://en.m.wikipedia.org/wiki/Color_difference
define('EUD_MAXD' , 1.0/442);
define('LAB_MAXD' , 1.0/101);
define('HEX_MAXD' , 1.0/2.55);

function binbash( $cmd )
{
	$ret = [];
	exec($cmd, $ret);
	if ( count($ret) === 1 )
		return $ret[0];
	else
		return $ret;
}

function mean_rgb( $fname, $w, $h, $x, $y )
{
	$cmd  = 'convert "%s"'
	. ' -crop %dx%d+%d+%d'
	. ' +repage'
	. ' -resize 1x1\!'
	. ' -format "%[fx:int(255*r)],%[fx:int(255*g)],%[fx:int(255*b)]"'
	. ' info:-';
	$cmd = sprintf($cmd, $fname, $w, $h, $x, $y);
	$rgb = binbash($cmd);
	return explode(',', $rgb);
}
//////////////////////////////
function rgb_dist( $name, $c1, $c2 )
{
	$r = $c2[0] - $c1[0];
	$g = $c2[1] - $c1[1];
	$b = $c2[2] - $c1[2];
	$d1 = ($r*$r) + ($g*$g) + ($b*$b);
		$d1 = sqrt($d1);
	$d2 = $d1 * EUD_MAXD;
	printf("%s  %6.2f  %6.2f\n", $name, $d2*100, $d1);
	return ($d2 > 0.1) ? 1 : 0;
}

function euclidean_dist( &$rgb )
{
	$d = 0;
	$d += rgb_dist('U' , $rgb[0], $rgb[1]);
	$d += rgb_dist('D' , $rgb[2], $rgb[3]);
	$d += rgb_dist('L' , $rgb[0], $rgb[2]);
	$d += rgb_dist('R' , $rgb[1], $rgb[3]);
	$d += rgb_dist('\\', $rgb[0], $rgb[3]);
	$d += rgb_dist('/' , $rgb[1], $rgb[2]);
	printf("dist  %d\n", $d);
	return;
}
//////////////////////////////
function labf( $f )
{
	$q = 6 / 29;
	if ( $f > ($q ** 3) )
		return $f ** (1/3);
	else
		return ($f / (3 * $q * $q)) + (4 / 29);
}

function rgb2lab( $rgb )
{
	// https://en.m.wikipedia.org/wiki/CIE_1931_color_space

	//Standard Illuminant D65
	$xyzn = [95.0489 , 100 , 108.8840];

	//Standard Illuminant D50 , for printing
	//$xyzn = [96.4212 , 100 , 82.5188];

	$rgb = [
		$rgb[0] * HEX_MAXD ,
		$rgb[1] * HEX_MAXD ,
		$rgb[2] * HEX_MAXD ,
	];

	// https://en.m.wikipedia.org/wiki/CIELAB_color_space
	// https://en.m.wikipedia.org/wiki/Grassmann's_laws_%28color_science%29
	// | X |   | 0.49     0.31    0.2     | | R |
	// | Y | = | 0.17697  0.8124  0.01063 | | G |
	// | Z |   | 0        0.01    0.99    | | B |
	$xyz = [
		(0.49     * $rgb[0]) + (0.31   * $rgb[1]) + (0.2 * $rgb[2]),
		(0.17698  * $rgb[0]) + (0.8124 * $rgb[1]) + (0.01063 * $rgb[2]),
		(0.01     * $rgb[1]) + (0.99   * $rgb[2]),
	];

	$lab = [
		116 *  labf( $xyz[1]/$xyzn[1] ) - 16 ,
		500 * (labf( $xyz[0]/$xyzn[0] ) - labf( $xyz[1]/$xyzn[1] )) ,
		200 * (labf( $xyz[1]/$xyzn[1] ) - labf( $xyz[2]/$xyzn[2] )) ,
	];
	return $lab;
}

function lab_dist( $name, $c1, $c2 )
{
	$l = $c2[0] - $c1[0];
	$a = $c2[1] - $c1[1];
	$b = $c2[2] - $c1[2];
	$d1 = ($l*$l) + ($a*$a) + ($b*$b);
		$d1 = sqrt($d1);
	$d2 = $d1 * LAB_MAXD;
	printf("%s  %6.2f  %6.2f\n", $name, $d2*100, $d1);
	return ($d2 > 0.1) ? 1 : 0;
}

function deltaE_76_dist( &$rgb )
{
	$lab = [
		rgb2lab($rgb[0]),
		rgb2lab($rgb[1]),
		rgb2lab($rgb[2]),
		rgb2lab($rgb[3]),
	];
	$d = 0;
	$d += lab_dist('U' , $lab[0], $lab[1]);
	$d += lab_dist('D' , $lab[2], $lab[3]);
	$d += lab_dist('L' , $lab[0], $lab[2]);
	$d += lab_dist('R' , $lab[1], $lab[3]);
	$d += lab_dist('\\', $lab[0], $lab[3]);
	$d += lab_dist('/' , $lab[1], $lab[2]);
	printf("dist  %d\n", $d);
	return $d;
}
//////////////////////////////
function imgcorner( $rm, $fname )
{
	$dim = binbash('identify -format "%w,%h" "' .$fname. '"');
	$d = explode(',', $dim);
		$w = (int)$d[0];
		$h = (int)$d[1];

	printf("%4x x %4x = %s\n", $w, $h, $fname);
	$qw = $w >> 8;
	$qh = $h >> 8;
	if ( $qw < 1 || $qh < 1 )
		return;

	$rgb = [
		mean_rgb($fname, $qw, $qh, 0     , 0     ), // top left
		mean_rgb($fname, $qw, $qh, $w-$qw, 0     ), // top right
		mean_rgb($fname, $qw, $qh, 0     , $h-$qh), // bottom left
		mean_rgb($fname, $qw, $qh, $w-$qw, $h-$qh), // bottom right
	];

	//$d = euclidean_dist($rgb);
	$d = deltaE_76_dist($rgb);
	if ( $rm && $d < 6 )
		unlink($fname);
	return;
}

$rm = false;
for ( $i=1; $i < $argc; $i++ )
{
	if ( $argv[$i] === '-rm' )
		$rm = true;
	else
		imgcorner( $rm, $argv[$i] );
}


$rgbk = [  0,  0,  0];
$rgbg = [128,128,128];
$rgbw = [255,255,255];
rgb_dist('kg', $rgbk, $rgbg);
rgb_dist('kw', $rgbk, $rgbw);
rgb_dist('ww', $rgbw, $rgbw);

$labk = rgb2lab($rgbk);
$labg = rgb2lab($rgbg);
$labw = rgb2lab($rgbw);
lab_dist('kg', $labk, $labg);
lab_dist('kw', $labk, $labw);
lab_dist('ww', $labw, $labw);

/*
convert  $img  -resize 2x2\!  txt:
*/
