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
declare( strict_types=1 );

class sh
{
	// return string[]
	// if count(string[]) == 1, return string[0]
	// if count(string[]) == 0, return string ''
	public static function exec()
	{
		$args = func_get_args();
		$fmt = array_shift($args);
		$cmd = escapeshellcmd( vsprintf($fmt, $args) );

		$out = [];
		exec($cmd, $out);

		if ( ! isset($out[0]) ) // empty array
			return '';
		if ( ! isset($out[1]) ) // one item only
			return $out[0];
		return $out;
	}

	//////////////////////////////

	public static function which( string $cmd, bool $is_err=true ) : string
	{
		$sh = self::exec('which %s', $cmd);
		if ( empty($sh) && $is_err )
			tool::error('cmd not found', $cmd);
		return "$sh";
	}

	public static function which_im( string $cmd ) : string
	{
		$bit = ['q64', 'q32', 'q16', 'q8' ];
		$ver = ['im8', 'im7', 'im6', 'im5'];
		foreach ( $bit as $bv )
		{
			foreach ( $ver as $vv )
			{
				$bin = sprintf('%s-%s.%s', $cmd, $vv, $bv);
				$res = self::which($bin, false);
				if ( ! empty($res) )
					return $res;
			} // foreach ( $ver as $vv )
		} // foreach ( $bit => $bv )

		tool::error('imagemagick not found', $cmd);
	}

	public static function im_mean( string $fname, array $im ) : float
	{
		$cmd = '%s'
			. '"%s"'
			. '-colorspace Gray'
			. '-format "%%[fx:mean]" info:';
		$mean = self::exec($cmd, $im, $fname);
		return (0.0 + $mean);
	}

	public static function wget( string $url, string $outp='' ) : string
	{
		$dom = parse_url($url);
		if ( ! isset($dom['host']) )
			return '';
		self::which('wget');

		$tmp = ( empty($outp) ) ? tempnam(sys_get_temp_dir(), 'wget') : $outp;
		$wget = 'wget'
			. ' --quiet'
			. ' --random-wait'
			. ' --no-config'
			. ' --no-hsts'
			. ' --no-check-certificate'
			. ' --timeout=60'
			. ' --tries=5'
			. ' --max-redirect=1'
			. ' --user-agent="Mozilla/5.0 (Linux; Android 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.66 Mobile Safari/537.36"'
			. ' --output-document="%s"'
			. ' --referer="%s"'
			. ' "%s"';
		self::exec($wget, $tmp, $url, $url);

		// download failed = false
		$size = filesize($tmp);
		if ( $size < 1 )
		{
			unlink($tmp);
			return '';
		}

		// return file content , delete tmp file
		if ( empty($outp) )
		{
			$res = file_get_contents($tmp);
			unlink($tmp);
			return $res;
		}

		// file saved = true
		return 'OK';
	}

	public static function xprop() : string
	{
		// _NET_CURRENT_DESKTOP = 2
		self::which('xprop');
		$xprop = 'xprop'
			. ' -root'
			. ' -notype _NET_CURRENT_DESKTOP';
		return self::exec($xprop);
	}

	public static function ffprobe( string $fname ) : array
	{
		// keep this in mind: Headers can lie, but data doesn't.
		// A cover image has exactly 1 packet.
		// A real video has multiple packets.
		$dur = [
			'duration' => -1 ,
			'video'    => -1 ,
			'audio'    => -1 ,
		];
		if ( ! is_file($fname) )
			return [];
		self::which('ffprobe');

		//. ' -show_format'  // format_name=<container>
		//. ' -show_streams' //  codec_type=video audio subtitle
		$ffprobe = 'ffprobe'
			. ' -find_stream_info'
			. ' -count_packets'
			. ' -loglevel      quiet'
			. ' -show_entries  format=duration:stream=codec_type,nb_read_packets'
			. ' -print_format  csv'
			. ' "%s"';
		$res = self::exec($ffprobe, $fname);
		if ( ! is_array($res) )
			return [];

		//var_dump($res);
		while ( ! empty($res) )
		{
			// stream,video,1
			// stream,audio,1000
			// format,60.000000
			$val = explode(',', array_shift($res));
			if ( $val[0] === 'format' )
				$dur['duration'] = (int)$val[1];
			else
			if ( $val[0] === 'stream' )
			{
				$k = $val[1];
				$v = &$dur[$k];
				$v = max($v, (int)$val[2]);
			}
		} // while ( ! empty($res) )

		return $dur;
	}

	public static function cd( string $dir ) : void
	{
		$t = chdir($dir);
		if ( ! $t )
			tool::error('[FAILED] self::cd()', $dir);
	}

	public static function pwd() : string
	{
		$t = realpath('.');
		if ( ! $t )
			tool::error('[FAILED] self::pwd()');
		return $t;
	}

	public static function realbase( string $ent ) : string
	{
		$real = realpath($ent);
		if ( empty($real) )
			return '';

		$base = basename($real);
		if ( empty($base)     )  return '';
		if ( $base[0] === '.' )  return '';
		return $base;
	}
} // class sh
