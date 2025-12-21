function QuadDraw(Q){
	var $ = this; // public
	var __ = {};  // private

	//////////////////////////////

	__.color = [
		[1,0,0,1] , [0,1,0,1] , [0,0,1,1] , // rgb
		[0,1,1,1] , [1,0,1,1] , [1,1,0,1] , // cmy
		[0,0,0,1] , [1,1,1,1] ,             // black white
		[0.5,0  ,0  ,1] , [0  ,0.5,0  ,1] , [0  ,0  ,0.5,1] , // 0.5 rgb
		[0  ,0.5,0.5,1] , [0.5,0  ,0.5,1] , [0.5,0.5,0  ,1] , // 0.5 cmy
		[0.5,0.5,0.5,1] , // gray
	];

	__.draw_lines = function( qdata, layer, mat4, quad ){
		var clines = [];

		var debug = [];
		layer.forEach(function(lv,lk){
			if ( ! lv )
				return;

			var dbg_id = debug.indexOf(lv.debug);
			if ( dbg_id < 0 ){
				dbg_id = debug.length;
				debug.push(lv.debug);
				clines[dbg_id] = [];
			}

			var dst = Q.math.quad_multi4(mat4, lv[quad]);
			clines[dbg_id] = clines[dbg_id].concat(dst);
		});

		Q.gl.enable_blend(0);
		clines.forEach(function(cv,ck){
			var clr = __.color[qdata.line_index];
			Q.gl.draw_line(cv, clr);

			qdata.line_index = (qdata.line_index + 1) & 7; // 0-7
			qdata.is_draw = true;
		});
	}

	__.draw_hitbox = function( qdata, hid, mat4 ){
		var layer = qdata.quad.hitbox[hid].layer;
		__.draw_lines(qdata, layer, mat4, 'hitquad');
	}

	//////////////////////////////

	__.draw_keyframe_tex = function( qdata, layer, order, mat4, color ){
		var zrate = 1.0 / (layer.length + 1);
		var buf_list = {};
		var depth = 1.0;
		//console.log('order',order);

		// draw layers by keyframe order
		order.forEach(function(ov){
			var lv = layer[ov];
			if ( ! lv )
				return;
			if ( ! Q.func.is_attr_on(qdata.keyattr, lv.attribute) )
				return;
			depth -= zrate;

			var bid = lv.blend_id | 0;
			if ( ! buf_list[bid] )
				buf_list[bid] = { dst:[] , src:[] , fog:[] , z:[] };
			var ent = buf_list[bid];

			var src = Q.func.vram_srcquad(lv.srcquad, lv.tex_id, qdata.image);
			ent.src = ent.src.concat(src);

			var dst = Q.math.quad_multi4(mat4, lv.dstquad);
			var xyz = Q.math.perspective_quad(dst);
			ent.dst = ent.dst.concat(xyz);

			if ( lv.colorize > 0 ){
				var clr = qdata.colorize[lv.colorize];
				clr = Q.math.vec4_multi(clr, color);
				clr = Q.math.fog_multi4(clr, lv.fogquad);
			}
			else
				var clr = Q.math.fog_multi4(color, lv.fogquad);
			ent.fog = ent.fog.concat(clr);

			ent.z = ent.z.concat([depth , depth , depth , depth]);
		});
		//console.log('buf_list',buf_list);

		Q.gl.enable_depth('LESS');
		for ( var i = -1; i < qdata.quad.blend.length; i++ ){
			if ( ! buf_list[i] ) // no data to draw
				continue;

			if ( i < 0 ) // disable blending
				Q.gl.enable_blend(0);
			else {
				if ( ! qdata.quad.blend[i] ) // invalid or unknown blending
					continue;
				Q.gl.enable_blend( qdata.quad.blend[i] );
			}

			qdata.is_draw = true;
			var bv = buf_list[i];
			Q.gl.draw_keyframe( bv.dst, bv.src, bv.fog, bv.z, qdata.vram );
		} // for ( var i = -1; i < qdata.quad.blend.length; i++ )
		Q.gl.enable_depth(0);
	}

	__.draw_keyframe = function( qdata, kid, mat4, color ){
		var key = qdata.quad.keyframe[kid];
		if ( qdata.is_lines )
			return __.draw_lines(qdata, key.layer, mat4, 'dstquad');
		else
			return __.draw_keyframe_tex(qdata, key.layer, key.order, mat4, color);
	}

	//////////////////////////////

	__.draw_MIX = function( qdata, id, mat4, color ){
		var mix = qdata.quad.__MIX[id];
		if ( mix.key ){
			if ( qdata.is_lines )
				__.draw_lines(qdata, mix.key.layer, mat4, 'dstquad');
			else
				__.draw_keyframe_tex(qdata, mix.key.layer, mix.key.order, mat4, color);
		}
		if ( mix.hit && qdata.is_hits )
			__.draw_lines(qdata, mix.hit.layer, mat4, 'hitquad');
	}

	//////////////////////////////

	__.draw_skeleton = function( qdata, sid, mat4, color ){
		var bone = qdata.quad.skeleton[sid].bone;
		bone.forEach(function(bv,bk){
			__.draw_attach(qdata, bv.attach, mat4, color);
		});
	}

	//////////////////////////////

	__.draw_attach = function( qdata, attach, mat4, color ){
		if ( ! Q.func.is_valid_attach(qdata, attach.type, attach.id) )
			return;
		switch ( attach.type ){
			case 'keyframe':
				return __.draw_keyframe( qdata, attach.id, mat4, color );
			case 'animation':
				var t = Q.func.anim_current( qdata, attach.id, mat4, color );
				if ( ! t.attach )
					return;
				return __.draw_attach( qdata, t.attach, t.mat4, t.color );
			case 'slot':
				qdata.quad.slot[ attach.id ].forEach(function(sv,sk){
					__.draw_attach(qdata, sv, mat4, color);
				});
				return;
			case 'hitbox':
				if ( ! qdata.is_hits )
					return;
				return __.draw_hitbox( qdata, attach.id, mat4 );
			case 'skeleton':
				return __.draw_skeleton( qdata, attach.id, mat4, color );
			case '__MIX':
				return __.draw_MIX( qdata, attach.id, mat4, color );
			case 'list':
				var qid = qdata.quad.list[ attach.id ];
				if ( Q.func.is_undef(qid) || qid < 0 )
					return;
				return $.qdata_draw( qdata.list[qid], mat4, color );
		} // switch ( attach.type )
	}

	$.qdata_draw = function( qdata, mat4, color ){
		if ( ! qdata.quad )
			return;
		var m4 = Q.math.matrix_multi44( mat4, qdata.matrix );
		var c4 = Q.math.vec4_multi(color, qdata.color);
		return __.draw_attach(qdata, qdata.attach, m4, c4);
	}

/*
	$.hook = function( name, code ){
		if ( typeof code !== 'function' )
			return;
		if ( $[name] ){
			$[name] = code;
			return;
		}
		if ( __[name] ){
			__[name] = code;
			return;
		}
	}
*/

	//////////////////////////////

} // function QuadDraw
