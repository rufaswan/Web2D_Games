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
class zipstore
{
	private function error( $msg )
	{
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,10);
		return trigger_error($msg, E_USER_ERROR);
	}

	private function chdir( $new, $old=0 )
	{
		$newp = realpath($new);
		$oldp = '';

		if ( ! $newp )
			return $this->error('[FAILED] realpath='.$new);
		if ( ! empty($old) )
			$oldp = realpath($old);

		$t = chdir($newp);
		if ( ! $t )
			return $this->error('[FAILED] chdir='.$newp);
		return $oldp;
	}

	private function chr( $i )
	{
		$b0 = ($i >> 0) & 0xff;
		return chr($b0);
	}

	private function chr2( $i )
	{
		$b0 = ($i >> 0) & 0xff;
		$b1 = ($i >> 8) & 0xff;
		return chr($b0) . chr($b1);
	}

	private function chr4( $i )
	{
		$b0 = ($i >>  0) & 0xff;
		$b1 = ($i >>  8) & 0xff;
		$b2 = ($i >> 16) & 0xff;
		$b3 = ($i >> 24) & 0xff;
		return chr($b0) . chr($b1) . chr($b2) . chr($b3);
	}
	//////////////////////////////
	private function pack( $zipfn, &$list, $dir )
	{
		$fp = fopen($zipfn, 'wb');
		if ( ! $fp )  return;

		$cent = '';
		$pos  = 0;
		$cnt  = 0;
		$c1 = array($this, 'chr');
		$c2 = array($this, 'chr2');
		$c4 = array($this, 'chr4');
		foreach ( $list as $k => $v )
		{
			if ( isset($v['data']) )
				$data = &$v['data'];
			else
				$data = file_get_contents($dir .'/'. $v['name']);
			$len  = strlen($v['name']);
			$size = strlen($data);
			$crc  = crc32 ($data);

			$pk3  = "PK\x03\x04"; // 00  magic
			$pk3 .= $c2(10);    // 04  ver 1.0
			$pk3 .= $c2( 0);    // 06  flags
			$pk3 .= $c2( 0);    // 08  compression
			$pk3 .= $c2( 0);    // 0a  mod time
			$pk3 .= $c2( 0);    // 0c  mod date
			$pk3 .= $c4($crc ); // 0e  crc 32
			$pk3 .= $c4($size); // 12  fsize compressed
			$pk3 .= $c4($size); // 16  fsize uncompressed
			$pk3 .= $c2($len ); // 1a  fname len
			$pk3 .= $c2( 0);    // 1c  extra field size
			$pk3 .= $v['name'];
			$pk3 .= $data;
				fwrite($fp, $pk3);

			$pk1  = "PK\x01\x02"; // 00  magic
			$pk1 .= $c1(10);    // 04  made by ver 1.0
			$pk1 .= $c1( 0);    // 05  made by host (msdos)
			$pk1 .= $c2(10);    // 06  ver 1.0
			$pk1 .= $c2( 0);    // 08  flags
			$pk1 .= $c2( 0);    // 0a  compression
			$pk1 .= $c2( 0);    // 0c  mod time
			$pk1 .= $c2( 0);    // 0e  mod date
			$pk1 .= $c4($crc ); // 10  crc 32
			$pk1 .= $c4($size); // 14  fsize compressed
			$pk1 .= $c4($size); // 18  fsize uncompressed
			$pk1 .= $c2($len ); // 1c  fname len
			$pk1 .= $c2( 0);    // 1e  extra field size
			$pk1 .= $c2( 0);    // 20  comment len
			$pk1 .= $c2( 0);    // 22  disk .z01 .z02 ...
			$pk1 .= $c2( 0);    // 24  internal attr
			$pk1 .= $c4( 0);    // 26  external attr (made by host)
			$pk1 .= $c4($pos ); // 2a  PK34 offset
			$pk1 .= $v['name'];
				$cent .= $pk1;

			//printf("%8x , %8x , %s @ %s\n", $pos, $fsize, $zipn, $fnm);
			$pos += strlen($pk3);
			$cnt++;
		} // foreach ( $list as $fsize => $flist )

		fwrite($fp, $cent);
		$len = strlen($cent);

		$pk5  = "PK\x05\x06"; // 00  magic
		$pk5 .= $c2(0);    // 04  count .z01 .z02...
		$pk5 .= $c2(0);    // 06  disk w/central dir
		$pk5 .= $c2($cnt); // 08  disk  entry
		$pk5 .= $c2($cnt); // 0a  total entry
		$pk5 .= $c4($len); // 0c  central dir len
		$pk5 .= $c4($pos); // 10  central dir offset
		$pk5 .= $c2(0);    // 14  comment len

		fwrite($fp, $pk5);
		fclose($fp);
		return;
	}

	private function addfile( &$list, $dir )
	{
		$func = __FUNCTION__;
		foreach ( scandir($dir) as $ent )
		{
			if ( $ent[0] === '.' )
				continue;

			$full = $dir . '/' . $ent;
			if ( is_dir($full) )
				$this->$func($list, $full);

			if ( is_file($full) )
			{
				$p = strrpos($ent, '.');
				if ( $p === false )
					continue;
				$ext = substr($ent, $p+1);

				$t = array(
					'name' => substr($full, 2),
					'size' => filesize($full),
					'ext'  => strtolower($ext),
				);
				$list[] = $t;
			}
		} // foreach ( scandir($dir) as $d )
	}
	//////////////////////////////
	public function sort_size_asc( $a, $b )
	{
		if ( $a['size'] !== $b['size'] )
			return $a['size'] - $b['size'];
		return $a['ext'] > $b['ext'];
	}

	public function scan( $dir )
	{
		if ( ! is_dir($dir) )
			return 0;
		$pwd = $this->chdir($dir, '.');

		$list = array();
		$this->addfile($list, '.');

		$this->chdir($pwd);
		return $list;
	}

	public function save( $zipfx, &$list, $dir='.' )
	{
		if ( empty($zipfx) || empty($list) )
			return;

		$p = strrpos($zipfx, '.');
		if ( $p === false )
			return;

		$nam = substr($zipfx, 0, $p);
		$ext = substr($zipfx, $p+1);
		if ( count($list) > 0xfff0 )
		{
			$id = 0;
			while ( ! empty($list) )
			{
				$l = array_splice($list, 0, 0xfff0);
				$fn = sprintf('%s.%d.%s', $nam, $id, $ext);
					$id++;
				$this->pack($fn, $l, $dir);
			} // while ( ! empty($list) )
		}
		else
		{
			$fn = sprintf('%s.%s', $nam, $ext);
			$this->pack($fn, $list, $dir);
		}
		return;
	}
	//////////////////////////////
} // class zip
