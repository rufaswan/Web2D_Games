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

unlink( LIST_FILE );
init_filelist();
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>IMG list</title>
	<script src="<?php echo PATH_JQUERY; ?>"></script>
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
foreach( file(LIST_FILE) as $line )
{
	if ( stripos($line, ".png") == false )
		continue;
	$img = trim($line);

echo <<<_HTML
<div class="thumb">
	<a href="$img">
		<img src="$img" alt="$img" title="$img">
	</a>
</div>

_HTML;

}
?>

<p><a href="/">MAIN</a></p>
</body>
</html>

