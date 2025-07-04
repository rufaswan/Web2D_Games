'use strict';

var QUAD = {};

(function($){

	@@quad-gl.js@@
	@@quad-func.js@@
	@@quad-math.js@@
	@@quad-verify.js@@
	@@quad-export.js@@
	@@binary-reader.js@@

	$.version = 'ver 2025-7-3 (beta)';
	$.gl   = new QuadGL  ($);
	$.func = new QuadFunc($);
	$.math = new QuadMath($);
	$.verify = new QuadVerify($);
	$.export = new QuadExport($);
	$.binary = new BinaryReader();

})(QUAD);

function QuadData(qlist){
	var $ = this;
	$.list = qlist;

	// uploaded files
	$.colorize = [];
	$.keyattr  = [];
	$.hitattr  = [];
	$.name = '';
	$.quad  = {};
	$.image = []; // { pos:rect , name:string }
	$.vram  = QUAD.gl.create_vram(255,255); // white solid texture

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
}
