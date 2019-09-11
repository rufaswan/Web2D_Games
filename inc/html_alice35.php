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
<table><tr>

<td>
	<table>
	<tr>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['up']}">UP</button></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button data="{$gp_key['left']}">LT</button></td>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['right']}">RT</button></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['down']}">DN</button></td>
		<td>&nbsp;</td>
	</tr>
	</table>
</td>

<td>
	<button data="0">SKIP</button>
</td>

<td>
	<table>
	<tr>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['esc']}">C</button></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button data="{$gp_key['tab']}">D</button></td>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['enter']}">A</button></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><button data="{$gp_key['space']}">B</button></td>
		<td>&nbsp;</td>
	</tr>
	</table>
</td>

</tr></table>
<p>JOYPAD</p>
</div>
_HTML;
/*
		ajax_arg = ajax_arg + "&input=key," + data;
		padding:1em;
		if ( data == 0 )
			window_update( "&resume" );
		else
*/
?>

<style>
	#key_input {
		position:fixed;
		bottom:0;
		left:50%;
		width:40em;
		margin-left:-20em;
	}
	#key_input button {
		font-size:1em;
		padding:0.5em;
		cursor:pointer;
	}
	#key_input table {
		margin:0 auto 0 auto;
		text-align:center;
		display:none;
	}
	#key_input p {
		text-align:center;
		cursor:pointer;
	}
</style>

<script>
	$("#key_input").on("click", "button", function(){
		var data = $(this).attr("data");
		window_update( "&resume&input=key,"+data );
	});
	$("#key_input").on("click", "p", function(){
		$("#key_input table").toggle();
	});
</script>
