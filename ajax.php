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
if ( ! defined("GAME") )  exit();

header("Content-Type:text/html; charset={$gp_init['charset']};");
$ajax_html = "";
$engine = "exec_{$gp_init['engine']}";
$exec_run = true;
require ROOT . "/inc/$engine.php";
init_cheat();
//srand(0);
srand( time() );

if ( TRACE_OB )
{
	ob_start();
	while ( $exec_run )
		$engine( $gp_pc["pc"][0], $gp_pc["pc"][1], $exec_run );
	trace("html %d bytes", strlen($ajax_html) );
	$log = ob_get_clean();
	file_put_contents(SAVE_FILE . "log", $log, FILE_APPEND);
}
else
{
	while ( $exec_run )
		$engine( $gp_pc["pc"][0], $gp_pc["pc"][1], $exec_run );
	trace("html %d bytes", strlen($ajax_html) );
}

pc_save( "pc", $gp_pc );
echo $ajax_html;
