<?php
require('/opt/rfslib.php');
////////////////////////////////////////
// for derivations
define("DICT_SIZE",  0x7ff);
define("DICT_START", 0x7de);
function exsdict( $byte2 )
{
	$b1 = $byte2 & BYTE;
	$b2 = $byte2 >> 8;

	$b1 = (($b2 & 0xe0) << 3) + $b1;
	$b2 = $b2 & 0x1f;

	$dp = $b1;
	$dl = $b2 + 3;

	return [$dp, $dl];
}
////////////////////////////////////////
function unexs( $inp, $in_st, $outp, $out_st )
{
	//$in_st += 4; // skip header [size]
	$size = 0;
	$dict_st = DICT_START;
	$dict = array_pad( [], DICT_SIZE, 0 );

	printf("=== unexs() start ===\n");
	while(1) {
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
				$dict[ $dict_st ] = $b;

				rfs_fputint( $outp, $out_st, $dict[ $dict_st ], 1 );
				printf("COPY (%2x) %8x -> %8x\n", $b, $in_st, $out_st);

				$in_st++;
				$out_st++;
				$size++;
				$dict_st = ( $dict_st + 1 ) & DICT_SIZE;
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
		unexs( $lzsp, $in_st+4, $decp, 0 );

	fclose($lzsp);
	fclose($decp);
}

	unlzs1file( "PICT.IMG.5199000.dec", 0x180 );
	unlzs1file( "PICT.IMG.526e000.dec", 0x180 );

//if ( $argc == 1 )   exit();
//for ( $i=1; $i < $argc; $i++ )
	//dummy( $argv[$i] );
