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
require 'class-s3tc.inc';

//define('DRY_RUN', true);

// GNF surface format constants
define('GNF_FMT_BC4',  0x26);
define('GNF_FMT_ARGB', 0x0a); // kSurfaceFormat8_8_8_8

//////////////////////////////
// GNF header helpers
//
// GNF header layout (0x100 bytes):
//   0x00: "GNF " magic
//   0x04: offset from byte 8 to pixel data
//   0x08: version
//   0x09: count
//   0x14: $b1 = ccccccss ssssmmmm mmmmmmmm --------
//          s = surface format (bits 25:20)
//          c = channel type (bits 31:26)
//   0x18: $b2 = -ssshhhh hhhhhhhh hhwwwwww wwwwwwww
//          w = width (bits 13:0), h = height (bits 27:14)

function gnf_detect_format($gnf_header)
{
	if (substr($gnf_header, 0, 4) !== 'GNF ')
		return -1;
	$b1 = str2int($gnf_header, 0x14, 4);
	return ($b1 >> 20) & 0x3f;
}

function gnf_update_format(&$gnf_header, $fmt)
{
	$b1 = str2int($gnf_header, 0x14, 4);
	$b1 = ($b1 & ~(0x3f << 20)) | (($fmt & 0x3f) << 20);
	str_update($gnf_header, 0x14, chrint($b1, 4));
}

function gnf_format_name($fmt)
{
	$names = array(
		GNF_FMT_BC4  => 'BC4',
		GNF_FMT_ARGB => 'ARGB',
	);
	if (isset($names[$fmt]))
		return $names[$fmt];
	return sprintf('UNKNOWN(0x%02x)', $fmt);
}

//////////////////////////////
// Reverse of gnf_swizzled_bc - re-swizzle RGBA back to swizzled order
// Operates on 4x4 pixel tiles arranged in 8x8 tile groups with morton order
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
// Channel conversion functions

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

// Convert RGBA channel order to ARGB channel order
function rgba_to_argb(&$rgba)
{
	printf("== rgba_to_argb\n");
	$argb = '';
	$len = strlen($rgba);
	for ($i = 0; $i < $len; $i += 4) {
		$argb .= $rgba[$i + 3]; // A
		$argb .= $rgba[$i + 0]; // R
		$argb .= $rgba[$i + 1]; // G
		$argb .= $rgba[$i + 2]; // B
	}
	return $argb;
}

//////////////////////////////
// BC4 encoder - compress grayscale to BC4 format (8 bytes per 4x4 block)
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

