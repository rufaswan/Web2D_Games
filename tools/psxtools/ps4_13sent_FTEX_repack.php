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
 *   GFD Studio
 *   https://github.com/TGEnigma/GFD-Studio/blob/master/GFDLibrary/Textures/GNF/GNFTexture.cs
 */
require 'common.inc';
require 'common-guest.inc';
require 'class-bptc.inc';
require 'class-s3tc.inc';

//define('DRY_RUN', true);

//////////////////////////////
// Reverse of gnf_swizzled_bc - re-swizzle RGBA back to swizzled order
function gnf_reswizzle_bc(&$pix, $ow, $oh)
{
	printf("== gnf_reswizzle_bc( %x , %x )\n", $ow, $oh);

	// 1 pixel = 4*4 bc tile
	$bc = array(
		'pix' => $pix,
		'enc' => str_repeat(ZERO, strlen($pix)),
		'w'   => $ow >> 2, // div 4
		'h'   => $oh >> 2, // div 4
		'bpp' => 4,
	);

	$pos = 0;
	// morton swizzle for every 8x8 tiles
	for ($y = 0; $y < $bc['h']; $y += 8) {
		for ($x = 0; $x < $bc['w']; $x += 8) {
			for ($i = 0; $i < 0x40; $i++) {
				$sx = swizzle_bitmask($i, 0x55);
				$sy = swizzle_bitmask($i, 0x2a);
				$dx = $x + $sx;
				$dy = $y + $sy;

				if ($dx >= $bc['w'] || $dy >= $bc['h'])
					continue;

				// Copy 4x4 pixel block from unswizzled to swizzled position
				$row = 4 * $bc['bpp']; // 4 pixels * 4 bytes
				for ($py = 0; $py < 4; $py++) {
					$syy = ($dy * 4 + $py) * $bc['w'] * 4;
					$sxx = $dx * 4 + $syy;
					$s = substr($bc['pix'], $sxx * $bc['bpp'], $row);
					str_update($bc['enc'], $pos, $s);
					$pos += $row;
				}
			}
		}
	}

	$pix = $bc['enc'];
	return;
}

//////////////////////////////
// Convert RGBA to grayscale (take R channel since R=G=B for BC4 output)
function rgba_to_gray(&$rgba)
{
	$gray = '';
	$len = strlen($rgba);
	for ($i = 0; $i < $len; $i += 4) {
		$gray .= $rgba[$i]; // Take R channel (R=G=B for grayscale)
	}
	return $gray;
}

//////////////////////////////
// BC4 encoder - compress grayscale to BC4 format
function bc4_encode(&$gray)
{
	$enc = '';
	$len = strlen($gray);

	// Process 4x4 blocks (16 pixels = 16 bytes grayscale -> 8 bytes BC4)
	for ($i = 0; $i < $len; $i += 16) {
		$block = substr($gray, $i, 16);
		$enc .= bc4_encode_block($block);
	}
	return $enc;
}

