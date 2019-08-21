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
$gp_init["web_title"] = "Alice SYSTEM 3.0 WEB";
$gp_init["sco_head"] = 2;
$gp_init["path_ogg"] = GAME . "/audio/%02d.ogg";
$gp_init["path_sco"] = GAME . "/adisk_dat/%03d.sco";
$gp_init["path_img"] = GAME . "/acg_dat/%03d.png";
$gp_init["path_spr"] = GAME . "/acg_dat/%03d-%d.png";
$gp_init["path_mid"] = GAME . "/amus_dat/%03d.ogg";
$gp_init["path_dat"] = GAME . "/amap_dat/%03d.dat";

$gp_key = array(
	'up'    => (1 << 0),
	'down'  => (1 << 1),
	'left'  => (1 << 2),
	'right' => (1 << 3),
	'enter' => (1 << 4), // joy = A , mouse = left
	'space' => (1 << 5), // joy = B , mouse = right
);

$gp_img_meta = img_meta( ROOT ."/". GAME ."/acg_dat/meta.txt" );
