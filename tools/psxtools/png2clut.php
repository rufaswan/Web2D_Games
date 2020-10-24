<?php
require "common.inc";
require "common-guest.inc";

function png_chunk( &$png )
{
	$chunk = array();
	$chunk['PNG'] = substr($png, 0, 8);

	$ed = strlen($png);
	$st = 8;
	while ( $st < $ed )
	{
		//   uppercase     lowercase
		// 1 is critical / optional
		// 2 is public   / private
		// 3 *reserved*  / *invalid*
		// 4 is unsafe   / safe to copy by editor
		$mgc = substr($png, $st+4, 4);
		$len = str2big($png, $st+0, 4);
		printf("%8x , %8x , $mgc\n", $st, $len);

		$dat = substr($png, $st+8, $len);
		if ( ! isset( $chunk[$mgc] ) )
			$chunk[$mgc] = "";
		$chunk[$mgc] .= $dat;

		$st += (8 + $len + 4);
	} // while ( $st < $ed )

	$chunk['IDAT'] = zlib_decode( $chunk['IDAT'] );
	//file_put_contents("png.idat", $chunk['IDAT']);
	return $chunk;
}
//////////////////////////////
function png_unfilter( &$idat, $w, $h )
{
	$rows = array();
	for ( $y=0; $y < $h; $y++ )
		$rows[] = substr($idat, $y*($w+1), $w+1);

	// https://www.w3.org/TR/PNG-Filters.html
	$prv = "";
	for ( $y=0; $y < $h; $y++ )
	{
		$fil = ord( $rows[$y][0] );
		$dat = substr($rows[$y], 1);
		switch ( $fil )
		{
			case 1: // sub
				for ( $x=1; $x < $w; $x++ )
				{
					$b0 = ord( $dat[$x] );
					$b1 = ord( $dat[$x-1] ); // left

					$b = ($b0 + $b1) & BIT8;
					$dat[$x] = chr($b);
				}
				break;
			case 2: // up
				for ( $x=0; $x < $w; $x++ )
				{
					$b0 = ord( $dat[$x] );
					$b1 = ord( $prv[$x] ); // up

					$b = ($b0 + $b1) & BIT8;
					$dat[$x] = chr($b);
				}
				break;
			case 3: // average
				for ( $x=1; $x < $w; $x++ )
				{
					$b0 = ord( $dat[$x] );
					$b1 = ord( $dat[$x-1] ); // left
					$b2 = ord( $prv[$x] );   // up

					$bs = ($b1 + $b2) / 2;
					$b  = (int)($b0 + $bs) & BIT8;
					$dat[$x] = chr($b);
				}
				break;
			case 4: // paeth
				for ( $x=1; $x < $w; $x++ )
				{
					$b0 = ord( $dat[$x] );
					$b1 = ord( $dat[$x-1] ); // left
					$b2 = ord( $prv[$x] );   // up
					$b3 = ord( $prv[$x-1] ); // up left

					$bs = ($b1 + $b2) - $b3;
					$ba = ($bs - $b1);
					$bb = ($bs - $b2);
					$bc = ($bs - $b3);

					if ( $ba <= $bb && $ba <= $bc )
						$b = ($b0 + $b1) & BIT8;
					else
					if ( $bb <= $bc )
						$b = ($b0 + $b2) & BIT8;
					else
						$b = ($b0 + $b3) & BIT8;

					$dat[$x] = chr($b);
				}
				break;
			default: // none
				break;
		} // switch ( $fil )

		$rows[$y] = $dat;
		$prv = $dat;
	} // for ( $y=0; $y < $h; $y++ )

	$idat = implode('', $rows);
	return;
}

function png_8bpp( &$idat, $dp )
{
	switch ( $dp )
	{
		case 8:
			return $idat;
		case 4:
			$pix = '';
			$len = strlen($idat);
			for ( $i=0; $i < $len; $i++ )
			{
				$b = ord( $idat[$i] );
				$cnt = 8;
				while ( $cnt > 0 )
				{
					$cnt -= 4;
					$b1 = ($b >> $cnt) & 0x0f;
					$pix .= chr($b1);
				}
			} // for ( $i=0; $i < $len; $i++ )
			return $pix;
		case 2:
			$pix = '';
			$len = strlen($idat);
			for ( $i=0; $i < $len; $i++ )
			{
				$b = ord( $idat[$i] );
				$cnt = 8;
				while ( $cnt > 0 )
				{
					$cnt -= 2;
					$b1 = ($b >> $cnt) & 3;
					$pix .= chr($b1);
				}
			} // for ( $i=0; $i < $len; $i++ )
			return $pix;
		case 1:
			$pix = '';
			$len = strlen($idat);
			for ( $i=0; $i < $len; $i++ )
			{
				$b = ord( $idat[$i] );
				$cnt = 8;
				while ( $cnt > 0 )
				{
					$cnt -= 1;
					$b1 = ($b >> $cnt) & 1;
					$pix .= chr($b1);
				}
			} // for ( $i=0; $i < $len; $i++ )
			return $pix;
	} // switch ( $dp )

	return '';
}

function png_plte( &$chunk )
{
	if ( ! isset( $chunk['PLTE'] ) )
		return "";

	$len = strlen($chunk['PLTE']);
	$num = (int)($len / 3);
	$pal = "";
	for ( $i=0; $i < $num; $i++ )
	{
		$pal .= substr($chunk['PLTE'], $i*3, 3);
		if ( isset( $chunk['tRNS'][$i] ) )
			$pal .= $chunk['tRNS'][$i];
		else
			$pal .= BYTE;
	}
	return $pal;
}
//////////////////////////////
function png2clut( $fname )
{
	$png = file_get_contents($fname);
	if ( empty($png) )  return;

	if ( substr($png, 1, 3) != "PNG" )
		return;

	$chunk = png_chunk($png);
	$w = str2big($chunk['IHDR'], 0, 4);
	$h = str2big($chunk['IHDR'], 4, 4);
	$dp = ord( $chunk['IHDR'][8] );
	$cl = ord( $chunk['IHDR'][9] );

	if ( $dp > 8 ) // 16-bit not supported
		return;
	if ( ($cl & 2) == 0 ) // grayscale not supported
		return;

	$pix = "";
	switch ( $dp )
	{
		case 1:
			png_unfilter($chunk['IDAT'], $w/8, $h);
			$pix = png_8bpp($chunk['IDAT'], 1);
			break;
		case 2:
			png_unfilter($chunk['IDAT'], $w/4, $h);
			$pix = png_8bpp($chunk['IDAT'], 2);
			break;
		case 4:
			png_unfilter($chunk['IDAT'], $w/2, $h);
			$pix = png_8bpp($chunk['IDAT'], 4);
			break;
		case 8:
			png_unfilter($chunk['IDAT'], $w,   $h);
			$pix = $chunk['IDAT'];
			break;
	} // switch ( $dp )

	$pal = png_plte($chunk);
	if ( empty($pal) )
	{
		$rgba = 'RGBA';
		$rgba .= chrint($w, 4);
		$rgba .= chrint($h, 4);
		$rgba .= $pix;
		file_put_contents("$fname.rgba", $rgba);
	}
	else
	{
		$cc = strlen($pal) / 4;
		$rgba = 'CLUT';
		$rgba .= chrint($cc, 4);
		$rgba .= chrint($w,  4);
		$rgba .= chrint($h,  4);
		$rgba .= $pal;
		$rgba .= $pix;
		file_put_contents("$fname.clut", $rgba);
	}
	return;
}

for ( $i=0; $i < $argc; $i++ )
	png2clut( $argv[$i] );
