<?php
// https://ps2linux.no-ip.info/playstation2-linux.com/download/p2lsd/sparkys_swizzle_code.html

function pixadd(&$list, $k, $v)
{
	if ( isset( $list[$k] ) )
	{
		$err = sprintf("DUPL %x = %x [%x]\n", $k, $v, $list[$k]);
		trigger_error($err, E_USER_WARNING);
	}
	$list[$k] = $v;
	return;
}

function pixlist(&$list, $name1, $name2)
{
	printf("\n== %s[] = %s[] == %x\n", $name1, $name2, count($list));
	ksort($list);
	foreach ( $list as $k => $v )
		printf("%s[%x] = %s[%x]\n", $name1, $k, $name2, $v);
	return;
}
///////// swizzlers /////////
function Swizzle8to32($width, $height)
{
	printf("== %s( %x , %x ) = %x\n", __FUNCTION__, $width, $height, $width*$height);
	// this function works for the following resolutions
	// Width:       any multiple of 16 smaller then or equal to 4096
	// Height:      any multiple of 4 smaller then or equal to 4096

	// the texels must be uploaded as a 32bit texture
	// width_32bit = width_8bit / 2
	// height_32bit = height_8bit / 2
	// remember to adjust the mapping coordinates when
	// using a dimension which is not a power of two

	$in = [];
	$swiz = [];
	for ( $y=0; $y < $height; $y++ )
		for ( $x=0; $x < $width; $x++ )
		{
			$block_location = ($y&(~0xf))*$width + ($x&(~0xf))*2;
			$swap_selector = ((($y+2)>>2)&0x1)*4;
			$posY = ((($y&(~3))>>1) + ($y&1))&0x7;
			$column_location = $posY*$width*2 + (($x+$swap_selector)&0x7)*4;

			$byte_num = (($y>>1)&1) + (($x>>2)&2);     // 0,1,2,3

			$setPix = $block_location + $column_location + $byte_num;
			$uPen = $y * $width + $x;

			printf("Swiz[%x] = In[%x]\n", $setPix, $uPen);
			pixadd($swiz, $setPix, $uPen  );
			pixadd($in  , $uPen  , $setPix);
		} // for ( $x=0; $x < $width; $x++ )

	pixlist($swiz, "Swiz", "In");
	pixlist($in  , "In", "Swiz");
	return;
}

function Swizzle4to32($width, $height)
{
	printf("== %s( %x , %x ) = %x\n", __FUNCTION__, $width, $height, $width*$height);
	// this function works for the following resolutions
	// Width:       32, 64, 96, 128, any multiple of 128 smaller then or equal to 4096
	// Height:      16, 32, 48, 64, 80, 96, 112, 128, any multiple of 128 smaller then or equal to 4096

	// the texels must be uploaded as a 32bit texture
	// width_32bit = height_4bit / 2
	// height_32bit = width_4bit / 4
	// remember to adjust the mapping coordinates when
	// using a dimension which is not a power of two

	$in = [];
	$swiz = [];
	for ( $y=0; $y < $height; $y++ )
		for ( $x=0; $x < $width; $x++ )
		{
			// swizzle
			$pageX = $x & (~0x7f);
			$pageY = $y & (~0x7f);

			$pages_horz = ($width+127)/128;
			$pages_vert = ($height+127)/128;

			$page_number = ($pageY/128)*$pages_horz + ($pageX/128);

			$page32Y = ($page_number/$pages_vert)*32;
			$page32X = ($page_number%$pages_vert)*64;

			$page_location = $page32Y*$height*2 + $page32X*4;

			$locX = $x & 0x7f;
			$locY = $y & 0x7f;

			$block_location = (($locX&(~0x1f))>>1)*$height + ($locY&(~0xf))*2;
			$swap_selector = ((($y+2)>>2)&0x1)*4;
			$posY = ((($y&(~3))>>1) + ($y&1))&0x7;

			$column_location = $posY*$height*2 + (($x+$swap_selector)&0x7)*4;

			$byte_num = ($x>>3)&3;     // 0,1,2,3
			$bits_set = ($y>>1)&1;     // 0,1            (lower/upper 4 bits)

			$setPix = $page_location + $block_location + $column_location + $byte_num;
			//$index = $y * $width + $x;
			//$uPen = ($index >> 1);
			//printf("Swiz[%x] & %x |= In[%x] >> %x\n", $setPix, -$bits_set, $uPen, (($index&1)*4));
			$uPen = $y * $width + $x;
			$setPix = ($setPix * 2) + $bits_set;
			printf("Swiz[%x] = In[%x]\n", $setPix, $uPen);
			pixadd($swiz, $setPix, $uPen  );
			pixadd($in  , $uPen  , $setPix);
		} // for ( $x=0; $x < $width; $x++ )

	pixlist($swiz, "Swiz", "In");
	pixlist($in  , "In", "Swiz");
	return;
}


function Swizzle16to32($width, $height)
{
	printf("== %s( %x , %x ) = %x\n", __FUNCTION__, $width, $height, $width*$height);
	// this function works for the following resolutions
	// Width:       16, 32, 48, 64, any multiple of 64 smaller then or equal to 4096
	// Height:      8, 16, 24, 32, 40, 48, 56, 64, any multiple of 64 smaller then or equal to 4096

	// the texels must be uploaded as a 32bit texture
	// width_32bit = height_16bit
	// height_32bit = width_16bit / 2
	// remember to adjust the mapping coordinates when
	// using a dimension which is not a power of two

	$in = [];
	$swiz = [];
	for ( $y=0; $y < $height; $y++ )
		for ( $x=0; $x < $width; $x++ )
		{
			$pageX = $x & (~0x3f);
			$pageY = $y & (~0x3f);

			$pages_horz = ($width+63)/64;
			$pages_vert = ($height+63)/64;

			$page_number = ($pageY/64)*$pages_horz + ($pageX/64);

			$page32Y = ($page_number/$pages_vert)*32;
			$page32X = ($page_number%$pages_vert)*64;

			$page_location = ($page32Y*$height + $page32X)*2;

			$locX = $x & 0x3f;
			$locY = $y & 0x3f;

			$block_location = ($locX&(~0xf))*$height + ($locY&(~0x7))*2;
			$column_location = (($y&0x7)*$height + ($x&0x7))*2;

			$short_num = ($x>>3)&1;       // 0,1

			$uCol = $y * $width + $x;
			$setPix = $page_location + $block_location + $column_location + $short_num;
			printf("Swiz[%x] = In[%x]\n", $setPix, $uCol);
			pixadd($swiz, $setPix, $uCol  );
			pixadd($in  , $uCol  , $setPix);
		} // for ( $x=0; $x < $width; $x++ )

	pixlist($swiz, "Swiz", "In");
	pixlist($in  , "In", "Swiz");
	return;
}

$w = 0x20;
$h = 0x20;
Swizzle4to32($w, $h);
//Swizzle8to32($w, $h);
//Swizzle16to32($w, $h);

