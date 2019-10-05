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
unlink(SAVE_FILE . "log");
?><!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<title><?php echo $gp_init["web_title"]; ?></title>
<style>
	* { margin:0; padding:0; }
	@font-face {
		font-family:"JPfont";
		src:url("<?php echo PATH_JPFONT; ?>") format("truetype");
	}
	body {
		font-family:"JPfont";
		background-color:#000;
		color:#fff;
	}
	ul { list-style:none; }
	.sprites { position:absolute; }
	.hidden { display:none; }
	.grid { border:1px #0f0 solid; }
	#select { cursor:pointer; }
	div#canvas { display:block; }
	div#window {
		margin:0 auto 0 auto;
		position:relative;
		color: #fff;
		width: 320px;
		height:240px;
		border:1px #fff solid;
	}
</style>
<script src="<?php echo PATH_JQUERY; ?>"></script>
<script>
var ajax_url  = "ajax.php?game=<?php echo GAME; ?>";
var ajax_done = true;
var ajax_auto = false;
var ajax_ms = 200;
var win_w = 320;
var win_h = 240;
var grid_sz = 16;
</script>
</head><body>
<div id="canvas">
	<div id="window" style="background-image:url('<?php echo GAME; ?>/thumb.png') center center;">
		<span class="sprites" mouse="160,120">160,120</span>
		<ul id="select" class="sprites" mouse="320,0">
			<li data="0">SELECT 1</li>
			<li data="1">SELECT 2</li>
			<li data="2">SELECT 3</li>
			<li data="3">SELECT 4</li>
		</ul>
	</div> <!-- #window -->
	<input id="bgm"  type="hidden" value="">
	<input id="wave" type="hidden" value="">
</div> <!-- #canvas -->

<div id="dataset" style="display:none;">
	<audio id="playbgm"  src="" type="audio/ogg" autoplay loop></audio>
	<audio id="playwave" src="" type="audio/ogg" autoplay></audio>
</div> <!-- #dataset -->

<script>
function add_grid(){
	var x = 0;
	while ( x < win_w ){
		var y = 0;
		while ( y < win_h ){
			var grid = "<div class='sprites grid'";
			grid += " mouse='" +x+ "," +y+ "'";
			grid += " box='" +grid_sz+ "," +grid_sz+ "'";
			grid += " style='left:" +x+ "px;top:" +y+ "px;width:" +grid_sz+ "px;height:" +grid_sz+ "px;'";
			grid += "></div>";
			$("#window").append(grid);
			y += grid_sz;
		}
		x += grid_sz;
	}
	return;
}

function update_audio(){
	["bgm","wave"].forEach(function(v){
		var src  = $("#play"+v).attr("src");
		var wave = $("#"+v).val();
		if ( wave != src )
		{
			var play = document.getElementById("play"+v);
			play.src = wave;
			play.play();
		}
	});
	return;
}

function window_update( input ){
	if ( ! ajax_done )
		return;
	ajax_done = false;

	$.ajax({
		method : 'GET',
		url : ajax_url + input,
		success : function(data,textStatus,jqXHR){
			$("#window").empty().append(data);

			update_audio();

			$(".sprites").each(function(){
				var style = $(this).attr("style");
				if ( ! style )
					style = "";

				var mouse = $(this).attr("mouse");
				if ( mouse )
				{
					var css = mouse.split(',');
					style += "left:" +css[0]+ "px;";
					style += "top:"  +css[1]+ "px;";
				}

				var box = $(this).attr("box");
				if ( box )
				{
					var css = box.split(',');
					style += "width:"  +css[0]+ "px;";
					style += "height:" +css[1]+ "px;";
					if ( css.length == 3 )
						style += "background-color:" +css[2]+ ";";
					if ( css.length == 4 )
						style += "background-position:" +css[2]+ "px " +css[3]+ "px;";
				}

				$(this).attr("style", style);
			});

			win_w = $("#window").width();
			win_h = $("#window").height();
			ajax_auto = $("#win_data").attr("ajax");
			ajax_done = true;
			if ( ajax_auto ){
				setTimeout(function(){
					window_update( "&resume" );
				}, ajax_ms);
			}
		}
	});
	return;
}
</script><?php

require ROOT . "/inc/html_{$gp_init["engine"]}.php";

?><script>
window_update("");

	$("body").on("click", "li", function(){
		var data = $(this).attr("data");
		window_update( "&resume&input=select," + data );
	});

	$("body").on("click", ".sprites", function(){
		var mouse = $(this).attr("mouse");
		window_update( "&resume&input=mouse," + mouse );
	});

</script>

</body>
</html>
<?php
/*
				if ( css[0] >= win_w || css[1] >= win_h )
				{
					$(this).attr("style", "display:none;");
					return;
				}

var arr = MyDiv.getElementsByTagName('script')
for (var n = 0; n < arr.length; n++)
	eval(arr[n].innerHTML) //run script inside div

	if ( typeof auto_ajax === "function" )
		auto_ajax();

<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==" alt="Red dot" />
*/
