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
header("Content-Type:text/html; charset=UTF-8;");
if ( ! defined("GAME") )
	exit("NO GAME\n");

init_listfile( true );
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>IMG list</title>
	<style>
		body { background-color:#000; color:#fff; }
		div.thumb {
			width: 150px;
			height:100px;
			float:left;
			display:block;
			text-align:center;
		}
		div.thumb img {
			max-width: 100%;
			max-height:100%;
		}
	</style>
</head>
<body>

<?php
foreach( file(LIST_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $img )
{
	if ( stripos($img, ".png") == false )
		continue;

echo <<<_HTML
<div class="thumb">
	<a href="$img">
		<img src="$img" alt="$img" title="$img">
	</a>
</div>

_HTML;

} // foreach( file(LIST_FILE) as $img )
?>

<br style="clear:both;"/>
<p style="text-decoration:underline;"><a href="..">&gt;&gt; MAIN</a></p>
</body>
</html>

