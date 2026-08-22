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
declare(strict_types=1);

tool::class('jsonpretty');

class quadfile
{
	// barycenter series
	// flatten nested array into 1 simple array
	public static function flatten_var( array &$list, array &$arg ) : void
	{
		$func = [__CLASS__, __FUNCTION__];
		foreach ( $arg as $v )
		{
			if ( is_array($v) )
				$func($list, $v);
			else
				$list[] = $v;
		}
	}

	public static function flatten( array $arg ) : array
	{
		$list = [];
		self::flatten_var($list, $arg);
		return $list;
	}

	// arg = [ x1,y1     ,  x2,y2 ]
	// arg = [[x1,y1]    , [x2,y2]]
	// arg = [[x1,y1,z1] , [x2,y2,z2]]
	public static function cross() : array
	{
		$args = self::flatten( func_get_args() );
		if ( count($args) === 4 )
		{
			list($bx,$by,$cx,$cy) = $args;
			$bz = 1.0;
			$cz = 1.0;
		}
		else
		if ( count($args) === 6 )
			list($bx,$by,$bz,$cx,$cy,$cz) = $args;
		else
			return [];

		// | ax bx cx |
		// | ay by cy |
		// | az bz cz |
		$ax = ($by * $cz) - ($cy * $bz);
		$ay = ($bz * $cx) - ($cz * $bx);
		$az = ($bx * $cy) - ($cx * $by);
		return [$ax, $ay, $az];
	}

	public static function cross_2d() : array
	{
		$crx = self::cross( func_get_args() );
		if ( empty($crx) )
			return [];

		list($x,$y,$z) = $crx;
		$z_inv = 1.0 / $z;

		// convert x,y,z (3d) to x,y (2d)
		$x *= $z_inv;
		$y *= $z_inv;
		//$z *= $z_inv; // $z == 1
		return [$x, $y];
	}

	public static function triad_area() : float
	{
		$args = self::flatten( func_get_args() );
		if ( count($args) !== 6 )
			return 0.0;

		// https://www.mathopenref.com/coordtrianglearea.html
		// | ax bx cx |
		// | ay by cy |
		// 2r = ax(by-cy) + bx(cy-ay) + cx(ay-by)
		list($ax,$ay,$bx,$by,$cx,$cy) = $args;
		$r1 = $ax * ($by - $cy);
		$r2 = $bx * ($cy - $ay);
		$r3 = $cx * ($ay - $by);
		$r  = 0.5 * ($r1 + $r2 + $r3);
		return abs($r);
	}
	//////////////////////////////
	// concave/convex quad series

	//////////////////////////////
	// xywh series , for dstquad
	public static function xywh_quad( int $w, int $h, bool $is_rotate=false ) : array
	{
		if ( ! $is_rotate )
			$quad = [
				0  , 0  ,
				$w , 0  ,
				$w , $h ,
				0  , $h ,
			];
		else
			$quad = [
				0  , $h ,
				0  , 0  ,
				$w , 0  ,
				$w , $h ,
			];
		return $quad;
		//  normal     flipx      flipy
		//  0,1  2,3 | 2,3  0,1 | 6,7  4,5
		//  6,7  4,5 | 4,5  6,7 | 0,1  2,3
	}

	// 2x2 matrix = scale + rotate z
	public static function xywh_mat2( array &$quad, array $m2 ) : void
	{
		if ( empty($m2) || count($m2) !== 4 )
			return;
		//          | a b |
		// | 0 1 |  | c d | = | 0a+1c  0b+1d |
		for ( $i=0; $i < 8; $i += 2 )
		{
			$x = $quad[$i+0];
			$y = $quad[$i+1];
			$quad[$i+0] = $x * $m2[0] + $y * $m2[2];
			$quad[$i+1] = $x * $m2[1] + $y * $m2[3];
		}
	}

	// move need 3x3 matrix
	public static function xywh_move( array &$quad, float $mx, float $my ) : void
	{
		for ( $i=0; $i < 8; $i += 2 )
		{
			$quad[$i+0] += $mx;
			$quad[$i+1] += $my;
		}
	}
	//////////////////////////////
	// quad file series
	public static function attach( string $type, int $id ) : array
	{
		$attach = [
			'type' => $type,
			'id'   => $id,
		];
		return $attach;
	}

	// mixed $v
	public static function list_add( array &$list, int $k, &$v ) : void
	{
		for ( $i=0; $i <= $k; $i++ )
		{
			// pad missing entries
			if ( ! isset($list[$i]) )
				$list[$i] = 0;

			// add entry
			if ( $i === $k )
				$list[$i] = $v;
		}

		// unorder keys is std::map    "{...}"
		// key sort ->     std::vector "[...]"
		ksort($list);
	}

