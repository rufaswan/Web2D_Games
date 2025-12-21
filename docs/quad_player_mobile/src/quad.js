'use strict';

var QUAD = {};

(function($){

	@@quad-gl.js@@
	@@quad-func.js@@
	@@quad-draw.js@@
	@@quad-math.js@@
	@@quad-verify.js@@
	@@quad-export.js@@
	@@binary-reader.js@@

	$.version = 'ver 2025-12-20 (beta)';
	$.gl   = new QuadGL  ($);
	$.func = new QuadFunc($);
	$.draw = new QuadDraw($);
	$.math = new QuadMath($);
	$.verify = new QuadVerify($);
	$.export = new QuadExport($);
	$.binary = new BinaryReader();

	$.QuadData = function (qlist){
		var $  = this;
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
		$.is_hits  = true;
		$.is_lines = true;
		$.is_flipx = false;
		$.is_flipy = false;
		$.is_draw  = false; // nothing drawn = END/skipped
		$.matrix = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
		$.color  = [1,1,1,1];

		$.attach = {
			type : '',
			id   : 0
		};
		$.anim_fps = 0;

		// internal use
		//$.is_wait  = true; // ???
		$.zoom       = 1; // for QUAD.export
		$.line_index = 0; // for QUAD.draw.draw_lines()
	}
})(QUAD);
