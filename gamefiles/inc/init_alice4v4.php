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
$gp_init["web_title"] = "Alice-soft SYSTEM 4.0 WEB";

// BA.ald -> Sound.afa
switch ( $gp_init["sound"] )
{
	case "ba.ald":     $t1="ba";    $t2="%03d"; break;
	case "ba.afa":     $t1="ba";    $t2="%s";   break;
	case "sound.afa":  $t1="sound"; $t2="%s";   break;
}
$gp_init["path_bgm"] = GAME . "/$t1/$t2.ogg";

// GA.ald -> CG.afa
switch ( $gp_init["cg"] )
{
	case "ga.ald":  $t1="ga"; $t2="%03d/%05d"; break;
	case "ga.afa":  $t1="ga"; $t2="%s"; break;
	case "cg.afa":  $t1="cg"; $t2="%s"; break;
}
$gp_init["path_img"] = GAME . "/$t1/$t2.png";

// WA.ald -> Voice.afa
switch ( $gp_init["voice"] )
{
	case "wa.ald":     $t1="wa";    $t2="%03d/%05d"; break;
	case "wa.afa":     $t1="wa";    $t2="%s"; break;
	case "voice.afa":  $t1="voice"; $t2="%s"; break;
}
$gp_init["path_wav"] = GAME . "/$t1/$t2.ogg";


$gp_init["path_code"] = GAME ."/". $gp_init["ain"] . "/codefunc/%s.txt";
define("PATH_CG"   , ROOT ."/". GAME ."/cg_ga.txt");
define("PATH_SOUND", ROOT ."/". GAME ."/sound_ba.txt");
define("PATH_VOICE", ROOT ."/". GAME ."/voice_wa.txt");
