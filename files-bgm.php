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

init_listfile( true );
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
			margin-right:220px;
		}
		#thumb {
			position:fixed;
			right:0;
			margin:1em;
			width:200px;
		}
		#thumb audio {
			width:200px;
		}
		#thumb * {
			margin: 0 auto;
			padding:0 auto;
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
	<p><img src="<?php echo GAME; ?>/thumb.png" width="200" height="300"></p>
	<audio id="bgm" src="<?php echo PATH_OGG_1S; ?>" type="audio/ogg" controls autoplay loop>AUDIO</audio>
	<p>&nbsp;</p>
	<p>NOW : <span id="bgmnow"></span></p>
</div>

<div id="ogglist">
<ol>
<?php
foreach( file(LIST_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $bgm )
{
	if ( stripos($bgm, ".ogg") != false )
		echo "<li><button>". $bgm ."</button></li>\n";
}
?>
</ol>
</div>

<script>
var jq = jQuery.noConflict();
var audio = document.getElementById("bgm");

	jq("body").on("click", "button", function(){
		var ogg = jq(this).html();
		var now = jq("#bgm").attr("src");
		if ( ogg != now )
		{
			jq("#bgmnow").empty().append(ogg);
			audio.src = ogg;
			audio.play();
		}
	});
</script>

<p style="text-decoration:underline;"><a href="..">&gt;&gt; MAIN</a></p>
</body>
</html>
<?php
/*
*/
