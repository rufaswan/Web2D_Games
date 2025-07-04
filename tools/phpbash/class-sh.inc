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
class sh
{
	public static function error()
	{
		$args = func_get_args();
		$fmt = array_shift($args);
		$cmd = vsprintf($fmt, $args);
		return trigger_error($cmd, E_USER_ERROR);
	}

	public static function exec()
	{
		$args = func_get_args();
		$fmt = array_shift($args);
		$cmd = vsprintf($fmt, $args);
		return exec($cmd);
	}

	public static function which( $cmd, $is_err=true )
	{
		$sh = self::exec('which %s', $cmd);
		if ( empty($sh) && $is_err )
			return sh::error('cmd %s not found', $cmd);
		return $sh;
	}

	public static function which_im( $cmd )
	{
		$bit = array(
			array('q8' , 100.0 / (1 <<  8)),
			array('q16', 100.0 / (1 << 16)),
			array('q32', 100.0 / (1 << 32)),
			array('q64', 100.0 / (1 << 64)),
		);
		$ver = array('im8', 'im7', 'im6', 'im5');
		foreach ( $bit as $bv )
		{
			foreach ( $ver as $vv )
			{
				$bin = sprintf('%s-%s.%s', $cmd, $vv, $bv[0]);
				$res = self::which($bin, false);
				if ( ! empty($res) )
					return array($res, $bv[1]);
			} // foreach ( $ver as $vv )
		} // foreach ( $bit => $bv )

		return sh::error('imagemagick [%s] not found', $cmd);
	}

	public static function im_mean( $fname, $im )
	{
		$cmd = '%s'
			. '"%s"'
			. '-colorspace Gray'
			. '-format "%%[mean]" info:';
		$mean = self::exec($cmd, $im[0], $fname);
		return ($mean * $im[1]);
	}

	public static function wget( $url, $outp='' )
	{
		$dom = parse_url($url);
		if ( ! isset($dom['host']) )
			return '';
		$sh = self::which('wget');

		$tmp = ( empty($outp) ) ? tempnam(sys_get_temp_dir(), 'wget') : $outp;
		$wget = '%s'
			. ' --quiet'
			. ' --random-wait'
			. ' --no-config'
			. ' --no-hsts'
			. ' --no-check-certificate'
			. ' --timeout=60'
			. ' --tries=5'
			. ' --max-redirect=1'
			. ' --user-agent="Mozilla/5.0 (Linux; Android 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.66 Mobile Safari/537.36"'
			. ' --output-document=%s'
			. ' --referer="%s"'
			. ' "%s"';
		$t = self::exec($wget, $sh, $tmp, $url, $url);

		// download failed = false
		$size = filesize($tmp);
		if ( $size < 1 )
		{
			unlink($tmp);
			return 0;
		}

		// return file content , delete tmp file
		if ( empty($outp) )
		{
			$res = file_get_contents($tmp);
			unlink($tmp);
			return $res;
		}

		// file saved = true
		return $size;
	}

	public static function xprop()
	{
		$sh = self::which('xprop');
		$xprop = '%s'
			. ' -root'
			. ' -notype _NET_CURRENT_DESKTOP';
		return self::exec($xprop, $sh);
	}

	public static function pwddir()
	{
		$real = realpath('.');
		if ( $real === false )
			return '';
		$base = basename($real);

		$base = trim($base);
		if ( empty($base) || $base[0] === '.' )
			return '';
		return $base;
	}

	public static function dos2unix( &$path )
	{
		if ( strpos($path, '\\') !== false )
			$path = str_replace('\\', '/', $path);
		$path = rtrim($path, '/');
		return;
	}

	public static function mkdir( $path, $is_file=true )
	{
		self::dos2unix($path);
		$pos = strrpos($path, '/');
		if ( $pos === false )
			return;

		if ( $is_file )
			$dir = substr($path, 0, $pos);
		else
			$dir = $path;

		return @mkdir($dir, 0755, true);
	}

	public static function save( $fname, &$bin )
	{
		if ( empty($bin) )
			return false;

		self::mkdir($fname);
		file_put_contents($fname, $bin);

		// in case stopped halfway , or run out of disk space
		if ( filesize($fname) !== strlen($bin) )
		{
			unlink($fname);
			return false;
		}
		return true;
	}

	public static function copy( $src, $dst )
	{
		$bin = file_get_contents($src);
		return self::save($dst, $bin);
	}

	public static function move( $src, $dst )
	{
		$r = self::copy($src, $dst);
		// do not remove src if save failed
		if ( ! $r )
			return false;

		unlink($src);
		return true;
	}

	public static function decdigit( $int )
	{
		$dig = 1;
		if ( $int < 0 )
		{
			$dig++;
			$int = -$int;
		}
		while ( $int > 0 )
		{
			$dig++;
			$int = (int)($int * 0.1);
		}
		return $dig;
	}

	public static function hexdigit( $int )
	{
		$dig = 1;
		if ( $int < 0 )
		{
			$dig++;
			$int = -$int;
		}
		while ( $int > 0 )
		{
			$dig++;
			$int >>= 4;
		}
		return $dig;
	}
} // class sh