	public static function blend( string $mode ) : array
	{
		switch ( $mode )
		{
			case 'normal':
				$blend = [
					'name' => 'normal',
					'mode_rgb'   => ['FUNC_ADD' , 'SRC_ALPHA' , 'ONE_MINUS_SRC_ALPHA'],
					'mode_alpha' => ['FUNC_ADD' , 'ONE'       , 'ONE_MINUS_SRC_ALPHA'],
					'debug' => [
						'SRC.rgb * SRC.a + DST.rgb * (1 - SRC.a)',
						'SRC.a           + DST.a   * (1 - SRC.a)',
					],
				];
				return $blend;

			case 'add':
			case 'addition':
				$blend = [
					'name' => 'add',
					'mode_rgb'   => ['FUNC_ADD' , 'ONE' , 'ONE'],
					'mode_alpha' => ['FUNC_ADD' , 'ONE' , 'ONE'],
					'debug' => 'SRC + DST',
				];
				return $blend;

			case 'sub':
			case 'subtract':
				$blend = [
					'name' => 'subtract',
					'mode_rgb'   => ['FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE'],
					'mode_alpha' => ['FUNC_REVERSE_SUBTRACT' , 'ONE' , 'ONE'],
					'debug' => '-SRC + DST',
				];
				return $blend;

			case 'mult':
			case 'multiply':
				$blend = [
					'name' => 'multiply',
					'mode_rgb'   => ['FUNC_ADD' , 'DST_COLOR' , 'ZERO'],
					'mode_alpha' => ['FUNC_ADD' , 'DST_ALPHA' , 'ZERO'],
					'debug' => 'SRC * DST',
				];
				return $blend;

			case 'avg':
			case 'average':
				$blend = [
					'name' => 'average',
					'mode_rgb'   => ['FUNC_ADD' , 'CONSTANT_ALPHA' , 'CONSTANT_ALPHA'],
					'mode_alpha' => ['FUNC_ADD' , 'CONSTANT_ALPHA' , 'CONSTANT_ALPHA'],
					'color' => '#ffffff80',
					'debug' => 'SRC * 0.5 + DST * 0.5',
				];
				return $blend;
		} // switch ( $mode )
		return [];
	}
	//////////////////////////////
	public static function idtag_decode_token( array &$token, bool $has_key ) : string
	{
		$func = [__CLASS__, __FUNCTION__];
		$tmp  = [];
		$key  = [];
		$val  = [];
		$is_key = $has_key;
		while ( ! empty($token) )
		{
			$t = array_shift($token);
			switch ( $t )
			{
				case '[':
					$tk = implode(' ', $key);
					$tv = $func($token,  false);
					$tmp[] = sprintf(' "%s" : [ %s ] ', $tk, $tv);
					$key = [];
					break;
				case '{':
					$tk = implode(' ', $key);
					$tv = $func($token,  true);
					$tmp[] = sprintf(' "%s" : { %s } ', $tk, $tv);
					$key = [];
					break;
				case ']':
					break 2;
				case '}':
					break 2;

				case "\x00":
					$tk = implode(' ', $key);
					$tv = implode(' ', $val);
					$key = [];
					$val = [];
					if ( empty($tv) )
						continue;
					if ( $has_key )
					{
						$tmp[] = sprintf(' "%s" : "%s" ', $tk, $tv);
						$is_key = true;
					}
					else
					{
						$tmp[] = sprintf(' "%s" ', $tv);
						$is_key = false;
					}
					break;
				case ':':
					$is_key = false;
					break;
				default:
					if ( $is_key )
						$key[] = $t;
					else
						$val[] = $t;
					break;
			} // switch ( $t )
		} // while ( ! empty($token) )

		return implode(" , ", $tmp);
	}

	public static function idtag_decode( string $idtag ) : array
	{
		$idtag = str_replace("\n", " \x00 ", $idtag." \n");
		$token = preg_split('|[\s]+|', trim($idtag));

		$json = '{ "tag" : { ';
		$json .= self::idtag_decode_token($token, true);
		$json .= '} }';
		$dec = json_decode($json, true);
		if ( empty($dec) )
		{
			tool::warning('json_decode failed');
			return [];
		}
		return $dec;
	}

	public static function load_idtag( string $name ) : array
	{
		// $(dirname $0)/idtag/*$name*.txt;
		$list = get_required_files();
		$dir  = dirname($list[0]) . '/idtag';
		$preg = sprintf('|.*%s.*\.txt$|i', $name);
		$fn   = tool::find($dir, $preg);
		if ( empty($fn) )
			return '';

		$file = file_get_contents($fn);
		return self::idtag_decode($file);
	}

	public static function save( string $pfx, array &$quad ) : void
	{
		if ( empty($quad) )
			return;
		//anim_allkeys($quad);

		$json = jsonpretty::encode($quad);
		tool::save("$pfx.quad", $json);
	}
} // class quadfile
