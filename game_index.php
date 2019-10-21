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

function getinit( $dir, &$inits )
{
	if ( ! is_dir($dir) )  return;
	$func = __FUNCTION__;

	$rootlen = strlen( ROOT ) + 1;
	foreach ( scandir($dir) as $d )
	{
		if ( $d[0] == '.' )
			continue;
		if ( is_dir("$dir/$d") )
			$func("$dir/$d", $inits);
		if ( $d == "init.cfg" )
		{
			$t1 = substr($dir, $rootlen);
			$inits[] = array("$t1", "$t1/thumb.png");
		}
	}
	return;
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>GAMES INDEX</title>
	<style>
		body { background-color:#000; text-align:center; }
		img { display:block; }
		a { color:#fff; }
		div.games {
			border:1px #fff solid;
			margin:0.1em;
			padding:0.1em;
			display:inline-block;
		}
		div#canvas { display:block; }
	</style>
</head>
<body>
<div id="canvas">

<?php
$inits = array();
getinit( ROOT , $inits );
foreach ( $inits as $d )
{
	list($path,$thumb) = $d;

echo <<<_HTML
<div class="games">
	<a href="game_run.php?game=$path" target="_blank" title="GAME">
	<img src="$thumb" alt="$path" title="$thumb" width="200" height="300">
	</a>
	<p>
		<a href="files-bgm.php?game=$path" target="_blank" title="BGM">BGM</a>
		<a href="files-img.php?game=$path" target="_blank" title="IMG">IMG</a>
	</p>
</div>
_HTML;

}
?>

</canvas> <!-- #canvas -->
</body>
</html>
