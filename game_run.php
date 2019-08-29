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
<meta charset="<?php echo $gp_init["charset"]; ?>">
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
	.pixel {
		width: 1px;
		height:1px;
	}
	.tiled {
		width: 8px;
		height:8px;
		cursor:pointer;
	}
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
var win_w = 320;
var win_h = 240;
</script>
</head><body>
<div id="canvas">
	<div id="window" style="background:url('<?php echo GAME; ?>/thumb.png') center center;">
		<span class="sprites" style="left:320px; top:240px;">320,240</span>
		<ul id="select" class="sprites" style="left:480px; top:120px;">
			<li data="0">SELECT 1</li>
			<li data="1">SELECT 2</li>
			<li data="2">SELECT 3</li>
			<li data="3">SELECT 4</li>
		</ul>
	</div> <!-- #window -->
	<input id="ogg"  type="hidden" value="">
	<input id="midi" type="hidden" value="">
	<input id="wave" type="hidden" value="">
</div> <!-- #canvas -->

<div id="dataset" style="display:none;">
	<audio id="playogg"  src="" type="audio/ogg" autoplay loop></audio>
	<audio id="playmidi" src="" type="audio/ogg" autoplay loop></audio>
	<audio id="playwave" src="" type="audio/ogg" autoplay></audio>
</div> <!-- #dataset -->

<script>
function update_audio(){
	["ogg","midi","wave"].forEach(function(v){
		var src  = $("#play"+v).attr("src");
		var wave = $("#"+v).val();
		if ( wave != src )
		{
			var play = document.getElementById("play"+v);
			play.src = wave;
			play.play();
		}
	});
}

function window_update( input ){
	if ( ! ajax_done )
		return;
	ajax_done = false;

	$.get( ajax_url + input, function(data){
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

		var win_css = $("#window_css").val();
			$("#window").attr("style", win_css );
		win_w = $("#window").width();
		win_h = $("#window").height();
		ajax_done = true;
	});
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
		var data = $(this).attr("mouse");
		window_update( "&resume&input=mouse," + data );
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


		ajax_arg = ajax_arg + "&input=select," + data;
		ajax_arg = ajax_arg + "&input=mouse," + data;
	ajax_ok = true;
	ajax_arg = "&resume";
var fps = 1000/14;
setInterval( function(){
	if ( ajax_ok ){
		ajax_ok = false;
		window_update( ajax_arg );
	}
}, fps );
function loop(){
	setTimeout(function(){
		window_update( ajax_arg );
		ajax_arg = "&resume";
	}
	, fps);
	loop();
}
loop();
		console.log([src,wave]);
		$("#console").html(data);
var fps = 1000/14;
var interval = setInterval( function(){}, fps );
clearInterval(interval);
var ajax_arg = "";
var ajax_ok  = true;

function RecurringTimer(callback, delay){
	var timerId, start, remaining = delay;

	this.pause = function(){
		window.clearTimeout(timerId);
		remaining -= new Date() - start;
	};

	var resume = function(){
		start = new Date();
		timerId = window.setTimeout(function(){
			remaining = delay;
			resume();
			callback();
		}, remaining);
	};

	this.resume = resume;
	this.resume();
}

var arr = MyDiv.getElementsByTagName('script')
for (var n = 0; n < arr.length; n++)
	eval(arr[n].innerHTML) //run script inside div

$.ajax({
	type: 'GET',
	url: 'response.php',
	timeout: 2000,
	success: function(data) {
	  $("#content").html(data);
	  myFunction();
	},
	error: function (XMLHttpRequest, textStatus, errorThrown) {
	  alert("error retrieving content");
	}

	if ( typeof auto_ajax === "function" )
		auto_ajax();

<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==" alt="Red dot" />
*/
