'use strict';

var QUAD = {};

(function($){

	@@quad-gl.js@@
	@@quad-func.js@@
	@@quad-math.js@@
	@@quad-export.js@@
	@@binary-reader.js@@

	$.version = 'ver 2024-1-11 (beta)';
	$.gl   = new QuadGL  ($);
	$.func = new QuadFunc($);
	$.math = new QuadMath($);
	$.export = new QuadExport($);
	$.binary = new BinaryReader();

})(QUAD);

function QuadData(qlist){
	var $ = this;
	$.LIST = qlist;

	// uploaded files
	$.name = '';
	$.QUAD  = {};
	$.IMAGE = []; // { pos:rect , name:string }
	$.VRAM  = QUAD.gl.createPixel(255,-1,-1);

	// activated data
	$.is_wait  = true;
	$.is_draw  = false;
	$.is_hits  = true;
	$.is_lines = true;
	$.is_flipx = false;
	$.is_flipy = false;
	$.zoom   = 1;
	$.matrix = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
	$.color  = [1,1,1,1];

	$.attach = {
		type : '',
		id   : 0
	};
	$.anim_fps = 0;
	$.line_index = 0;
	$.prev = ['',-1,-1,true,true,false,false,1];
}
