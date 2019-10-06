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
echo <<<_HTML
<div id="key_input">
<p><span id="clickmouse">MOUSE</span></p>
</div>
_HTML;
?>

<style>
	.grid { border:none; }
	#key_input {
		position:fixed;
		bottom:0;
		left:50%;
		width:40em;
		margin-left:-20em;
	}
	#key_input p {
		text-align:center;
		cursor:pointer;
	}
</style>

<script>
$("#clickmouse").click(function(){
	grid_sz = 16;
	add_grid();
});
</script>
