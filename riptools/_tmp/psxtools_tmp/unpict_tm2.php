<?php
require('/opt/rfslib.php');
////////////////////////////////////////
function getbit( $inp, &$in_st, &$bits, &$ctrl )
{
	if ( 0 >= $bits )
	{
		$ctrl = rfs_fgetint( $inp, $in_st, 1 );
		printf("CTRL   (%2x) %8b <- %8x\n", $ctrl, $ctrl, $in_st);
		$in_st++;
		$bits = 8;
	}
	$bits--;
	$b = $ctrl & 1;
	$ctrl >>= 1;
	return $b;
}

function unexs( $inp, $in_st, $outp, $out_st )
{
	//$in_st += 4; // skip header [size]
	$size = 0;
	$ctrl = 0;
	$bits = 0;

	printf("=== unexs() start ===\n");
	while(1) {
		$flg1 = getbit( $inp, $in_st, $bits, $ctrl );
		if ( $flg1 )
		{
			$flg2 = getbit( $inp, $in_st, $bits, $ctrl );
			if ( $flg2 )
			{
				$flg3 = getbit( $inp, $in_st, $bits, $ctrl );
				$flg4 = getbit( $inp, $in_st, $bits, $ctrl );

				$len  = ($flg3 << 1) + 2 + $flg4;
				$back = rfs_fgetint( $inp, $in_st, 2 );
					$in_st++;

				if ( 0 == $back )
					$back = 0x100;

				printf("REL_11 POS %x LEN %x\n", $back, $len);
			}
			else
			{
				$b = rfs_fgetint( $inp, $in_st, 2 );
					$in_st += 2;
				$b = ( ($b & 0xff) << 8 ) + ($b >> 8);
				if ( 0 == $b )
					break;

				$len = $b & 0xf;
				if ( 0 == $len )
				{
					$len = rfs_fgetint( $inp, $in_st, 1 );
						$in_st++;
					$len++;
				}
				else
					$len += 2;

				$back = $b >> 4;
				printf("REL_10 POS %x LEN %x\n", $back, $len);
			}

			$pos = $out_st - $back;
			while ( 0 < $len )
			{
				$b = rfs_fgetint( $outp, $pos, 1 );
				rfs_fputint( $outp, $out_st, $b, 1 );
				printf("FLG_1  (%2x) %8x -> %8x\n", $b, $pos, $out_st);
				$pos++;
				$out_st++;
				$size++;
				$len--;
			} // while ( 0 < $len )
		}
		else
		{
			$b = rfs_fgetint( $inp, $in_st, 1 );
			rfs_fputint( $outp, $out_st, $b, 1 );
			printf("FLG_0  (%2x) %8x -> %8x\n", $b, $in_st, $out_st);
			$in_st++;
			$out_st++;
			$size++;
		}
	} // while(1)
	printf("=== unexs() end ===\n");

	printf("=== return size(%x) ===\n", $size);
	return $size;
}


function unlzs1file( $fname, $in_st )
{
	$lzsp = fopen( "{$fname}", "rb" );
	$decp = fopen( "{$fname}.dec", "wb+" );

		printf("=== UNPICT : {$fname} ===\n");
		unexs( $lzsp, $in_st, $decp, 0 );

	fclose($lzsp);
	fclose($decp);
}

	unlzs1file( "evs3-title.ram.802984", 0 );

//if ( $argc == 1 )   exit();
//for ( $i=1; $i < $argc; $i++ )
	//dummy( $argv[$i] );

/*
		$ctrl = rfs_fgetint( $inp, $in_st, 4 );
		if ( 0 == $ctrl )
			break;

		$ctrl = $ctrl & BYTE;
		printf("CONTROL    %2x %8b <- %8x\n", $ctrl, $ctrl, $in_st);
		$in_st++;
		for ( $i=0; $i < 8; $i++ )
		{
			$flg = $ctrl & 1;
			if ( $flg )
			{
				$b = rfs_fgetint( $inp, $in_st, 1 );

				rfs_fputint( $outp, $out_st, $b, 1 );
				printf("COPY (%2x) %8x -> %8x\n", $b, $in_st, $out_st);

				$in_st++;
				$out_st++;
				$size++;
			}
			else
			{
				$b = rfs_fgetint( $inp, $in_st, 2 );
				list($dp, $dl) = exsdict($b);
				printf("DICT POS  %3x LEN   %2d <- %8x\n", $dp, $dl, $in_st);

				for ( $l=0; $l < $dl; $l++ )
				{
					$pos = ($dp + $l) & DICT_SIZE;
					$b = $dict[ $pos ];
					$dict[ $dict_st ] = $b;

					rfs_fputint( $outp, $out_st, $dict[ $dict_st ], 1 );
					printf("DICT COPY %3x (%2x) -> %8x\n", $pos, $b, $out_st);

					$out_st++;
					$size++;
					$dict_st = ( $dict_st + 1 ) & DICT_SIZE;
				}
				$in_st += 2;
			}

			$ctrl >>= 1;
		}
		//break;
*/