// Encode a single 4x4 block of single-channel data (16 bytes -> 8 bytes)
// Used by BC4 (grayscale)
function bc4_encode_block(&$block)
{
	// Find min and max values in the block
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
// Format-specific repack functions
// Each takes RGBA pixel data and returns compressed/formatted pixel data

function im_bc4_repack(&$rgba, $w, $h)
{
	printf("== im_bc4_repack( %x , %x )\n", $w, $h);

	// Re-swizzle RGBA data into BC tile order
	gnf_reswizzle_bc($rgba, $w, $h);

	// Convert RGBA to grayscale
	$gray = rgba_to_gray($rgba);

	// Compress to BC4
	$bc4 = bc4_encode($gray);

	return $bc4;
}

function im_argb_repack(&$rgba, $w, $h)
{
	printf("== im_argb_repack( %x , %x )\n", $w, $h);

	// Re-swizzle using same 4x4-tile / 8x8-macro-block / morton layout as BC
	gnf_reswizzle_bc($rgba, $w, $h);

	// Convert RGBA channel order to ARGB
	$argb = rgba_to_argb($rgba);

	return $argb;
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
	// Parse --force-format flag from arguments
	$force_format = null;
	$filtered = array();
	$filtered[] = $argv[0]; // script name
	$i = 1;
	while ($i < count($argv)) {
		if ($argv[$i] === '--force-format') {
			if ($i + 1 >= count($argv)) {
				printf("ERROR: --force-format requires a value (BC4 or ARGB)\n");
				return;
			}
			$fmt_name = strtoupper($argv[$i + 1]);
			$fmt_map = array(
				'BC4'  => GNF_FMT_BC4,
				'ARGB' => GNF_FMT_ARGB,
			);
			if (!isset($fmt_map[$fmt_name])) {
				printf("ERROR: Unknown format '%s'. Supported: BC4, ARGB\n", $argv[$i + 1]);
				return;
			}
			$force_format = $fmt_map[$fmt_name];
			$i += 2;
		} else {
			$filtered[] = $argv[$i];
			$i++;
		}
	}
	$argv = $filtered;

	if (count($argv) < 4) {
		printf("Usage: php %s [--force-format FORMAT] <source.ftx> <output.ftx> <input1.gnf> [input2.gnf ...]\n", $argv[0]);
		printf("  Repacks .gnf texture files into a .ftx asset file\n");
		printf("  <source.ftx>  - Original FTEX file (used as header template)\n");
		printf("  <output.ftx>  - Output FTEX file with repacked textures\n");
		printf("  <inputN.gnf>  - Input texture files (must match count in source.ftx)\n");
		printf("  --force-format FORMAT  Force output format: BC4 or ARGB\n");
		printf("  Supported formats: BC4, ARGB\n");
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
	if ($force_format !== null)
		printf("  Force Format: %s\n", gnf_format_name($force_format));
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

	// Parse source FTX0 chunks to extract GNF headers and detect formats
	$source_gnf_headers = array();
	$source_formats = array();
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

		// Detect format from GNF header
		$det_fmt = gnf_detect_format($gnf_header);
		$source_formats[$i] = $det_fmt;

		printf(
			"Extracted GNF header %d from 0x%x - format: %s (0x%02x)\n",
			$i,
			$st + $sz2,
			gnf_format_name($det_fmt),
			$det_fmt
		);

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

	// Supported format list for repack
	$supported_formats = array(GNF_FMT_BC4, GNF_FMT_ARGB);

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
		$input_type = isset($data['t']) ? $data['t'] : 'UNKNOWN';

		printf("  Input type: %s\n", $input_type);
		printf("  Size: %d x %d\n", $w, $h);

		// Determine output format: forced or detected from source
		$fmt = ($force_format !== null) ? $force_format : $source_formats[$idx];

		// Validate format is supported
		if (!in_array($fmt, $supported_formats)) {
			printf("ERROR: Unsupported format %s (0x%02x) for texture %d\n", gnf_format_name($fmt), $fmt, $idx);
			printf(
				"  Supported: BC4 (0x%02x), ARGB (0x%02x)\n",
				GNF_FMT_BC4,
				GNF_FMT_ARGB
			);
			continue;
		}

		printf("  Output format: %s\n", gnf_format_name($fmt));

		// If input is RGBA type (magic "RGBA") and output is ARGB, the conversion
		// is handled inside im_argb_repack. For BC formats, RGBA order is correct.
		// (All clut files from this toolchain are RGBA-ordered)

		// Encode according to format
		switch ($fmt) {
			case GNF_FMT_BC4:
				$pixel_data = im_bc4_repack($rgba, $w, $h);
				break;
			case GNF_FMT_ARGB:
				$pixel_data = im_argb_repack($rgba, $w, $h);
				break;
		}

		// Use the original GNF header from source
		if (!isset($source_gnf_headers[$idx])) {
			printf("ERROR: No source GNF header for texture %d\n", $idx);
			continue;
		}
		$gnf_header = $source_gnf_headers[$idx];

		// Update GNF header format if forcing a different format
		if ($force_format !== null && $force_format != $source_formats[$idx]) {
			printf(
				"  Updating GNF header format: %s -> %s\n",
				gnf_format_name($source_formats[$idx]),
				gnf_format_name($force_format)
			);
			gnf_update_format($gnf_header, $force_format);
		}

		// Build FTX0 chunk with GNF header
		$ftx0 = build_ftx0($gnf_header, $pixel_data);

		$textures[] = array(
			'ftx0' => $ftx0,
			'w'    => $w,
			'h'    => $h,
		);

		printf("  Compressed size: 0x%x (%d bytes)\n", strlen($pixel_data), strlen($pixel_data));
		printf("  GNF header:      0x%x (%d bytes)\n", strlen($gnf_header), strlen($gnf_header));
		printf("  FTX0 chunk:      0x%x (%d bytes)\n\n", strlen($ftx0), strlen($ftx0));
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
  php ps4_13sent_FTEX_repack.php [--force-format FORMAT] <source.ftx> <output.ftx> <input1.gnf> [input2.gnf ...]

Description:
  Repacks .gnf texture files into a FTEX (.ftx) asset file.
  Uses an existing FTEX file as a template for the header structure.
  The number of input .gnf files must match the texture count in source.ftx.

  The format for each texture is auto-detected from the source .ftx GNF headers.
  Use --force-format to override and force all textures to a specific format.

Parameters:
  <source.ftx>         - Original FTEX file (used as header template)
  <output.ftx>         - Output FTEX file with repacked textures
  <inputN.gnf>         - Input texture files (extracted .gnf or .rgba/.clut format)
  --force-format FMT   - Force output format for all textures (BC4 or ARGB)

Supported formats:
  0x26 = BC4  (grayscale/alpha, 8 bytes per 4x4 block)
  0x0a = ARGB (raw uncompressed 32bpp, 4 bytes per pixel)

Notes:
  - Input .gnf files with RGBA magic header have pixels in RGBA order.
    For ARGB output, channel order is automatically converted to ARGB.
  - When --force-format changes the format, the GNF header's surface format
    field is updated. Other header fields (tile mode, etc.) are preserved
    from the source and may need manual adjustment for non-BC formats.

Example:
  # Extract textures from original
  php ps4_13sent_FTEX.php CongenialFont.ftx
  # Output: CongenialFont.0.gnf

  # Edit the texture (convert to PNG, edit, convert back to .gnf format)
  php img_clut2png.php CongenialFont.0.gnf
  # Edit CongenialFont.0.gnf.png...
  php img_png2clut.php CongenialFont.0.gnf.png CongenialFont.0.gnf

  # Repack using detected format from source
  php ps4_13sent_FTEX_repack.php CongenialFont.ftx CongenialFont_modified.ftx CongenialFont.0.gnf

  # Repack as raw ARGB (uncompressed)
  php ps4_13sent_FTEX_repack.php --force-format ARGB source.ftx output.ftx input.0.gnf
 */
