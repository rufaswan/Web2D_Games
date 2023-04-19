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
require "define.php";
header("Content-Type:text/html; charset=" .$gp_init['charset']. ";");
if ( ! defined("GAME") )
	exit();

$ajax_html = "";
require ROOT . "/inc/" . $gp_init['engine'] . "/exec.php";
init_cheat();
srand( $gp_init["srand"] );

$engine = "exec_" . $gp_init['engine'];
if ( TRACE_OB )
{
	ob_start();
	$engine();
	trace("html %d bytes", strlen($ajax_html) );
	$log = ob_get_clean();
	file_put_contents(SAVE_FILE . "log", $log, FILE_APPEND);
}
else
{
	$engine();
	trace("html %d bytes", strlen($ajax_html) );
}

save_savefile( "pc", $gp_pc );
echo $ajax_html;
