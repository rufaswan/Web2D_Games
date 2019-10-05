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
if ( ! defined("GAME") )  exit("NO GAME\n");

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>HTML 5 audio</title>
	<script src="<?php echo PATH_JQUERY; ?>"></script>
	<style>
		body { background-color:#000; color:#fff; }
		img { background-color:#fff; }
		#ogglist {
			margin-right:200px;
		}
		#thumb {
			text-align:right;
			position:fixed;
			right:0;
			margin:1em;
			z-index:-1;
		}
		button {
			font-size:1em;
			margin: 0.1em;
			padding:0.5em;
		}
	</style>
</head>
<body>

<div id="thumb">
	<img src="<?php echo GAME; ?>/thumb.png" width="200" height="300">
	<br>
	<audio id="bgm" src="" type="audio/ogg" controls autoplay loop>AUDIO</audio>
</div>

<div id="ogglist">
<?php
$path = array(
	"path_ba" => "BGM",
	"path_ma" => "MID",
	//"path_wa" => "WAV",
);
foreach ( $path as $p => $x )
{
	if ( isset( $gp_init[$p] ) )
	{
		$i = 1;
		while(1)
		{
			$ogg = findfile( $gp_init[$p], $i, "dummy", 8 );
			if ( $ogg == "dummy" )
				break;
			printf("<button data='$ogg'>$x %02d</button>\n", $i);
			$i++;
		} // while(1)
	}
} // foreach ( $path as $p => $x )
?>
<p>NOW : <span id="bgmnow"></span></p>
</div>

<script>
var audio = document.getElementById("bgm");

	$("button").click( function(){
		var ogg = $(this).attr("data");
		var now = $("#bgm").attr("src");
		if ( ogg != now )
		{
			$("#bgmnow").empty().append(ogg);
			audio.src = ogg;
			audio.play();
		}
	});

</script>

</body>
</html>
