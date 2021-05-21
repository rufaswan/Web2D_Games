<?php
/*
[license]
[/license]
 */

function rgb555( $clut )
{
	if ( preg_match("|[g-zG-Z]|", $clut) )
		return printf("UNKNOWN %s\n", $clut);;

	$func = __FUNCTION__;
	switch ( strlen($clut) )
	{
		case 3: // shorthand RGB888
			$pix = $clut[0] . $clut[0] . $clut[1] . $clut[1] . $clut[2] . $clut[2];
			$func($pix);
			return;
		case 4: // RGB555 -> RGB888
			$pix = hexdec($clut);
			$r = ($pix << 3) & 0xf8; // << 11 >> 8
			$g = ($pix >> 2) & 0xf8; // <<  6 >> 8
			$b = ($pix >> 7) & 0xf8; // <<  1 >> 8
			printf("%s -> #%02x%02x%02x\n", $clut, $r, $g, $b);
			return;
		case 6: // RGB888 -> RGB555
			$r = hexdec( $clut[0] . $clut[1] ) & 0xf8;
			$g = hexdec( $clut[2] . $clut[3] ) & 0xf8;
			$b = hexdec( $clut[4] . $clut[5] ) & 0xf8;
			$pix = ($r >> 3) | ($g << 2) | ($b << 7);
			printf("#%s -> %04x , %04x\n", $clut, $pix, $pix|0x8000);
			return;
		default:
			printf("INVALID %s\n", $clut);
			return;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rgb555( $argv[$i] );
