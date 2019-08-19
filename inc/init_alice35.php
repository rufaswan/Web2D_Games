<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
$gp_init["web_title"] = "Alice SYSTEM 3.5 WEB";
$gp_init["sco_head"] = 0x20;
$gp_init["path_ogg"] = GAME . "/audio/%02d.ogg";
$gp_init["path_sco"] = GAME . "/sa_ald/%03d/%05d.sco";
$gp_init["path_img"] = GAME . "/ga_ald/%03d/%05d.png";
$gp_init["path_spr"] = GAME . "/ga_ald/%03d/%05d-%d.png";
//$gp_init["path_wav"] = GAME . "/wa_ald/%03d/%05d.wav";
$gp_init["path_wav"] = GAME . "/wa_ald/%03d/%05d.ogg";
//$gp_init["path_mid"] = GAME . "/ma_ald/%03d/%05d.mid";
$gp_init["path_mid"] = GAME . "/ma_ald/%03d/%05d.ogg";
$gp_init["path_dat"] = GAME . "/da_ald/%03d/%05d.dat";

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

$gp_img_meta = img_meta( ROOT ."/". GAME ."/ga_ald/meta.txt" );
