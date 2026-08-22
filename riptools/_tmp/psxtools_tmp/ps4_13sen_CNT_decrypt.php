<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 *
 * Special Thanks
 *   LibOrbisPkg
 *   https://github.com/maxton/LibOrbisPkg
 *     maxton
 *     Zer0xFF
 *     flatz
 *     idc
 */
require "common.inc";
require "common-guest.inc";

/*
// passcode = 0x10 bytes
// ekpfs , dk[0-6] = 0x20 bytes
$gp_pass = hex2bin('00000000000000000000000000000000'); // 16 byte , all zeros

function ps4fs_image( $fp, $pfx, $base, $pfs_flg )
{
	// https://www.psdevwiki.com/ps4/PFS
	$head = fp2str($fp, $base, 0x1000);
	if ( str2int($head,8,4) !== 0x01332a0b ) // 20130315
		return;

	// 1 signed
	// 2 64-bit
	// 4 encrypted
	// 8 == 1
	$b = str2int($head, 0x1c, 2);
	$mode = [
		'sig' => ($b & 1),
		'b64' => ($b & 2),
		'enc' => ($b & 4),
	];
	if ( $mode['sig'] )  printf("[signed] %s\n", $pfx);
	if ( $mode['b64'] )  printf("[64-bit] %s\n", $pfx);
	if ( $mode['enc'] )  printf("[encryp] %s\n", $pfx);

	$blksz = str2int($head, 0x20, 4);
	$ndblk = str2int($head, 0x38, 4);
	$seed  = substr ($head, 0x370, 0x10);

	$newcrypt = ord( $pfs_flg[0] ) & 2;

cryto.cs
	PFS_CrytoKey(ekpfs, seed, index)
		// array.copy(src,dst,len)
		// array.copy(src,srcin,dst,dstin,len)
		$d = chrint(index) . $seed;
		return hmac-sha256(ekpfs, d)

	PFS_EncKey(ekpfs, $seed, $newcrypt)
		if ( $newcrypt )
			ekpfs = hmac-sha256(ekpfs, seed)
		$enckey = PFS_CrytoKey(ekpfs, seed, 1)
		$tweakkey = $enckey:0:16
		$datakey  = $enckey:16:16
		return [$tweakkey,$datakey];

pfsreader.cs

	PFSreader
		XTSsize = 0x1000;
		XTSstart = $blksz >> 12

		list($tweakkey,$datakey) = PFS_EncKey(ekpfs, $seed, $newcrypt)

gp4creator.cs
	Create-project-from-pkg
		ekpfs = get-ekpfs
		va = pkgfile.create-view-accessor(pfs_offset,pfs_size,memory_read)
		pfs-reader(va, $pkg_flag, ekpfs)

pkg.cs
	get-ekpfs
		dk3 = rsa2048-dec(entrykeys[3], rsakeyset3)
		ivkey = sha256( imagekey . dk3 )
		imagekeydec = imagekey.filedata
		aes-cbc-cfb-128-dec( imagekeydec , imagekeydec , len(imagekeydec) , ivkey:16:16 , ivkey:0:16 )
		return rsa-2048-dec( imagekeydec , rsafakekey )

pkgreader.cs
	read-pkg
		case entryid.entrykey // 10
			pkg.entrykeys = keysentry.read(entry, s)
			break
		case entryid.imagekey // 20
			pkg.imagekey = offset:size
			break

entry.cs
		keysentry read(e, pkg)
			seeddigest = byte[32]
			for i in 0-6
				pkgentrykey[i].digest = byte[ 32]
			for i in 0-6
				pkgentrykey[i].key    = byte[256]

	return;
}

	$pfx = substr($head, 0x40, 0x24);
		$pfx = rtrim($pfx, ZERO);
	echo "ID : $pfx\n";

	$pfs_flg = substr ($head, 0x408, 8);
	$pfs_off = str2big($head, 0x414, 4);
	$pfs_siz = str2big($head, 0x41c, 4);
	printf("PS4FS image = %x [%x]\n", $pfs_off, $pfs_siz);
	if ( filesize($fname) !== ($pfs_off+$pfs_siz) )
		return;

	ps4fs_image($fp, $pfx, $pfs_off, $pfs_flg);
 */
//////////////////////////////
function RSA2048_decrypt( $cipher, $rsa )
{
	if ( strlen($cipher) !== 256 ) // 256 * 8 = 2048
		return php_error("RSA cipher len != 256");

	$plain = strrev($cipher);

	return;
}
//////////////////////////////
function pkghead( $fp, &$pkg, &$head )
{
	$pkg['ID'] = rtrim( substr($head, 0x40, 0x24) );

	// entry
	$en_off = str2big($head, 0x18, 4);
	$en_cnt = str2big($head, 0x10, 4);

	$ent = [];
	for ( $i=0; $i < $en_cnt; $i++ )
	{
		$pos = $en_off + ($i * 0x20);

		$id = str2big($head, $pos+ 0,   4);
		$of = str2big($head, $pos+16,   4);
		$sz = str2big($head, $pos+20,   4);
		$b1 = substr ($head, $pos+ 4,  12);
		$b2 = substr ($head, $of    , $sz);

		$ent[$id] = [$i, $b1, $b2];
	} // for ( $i=0; $i < $en_cnt; $i++ )

	foreach ( $ent as $ek => $ev )
	{
		list($i,$b1,$b2) = $ev;

		$b  = str2big($b1, 0, 4);
		$fn = substr0($ent[0x200][2], $b);
		if ( empty($fn) )
			$fn = sprintf("%04d.%x", $i, $ek);

		switch ( $ek )
		{
			case 0x10:
				$pkg['en10']['seed'] = substr($b2, 0, 0x20);
				for ( $i=0; $i < 7; $i++ )
				{
					$pkg['en10']['keys'][$i] = [
						substr($b2, 0x20 +$i*0x20 , 0x20 ),
						substr($b2, 0x100+$i*0x100, 0x100),
					];
				}
				break;
			case 0x20:
				$pkg['en20'] = $b2;
				break;
		} // switch ( $ek )

		save_file("{$pkg['ID']}/pkg/$fn", $b2);
	} // foreach ( $ent as $ek => $ev )

	// decrypt EKPFS key/dk1 for FPKG
	$dk3 = RSA2048_decrypt( $pkg['en10']['keys'][3][1],  )
	return;
}

function pkgfile( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	// https://www.psdevwiki.com/ps4/Package_Files
	$head = fp2str($fp, 0, 0x100);
	if ( substr($head,0,4) !== "\x7fCNT" )
		return;

	$size = str2big($head, 0x2c, 4);
	$head = fp2str ($fp, 0, $size);

	$pkg = [];
	pkghead($fp, $pkg, $head);

	if ( ! isset( $pkg['ekpfs'] ) || empty( $pkg['ekpfs'] ) )
		return php_notice("NOT Fake PKG %s", $fname);


	return;
}

for ( $i=1; $i < $argc; $i++ )
	pkgfile( $argv[$i] );
