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

class BakFile
{
	private $fname;
	private $fhash;
	private $fsize;
	private $has_bak;

	//////////////////////////////
	public $file;

	public function load( $fname )
	{
		$this->has_bak = false;
		$this->file    = '';
		$this->fname   = $fname;
		$this->fsize   = 0;
		$this->fhash   = '';
		if ( is_dir($fname) )
			return;

		if ( is_file("$fname.bak") )
		{
			$this->has_bak = true;
			$this->file    = file_get_contents("$fname.bak");
			$this->fsize   = strlen($this->file);
			$this->fhash   =   sha1($this->file);
			return;
		}

		if ( is_file($fname) )
		{
			$this->has_bak = false;
			$this->file    = file_get_contents($fname);
			$this->fsize   = strlen($this->file);
			$this->fhash   =   sha1($this->file);
			return;
		}
		return;
	}

	public function save()
	{
		if ( empty($this->file) )
			return;

		// no changes == skip save
		if ( strlen($this->file) === $this->fsize && sha1($this->file) === $this->fhash )
			return;

		// make a .BAK copy
		if ( ! $this->has_bak )
			rename($this->fname, $this->fname . '.bak');

		file_put_contents($this->fname, $this->file);
		return;
	}

	public function is_empty()  { return empty($this->file); }
	public function filesize( $cur )
	{
		if ( $cur )
			return strlen($this->file);
		else
			return $this->fsize;
	}
} // class BakFile
