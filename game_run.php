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

file_put_contents( SAVE_FILE . "log", "" );
init_listfile( true );
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
		width: <?php echo $gp_init["width"]; ?>px;
		height:<?php echo $gp_init["height"]; ?>px;
		border:1px #fff solid;
	}
</style>
<script src="<?php echo PATH_JQUERY; ?>"></script>
<script>
var jq = jQuery.noConflict();
var ajax_url  = "ajax.php?game=<?php echo GAME; ?>";
var ajax_timer = [];
var win_w = <?php echo $gp_init["width"]; ?>;
var win_h = <?php echo $gp_init["height"]; ?>;
var grid_sz = 256;
var dummy_ogg = "<?php echo PATH_OGG_1S; ?>";
</script>
</head><body>
<div id="canvas">
	<div id="window" style="background-image:url('<?php echo GAME; ?>/thumb.png') center center;">
		<span class="sprites" data-mouse="160,120,0,0">160,120</span>
		<ul id="select" class="sprites" data-mouse="320,0,0,0">
			<li data-select="0">SELECT 1</li>
			<li data-select="1">SELECT 2</li>
			<li data-select="2">SELECT 3</li>
			<li data-select="3">SELECT 4</li>
		</ul>
		<script>
			function ajax_auto(){ return true; }
		</script>
	</div> <!-- #window -->
	<input id="filebgm" type="hidden" value="">
	<input id="filewav" type="hidden" value="">
</div> <!-- #canvas -->

<div id="dataset" style="display:none;">
	<audio id="playbgm" src="" type="audio/ogg" autoplay loop></audio>
	<audio id="playwav" src="" type="audio/ogg" autoplay></audio>
</div> <!-- #dataset -->

<script>
function clear_ajax(){
	while ( ajax_timer.length > 0 )
	{
		clearTimeout( ajax_timer[0] );
		ajax_timer.shift();
	}
	return;
}

function update_audio(){
	["bgm","wav"].forEach(function(v){
		var src  = jq("#play"+v).attr("src");
		if ( ! src )
		{
			jq("#play"+v).attr("src", dummy_ogg);
			src = dummy_ogg;
		}

		var wave = jq("#file"+v).val();
		if ( ! wave )
		{
			jq("#file"+v).val(dummy_ogg);
			wave = dummy_ogg;
		}

		if ( wave != src )
		{
			var play = document.getElementById("play"+v);
			play.src = wave;
			play.play();
		}
	});
	return;
}

function update_sprites(){
	jq(".sprites").each(function(){
		var style = jq(this).attr("style");
		if ( ! style )
			style = "";

		var mouse = jq(this).attr("data-mouse");
		var css = mouse.split(',');
		style += "left:" +css[0]+ "px;";
		style += "top:"  +css[1]+ "px;";
		if ( css[2] > 0 )
			style += "width:"  +css[2]+ "px;";
		if ( css[3] > 0 )
			style += "height:" +css[3]+ "px;";

		var sxy = jq(this).attr("data-sxy");
		if ( sxy )
		{
			var xy = sxy.split(',');
			style += "background-position:" +xy[0]+ "px " +xy[1]+ "px;";
		}

		jq(this).attr("style", style);
	}); // jq(".sprites").each()
	return;
}

function window_update( input ){
	jq.ajax({
		method : 'GET',
		url : ajax_url + input,
	}).done(function(data){
		// ajax().done()
		jq("#window").empty().append(data);

		update_audio();
		update_sprites();

		ajax_auto();
		// ajax().done()
	});
	return;
}
</script><?php

require ROOT . "/inc/" . $gp_init['engine'] . "/html.php";

?><script>
window_update("");

	jq("#window").on("click", "li", function(){
		clear_ajax();
		var data = jq(this).attr("data-select");
		window_update( "&resume&input=select," + data );
	});

	jq("#window").on("click", "div.sprites , img.sprites", function(){
		clear_ajax();
		var mouse = jq(this).attr("data-mouse");
		var mpos = mouse.split(',');
		var x = mpos[0] + Math.round(mpos[2] / 2);
		var y = mpos[1] + Math.round(mpos[3] / 2);
		window_update( "&resume&input=mouse," +x+ "," +y );
	});

</script>

</body>
</html>
<?php
/*
function add_grid(){
	var x = 0;
	while ( x < win_w ){
		var y = 0;
		while ( y < win_h ){
			var grid = "<div class='sprites grid'";
			grid += " data-mouse='" +x+ "," +y+ "," +grid_sz+ "," +grid_sz+ "'";
			grid += " style='left:" +x+ "px;top:" +y+ "px;width:" +grid_sz+ "px;height:" +grid_sz+ "px;'";
			grid += "></div>";
			jq("#window").append(grid);
			y += grid_sz;
		}
		x += grid_sz;
	}
	return;
}
				ajax_timer = setTimeout(function(){
					window_update( "&resume" );
				}, ajax_ms);
				if ( css[0] >= win_w || css[1] >= win_h )
				{
					jq(this).attr("style", "display:none;");
					return;
				}

var arr = MyDiv.getElementsByTagName('script')
for (var n = 0; n < arr.length; n++)
	eval(arr[n].innerHTML) //run script inside div

	if ( typeof auto_ajax === "function" )
		auto_ajax();

data:text/html, <html contenteditable>
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==" alt="Red dot" />

var d = $.Deferred();
d.then(function(){
  var d1 = $.Deferred();
   console.log("in done 1");
   window.setTimeout(function(){ d1.resolve(); },1000)
   return d1.promise(); }
  ).
  then(function(){
    var d1 = $.Deferred();
    console.log("in done 1");
    window.setTimeout(function(){ d1.resolve(); },1000)
    return d1.promise(); }
  ).
*/
