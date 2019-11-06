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
$gp_init["web_title"] = "Alice SYSTEM 3.5 WEB";

// single SA DA RA MA BA
$gp_init["path_bgm"] = GAME . "/ba/%03d";
//$gp_init["path_mid"] = GAME . "/ma/%03d";
$gp_init["path_mid"] = GAME . "/ma/%03d";
$gp_init["path_sa"] = GAME . "/sa/%03d";
$gp_init["path_da"] = GAME . "/da/%03d";

// sets   GA-GB-GC  WA-WB
$gp_init["path_wav"] = GAME . "/wa/%03d/%05d";
$gp_init["path_ga"] = GAME . "/ga/%03d/%05d";


$gp_init["path_data"] = GAME . "/data/%s";

$gp_key = array(
	'up'    => (1 << 0),
	'down'  => (1 << 1),
	'left'  => (1 << 2),
	'right' => (1 << 3),
	'enter' => (1 << 4), // joy = A , mouse = left
	'space' => (1 << 5), // joy = B , mouse = right
	'esc'   => (1 << 6), // joy = C
	'tab'   => (1 << 7), // joy = D
);

define("PATH_META", ROOT ."/". GAME ."/ga/meta.txt");
$sco_input = "sco_keyboard";
