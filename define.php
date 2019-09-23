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
header("Content-Type: text/html; charset=utf-8;");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Thu, 1 Jan 1970 12:00:00 GMT");

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
require ROOT . "/inc/funcs.php";

$gp_init = array(
	"engine"  => "dummy",
	"charset" => "utf-8",
	"cheat" => array(),
);
$gp_pc = array(
	"pc"     => array(0,0),
	"var"    => array(),
	"jal"    => array(),
	"bgm"    => array(),
	"select" => array(),
	"stack"  => array(),
	"text"   => array(), // p=box,LANG=text,[opt]
	"div"    => array(), // t=type,p=box,[opt]
	"return" => "",
);
$gp_input = array();

// parse $_GET , $_POST , $_COOKIE
foreach ( $_REQUEST as $key=>$var )
{
	switch ( $key )
	{
		case "game":
			$cfg = initcfg_var( ROOT . "/$var/init.cfg" );
			$gp_init = $cfg + $gp_init;
			define("GAME", $var );
			//define("SAVE", dechex( crc32($var) ) );
			//define("SAVE",  md5($var) );
			//define("SAVE", sha1($var) );
			define("SAVE", preg_replace("|[^0-9a-zA-Z]|", '_', $var) );
			define("SAVE_FILE", ROOT ."/sav/". SAVE .".");
			break;
		case "input":
			$gp_input = explode(',', $var);
			break;
		case "resume":
			$gp_pc = pc_load( "pc" );
			break;
	}
}
//print_r($gp_init);

define("PATH_JQUERY", "inc/jquery-3.4.0.min.js");
define("PATH_JPFONT", "inc/mplus-1mn-063a.ttf");
define("PATH_OGG_1S", "inc/mono-1s.ogg");
define("SJIS_HALF", ROOT . "/inc/sjis_half.inc");
define("SJIS_ASC",  ROOT . "/inc/sjis_ascii.inc");

require ROOT . "/inc/init_{$gp_init["engine"]}.php";
//print_r($gp_init);
