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
 */
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";
require "quad.inc";

$gp_pix = array();

function qtexture( &$pix, $pfx, $tid )
{
	global $gp_pix;
	if ( ! isset($gp_pix[$tid]) )
	{
		$fn = sprintf("%s.%d.", $pfx, $tid);
		$img = load_clutfile($fn);
		if ( $img === 0 )
			return php_error("NOT FOUND %s", $fn);

		$gp_pix[$tid] = array('w'=>0,'h'=>0,'d'=>'');
		if ( isset( $img['cc'] ) )
		{
			$gp_pix[$tid]['w'] = $img['w'];
			$gp_pix[$tid]['h'] = $img['h'];
			$gp_pix[$tid]['d'] = clut2rgba($img['pal'], $img['pix'], false);
		}
		else
		{
			$gp_pix[$tid]['w'] = $img['w'];
			$gp_pix[$tid]['h'] = $img['h'];
			$gp_pix[$tid]['d'] = $img['pix'];
		}
	} // if ( ! isset($gp_pix[$tid]) )

	printf("== qtexture( %s, %d ) = %x x %x\n", $pfx, $tid, $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
	$pix['src']['w'] = $gp_pix[$tid]['w'];
	$pix['src']['h'] = $gp_pix[$tid]['h'];
	$pix['src']['pix'] = &$gp_pix[$tid]['d'];
	$pix['src']['pal'] = "";
	return;
}

function qrender( &$frame, $pfx, $id )
{
	printf("== qrender( %s , %x )\n", $pfx, $id);
	//print_r($frame);

	// ERROR : computer run out of memory
	// required CANV_S is too large for backgrounds
	//   auto canvas size detection
	//   auto move origin 0,0 from middle-center to top-left
	//   auto trim is DISABLED
	$CANV_S = 0;
	$is_mid = false;
	foreach ( $frame as $pk => $pv )
	{
		if ( ! isset($pv['DstQuad']) )
			continue;

		// detect origin and canvas size
		foreach ( $pv['DstQuad'] as $dst )
		{
			$b = abs($dst);
			if ( $b > $CANV_S )  $CANV_S = $b;
			if ( $dst < 0 )
				$is_mid = true;
		}
	} // foreach ( $fv as $pk => $pv )

	$ceil   = ( $is_mid ) ? int_ceil($CANV_S*2, 16) : int_ceil($CANV_S, 16);
	$origin = ( $is_mid ) ? $ceil / 2 : 0;

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);
	$pix['alpha'] = "alpha_normal";

	foreach ( $frame as $pk => $pv )
	{
		if ( ! isset($pv['DstQuad']) )
			continue;

		// skip non-texture data + custom blending parts
		if ( ! isset($pv['TexID']) || $pv['TexID'] < 0 )
			continue;
		if ( isset($pv['Blend']) )
			continue;

		$pix['vector'] = array(
			array( $pv['DstQuad'][0]+$origin , $pv['DstQuad'][1]+$origin , 1 ),
			array( $pv['DstQuad'][2]+$origin , $pv['DstQuad'][3]+$origin , 1 ),
			array( $pv['DstQuad'][4]+$origin , $pv['DstQuad'][5]+$origin , 1 ),
			array( $pv['DstQuad'][6]+$origin , $pv['DstQuad'][7]+$origin , 1 ),
		);

		$pix['src']['vector'] = array(
			array( $pv['SrcQuad'][0] , $pv['SrcQuad'][1] , 1 ),
			array( $pv['SrcQuad'][2] , $pv['SrcQuad'][3] , 1 ),
			array( $pv['SrcQuad'][4] , $pv['SrcQuad'][5] , 1 ),
			array( $pv['SrcQuad'][6] , $pv['SrcQuad'][7] , 1 ),
		);

		qtexture($pix, $pfx, $pv['TexID']);
		copyquad($pix, 4);
	} // foreach ( $fv as $pk => $pv )

	$fn = sprintf("%s/%04d", $pfx, $id);
	savepix($fn, $pix, false);
	return;
}

function quad( $fname )
{
	// for *.quad only
	if ( stripos($fname, '.quad') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$file = json_decode($file, true);
	if ( empty($file) )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	global $gp_pix;
	$gp_pix = array();

	foreach ( $file['Frame'] as $fk => $fv )
	{
		if ( empty($fv) )
			continue;
		qrender($fv, $pfx, $fk);
	} // foreach ( $file['Frame'] as $fk => $fv )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	quad( $argv[$i] );
