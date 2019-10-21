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
	<title>BGM list</title>
	<script src="<?php echo PATH_JQUERY; ?>"></script>
	<style>
		body { background-color:#000; color:#fff; }
		img { background-color:#fff; }
		#ogglist {
			margin-right:210px;
		}
		#thumb {
			position:fixed;
			bottom:0;
			right:0;
			margin:1em;
			width:200px;
		}
		#thumb * {
			margin: 0;
			padding:0;
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
	<p>NOW : <span id="bgmnow"></span></p>
	<p><img src="<?php echo GAME; ?>/thumb.png" width="200" height="300"></p>
	<audio id="bgm" src="" type="audio/ogg" controls autoplay loop>AUDIO</audio>
</div>

<div id="ogglist">
<ol>
<?php
foreach( file(LIST_FILE) as $line )
{
	if ( stripos($line, ".ogg") != false )
		echo "<li><button>". trim($line) ."</button></li>\n";
}
?>
</ol>
</div>

<script>
var audio = document.getElementById("bgm");

	$("button").click( function(){
		var ogg = $(this).html();
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
