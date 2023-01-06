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
'use strict';

// ZOOM related
document.getElementById('zoom_0').addEventListener("click", function(e){
	QUAD.zoom(0);
});
document.getElementById('zoom_a').addEventListener("click", function(e){
	QUAD.zoom(1);
});
document.getElementById('zoom_s').addEventListener("click", function(e){
	QUAD.zoom(-1);
});

// MOVE related
document.getElementById('move_0').addEventListener("click", function(e){
	QUAD.axis_x(0);
	QUAD.axis_y(0);
});
document.getElementById('move_l').addEventListener("click", function(e){
	QUAD.axis_x(-0.1);
});
document.getElementById('move_r').addEventListener("click", function(e){
	QUAD.axis_x(0.1);
});
document.getElementById('move_u').addEventListener("click", function(e){
	QUAD.axis_y(-0.1);
});
document.getElementById('move_d').addEventListener("click", function(e){
	QUAD.axis_y(0.1);
});

// TAG related
var dom_idtag  = document.getElementById('idtag');
document.getElementById('tag_btn').addEventListener("click", function(e){
	dom_idtag.style.display = 'inline';
	var top  = (window.innerHeight / 2) - (dom_idtag.clientHeight / 2);
	var left = (window.innerWidth  / 2) - (dom_idtag.clientWidth  / 2);
	dom_idtag.style.top  = top  + 'px';
	dom_idtag.style.left = left + 'px';
});
dom_idtag.addEventListener("click", function(e){
	dom_idtag.style.display = 'none';
});