function bc4_encode_block(&$block)
{
	// Find min and max alpha values in the block
	$min = 255;
	$max = 0;
	for ($i = 0; $i < 16; $i++) {
		$v = ord($block[$i]);
		if ($v < $min) $min = $v;
		if ($v > $max) $max = $v;
	}

	// Use 8-value mode (a0 > a1) for better quality
	$a0 = $max;
	$a1 = $min;

	// Build interpolated values
	$a = array();
	$a[0] = $a0;
	$a[1] = $a1;

	if ($a0 > $a1) {
		// 8 interpolated values
		$a[2] = (int)(($a0 * 6 + $a1 * 1) / 7);
		$a[3] = (int)(($a0 * 5 + $a1 * 2) / 7);
		$a[4] = (int)(($a0 * 4 + $a1 * 3) / 7);
		$a[5] = (int)(($a0 * 3 + $a1 * 4) / 7);
		$a[6] = (int)(($a0 * 2 + $a1 * 5) / 7);
		$a[7] = (int)(($a0 * 1 + $a1 * 6) / 7);
	} else {
		// 6 interpolated values + 0 and 255
		$a[2] = (int)(($a0 * 4 + $a1 * 1) / 5);
		$a[3] = (int)(($a0 * 3 + $a1 * 2) / 5);
		$a[4] = (int)(($a0 * 2 + $a1 * 3) / 5);
		$a[5] = (int)(($a0 * 1 + $a1 * 4) / 5);
		$a[6] = 0;
		$a[7] = 255;
	}

	// Find best index for each pixel
	$indices = array();
	for ($i = 0; $i < 16; $i++) {
		$v = ord($block[$i]);
		$best = 0;
		$best_diff = abs($v - $a[0]);
		for ($j = 1; $j < 8; $j++) {
			$diff = abs($v - $a[$j]);
			if ($diff < $best_diff) {
				$best_diff = $diff;
				$best = $j;
			}
		}
		$indices[$i] = $best;
	}

	// Pack into 8 bytes: 2 bytes for a0,a1 + 6 bytes for 16 3-bit indices
	$enc = chr($a0) . chr($a1);

	// Pack 16 3-bit indices into 48 bits (6 bytes)
	// First 8 indices -> 3 bytes
	$int = 0;
	for ($i = 0; $i < 8; $i++) {
		$int |= ($indices[$i] << ($i * 3));
	}
	$enc .= chr($int & 0xff);
	$enc .= chr(($int >> 8) & 0xff);
	$enc .= chr(($int >> 16) & 0xff);

	// Next 8 indices -> 3 bytes
	$int = 0;
	for ($i = 0; $i < 8; $i++) {
		$int |= ($indices[$i + 8] << ($i * 3));
	}
	$enc .= chr($int & 0xff);
	$enc .= chr(($int >> 8) & 0xff);
	$enc .= chr(($int >> 16) & 0xff);

	return $enc;
}

//////////////////////////////
// Repack BC4 texture back to PS4 GNF/FTX0/FTEX format
function im_bc4_repack(&$rgba, $w, $h)
{
	printf("== im_bc4_repack( %x , %x )\n", $w, $h);

	// Re-swizzle RGBA data
	gnf_reswizzle_bc($rgba, $w, $h);

	// Convert RGBA to grayscale
	$gray = rgba_to_gray($rgba);

	// Compress to BC4
	$bc4 = bc4_encode($gray);

	return $bc4;
}

//////////////////////////////
// Build GNF header for BC4 texture
function build_gnf_header($w, $h, $fmt)
{
	// GNF header is 0x100 bytes total
	// Offset stored is 0xF8 because decoder does base+=8 before using offset
	// So pixel_pos = (gnf_start + 8) + 0xF8 = gnf_start + 0x100
	$gnf = 'GNF ';
	$gnf .= chrint(0xF8, 4); // offset to pixel data (0x100 - 8)

	// Texture descriptor (from GFD Studio / PS4 GNF format)
	$gnf .= chr(0x01); // version
	$gnf .= chr(0x01); // texture count = 1
	$gnf .= chr(0x01); // alignment
	$gnf .= chr(0x00); // unused
	$gnf .= chrint(0, 4); // unused

	// b0 - unused for our purposes
	$gnf .= chrint(0, 4);

	// b1 - format info
	// ccccccss ssssmmmm mmmmmmmm --------
	// s = surface format (0x26 = BC4)
	// c = channel type
	$b1 = ($fmt << 20);
	$gnf .= chrint($b1, 4);

	// b2 - dimensions
	// -ssshhhh hhhhhhhh hhwwwwww wwwwwwww
	$b2 = (($w - 1) & 0x3fff) | ((($h - 1) & 0x3fff) << 14);
	$gnf .= chrint($b2, 4);

	// b3 - tile mode, mip levels, etc
	// Use default tile mode for swizzled
	$b3 = 0x0d000000; // tile mode
	$gnf .= chrint($b3, 4);

	// b4 - depth and pitch
	$gnf .= chrint(0, 4);

	// b5 - array slices
	$gnf .= chrint(0, 4);

	// b6 - misc flags
	$gnf .= chrint(0, 4);

	// Pad to 0x100 bytes (offset where pixel data starts)
	while (strlen($gnf) < 0x100) {
		$gnf .= ZERO;
	}

	return $gnf;
}

