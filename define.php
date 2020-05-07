<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
header("Content-Type: text/html; charset=UTF-8;");
header("Pragma: no-cache");
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
header("Expires: 0");

define("CRLF", "<br>");
define("DEBUG", true);
define("TRACE", true);
define("TRACE_OB", false);
define("ROOT", dirname(__FILE__) );

define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);
require ROOT . "/inc/funcs.php";

$gp_init = array(
	"engine"  => "dummy",
	"charset" => "utf-8",
	"width"   => 300,
	"height"  => 150,
	"cheat"   => array(),
	"srand"   => time(),
);
$gp_pc = array(
	"pc"     => array(),
	"var"    => array(),
	"jal"    => array(),
	"div"    => array(),
	"bgm"    => array(),
	"select" => array(),

	"stack"  => array(),
	"return" => "",
);
$gp_input = array();

// $_REQUEST = $_GET , $_POST , $_COOKIE
foreach ( $_REQUEST as $key=>$var )
{
	switch ( $key )
	{
		case "game":
			$t1 = ROOT ."/$var/init.cfg";
			if ( ! file_exists($t1) )
				break;
			gp_init_cfg($t1);

			define("GAME", $var);
			//$t1 = dechex( crc32(GAME) );
			//$t1 = md5(GAME);
			//$t1 = sha1(GAME);
			$t1 = preg_replace("|[^0-9a-zA-Z\x80-\xff]|" , '_' , GAME);
			define("SAVE", $t1);
			define("SAVE_FILE", ROOT ."/sav/". SAVE .".");
			define("LIST_FILE", SAVE_FILE . "files");
				init_listfile( false );
			break;
		case "input":
			init_input( explode(',', $var) );
			break;
		case "resume":
			$gp_pc = load_savefile( "pc" );
			break;
	}
}
//print_r($gp_init);

define("PATH_JQUERY", "files/jquery-3.4.0.min.js");
define("PATH_JPFONT", "files/mplus-1mn-063a.ttf");
define("PATH_OGG_1S", "files/mono-1s.ogg");
define("SJIS_HALF" , ROOT . "/files/sjis_half.inc");
define("SJIS_ASC"  , ROOT . "/files/sjis_ascii.inc");
define("FUNC_ICONV", "iconv");

require ROOT . "/inc/" . $gp_init['engine'] . "/init.php";
//print_r($gp_init);