//////////////////////////////
// Build FTX0 chunk
function build_ftx0($gnf_header, $pixel_data)
{
	$ftx0 = 'FTX0';

	// sz1 = size of GNF header + pixel data (total size after FTX0 header)
	$sz1 = strlen($gnf_header) + strlen($pixel_data);
	$ftx0 .= chrint($sz1, 4);

	// sz2 = offset to GNF from FTX0 start (after FTX0 header)
	$sz2 = 0x40; // FTX0 header size
	$ftx0 .= chrint($sz2, 4);

	// Pad FTX0 header to sz2 bytes
	while (strlen($ftx0) < $sz2) {
		$ftx0 .= ZERO;
	}

	// Append GNF header and pixel data
	$ftx0 .= $gnf_header;
	$ftx0 .= $pixel_data;

	return $ftx0;
}

//////////////////////////////
// Main repack function - process input .gnf files
function aegis_repack($argv)
{
	if (count($argv) < 4) {
		printf("Usage: php %s <source.ftx> <output.ftx> <input1.gnf> [input2.gnf ...]\n", $argv[0]);
		printf("  Repacks .gnf texture files into a .ftx asset file\n");
		printf("  <source.ftx>  - Original FTEX file (used as header template)\n");
		printf("  <output.ftx>  - Output FTEX file with repacked textures\n");
		printf("  <inputN.gnf>  - Input texture files (must match count in source.ftx)\n");
		printf("  Currently supports BC4 format only\n");
		return;
	}

	$source_file = $argv[1];
	$output_file = $argv[2];
	$input_files = array_slice($argv, 3);

	// Load and parse source FTEX to extract header
	printf("======================================\n");
	printf("Loading source FTEX: %s\n", basename($source_file));

	$source = file_get_contents($source_file);
	if (empty($source)) {
		printf("ERROR: Cannot load source file: %s\n", $source_file);
		return;
	}

	if (substr($source, 0, 4) !== 'FTEX') {
		printf("ERROR: Source file is not a valid FTEX file\n");
		return;
	}

	// Extract FTEX header info
	$ver  = str2int($source,  4, 4);
	$hdsz = str2int($source,  8, 4);
	$cnt  = str2int($source, 12, 4);

	printf("  Version:      0x%08x\n", $ver);
	printf("  Header Size:  0x%x (%d bytes)\n", $hdsz, $hdsz);
	printf("  Texture Count: %d\n", $cnt);
	printf("======================================\n\n");

	// Validate input file count matches
	if (count($input_files) != $cnt) {
		printf(
			"ERROR: Input file count (%d) doesn't match source texture count (%d)\n",
			count($input_files),
			$cnt
		);
		return;
	}

	// Extract header (everything before first FTX0 chunk)
	$ftex_header = substr($source, 0, $hdsz);
	printf("Extracted FTEX header: 0x%x bytes\n\n", strlen($ftex_header));

	// Parse source FTX0 chunks to extract GNF headers
	$source_gnf_headers = array();
	$st = $hdsz;
	for ($i = 0; $i < $cnt; $i++) {
		if (substr($source, $st, 4) !== 'FTX0') {
			printf("ERROR: Source FTX0 chunk %d not found at 0x%x\n", $i, $st);
			return;
		}

		$sz1 = str2int($source, $st + 4, 4);
		$sz2 = str2int($source, $st + 8, 4);

		// GNF header starts at $st + $sz2, size is 0x100
		$gnf_header = substr($source, $st + $sz2, 0x100);
		$source_gnf_headers[$i] = $gnf_header;

		printf("Extracted GNF header %d from 0x%x (0x%x bytes)\n", $i, $st + $sz2, strlen($gnf_header));

		// Move to next FTX0 chunk
		$st += ($sz1 + $sz2);
	}
	printf("\n");

	// Extract trailing 16 bytes from source
	$trailing_bytes = '';
	if (strlen($source) >= $st + 16) {
		$trailing_bytes = substr($source, $st, 16);
		printf("Extracted trailing bytes: 0x%x bytes from 0x%x\n\n", strlen($trailing_bytes), $st);
	}

	$textures = array();

	foreach ($input_files as $idx => $fname) {
		printf("------ Texture %d ------\n", $idx);
		printf("Processing: %s\n", basename($fname));

		// Load clut file
		$data = load_clutfile($fname);
		if ($data === 0) {
			printf("ERROR: Cannot load %s\n", $fname);
			continue;
		}

		$w = $data['w'];
		$h = $data['h'];
		$rgba = $data['pix'];

		printf("  Size: %d x %d\n", $w, $h);

		// Repack to BC4
		$bc4_data = im_bc4_repack($rgba, $w, $h);

		// Use the original GNF header from source (not building a new one)
		if (!isset($source_gnf_headers[$idx])) {
			printf("ERROR: No source GNF header for texture %d\n", $idx);
			continue;
		}
		$gnf_header = $source_gnf_headers[$idx];

		// Build FTX0 chunk with original GNF header
		$ftx0 = build_ftx0($gnf_header, $bc4_data);

		$textures[] = array(
			'ftx0' => $ftx0,
			'w'    => $w,
			'h'    => $h,
		);

		printf("  BC4 compressed size: 0x%x (%d bytes)\n", strlen($bc4_data), strlen($bc4_data));
		printf("  Using original GNF header: 0x%x (%d bytes)\n", strlen($gnf_header), strlen($gnf_header));
		printf("  FTX0 chunk size:     0x%x (%d bytes)\n\n", strlen($ftx0), strlen($ftx0));
	}

	if (empty($textures)) {
		printf("ERROR: No valid textures to pack\n");
		return;
	}

	// Build final FTEX: original header + new FTX0 chunks
	printf("======================================\n");
	printf("Building FTEX file...\n");

	$ftex = $ftex_header;

	// Append all FTX0 chunks
	foreach ($textures as $idx => $tex) {
		printf("  Appending texture %d: 0x%x bytes\n", $idx, strlen($tex['ftx0']));
		$ftex .= $tex['ftx0'];
	}

	// Append trailing bytes from source if they exist
	if (!empty($trailing_bytes)) {
		printf("  Appending trailing bytes: 0x%x bytes\n", strlen($trailing_bytes));
		$ftex .= $trailing_bytes;
	}

	// Save output
	printf("\nSaving: %s\n", $output_file);
	printf("  Total size: 0x%x (%d bytes)\n", strlen($ftex), strlen($ftex));
	save_file($output_file, $ftex);
	printf("======================================\n");
	printf("Repack complete!\n");
	printf("======================================\n");

	return;
}

aegis_repack($argv);

/*
Usage:
  php ps4_13sent_FTEX_repack.php <source.ftx> <output.ftx> <input1.gnf> [input2.gnf ...]

Description:
  Repacks .gnf texture files into a FTEX (.ftx) asset file.
  Uses an existing FTEX file as a template for the header structure.
  The number of input .gnf files must match the texture count in source.ftx.

Parameters:
  <source.ftx>   - Original FTEX file (used as header template)
  <output.ftx>   - Output FTEX file with repacked textures
  <inputN.gnf>   - Input texture files (extracted .gnf or .rgba/.clut format)

Supported formats:
  0x26 = BC4 (grayscale/alpha)

Example:
  # Extract textures from original
  php ps4_13sent_FTEX.php CongenialFont.ftx
  # Output: CongenialFont.0.gnf

  # Edit the texture (convert to PNG, edit, convert back to .gnf format)
  php img_clut2png.php CongenialFont.0.gnf
  # Edit CongenialFont.0.gnf.png...
  php img_png2clut.php CongenialFont.0.gnf.png CongenialFont.0.gnf

  # Repack using original as template
  php ps4_13sent_FTEX_repack.php CongenialFont.ftx CongenialFont_modified.ftx CongenialFont.0.gnf
 */
