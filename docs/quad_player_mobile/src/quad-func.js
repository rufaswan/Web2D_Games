function QuadFunc(Q){
	var $ = this; // public
	var __ = {};  // private

	//////////////////////////////

	__.LOGS = [];
	$.log = function(){
		var arg = [].slice.call(arguments);
		var txt = JSON.stringify(arg);
		__.LOGS.unshift( txt );
		while ( __.LOGS.length > 20 )
			__.LOGS.pop();
		return true;
	}
	$.error = function(){
		var arg = [].slice.call(arguments);
		var txt = JSON.stringify(arg);
		__.LOGS.unshift( 'ERROR : ' + txt );
		while ( __.LOGS.length > 20 )
			__.LOGS.pop();
		return false;
	}
	$.console = function(){
		return __.LOGS.join("\n\n");
	}

	$.is_array = function( array, size ){
		if ( ! Array.isArray(array) )
			return false;
		if ( array.length !== size )
			return false;
		return true;
	}
	$.is_array_unique = function( array ){
		if ( ! Array.isArray(array) )
			return false;
		for ( var i=0; i < array.length; i++){
			var idx = array.indexOf( array[i] );
			if ( idx !== i )
				return false;
		}
		return true;
	}
	$.array_clean_null = function( list ){
		if ( ! Array.isArray(list) )
			return;
		var len = list.length;
		while ( len > 0 ){
			len--;
			if ( list[len] === 0 )
				list.splice(len, 1);
		} // while ( len > 0 )
	}
	$.array_clean_dups = function( list ){
		if ( ! Array.isArray(list) )
			return;
		var len = list.length;
		while ( len > 0 ){
			len--;
			if ( list.indexOf( list[len] ) !== len )
				list.splice(len, 1);
		} // while ( len > 0 )
	}
	$.array_repeat = function( size, value, step=0 ){
		var res = [];
		for ( var i=0; i < size; i++ ){
			res.push(value);
			if ( step )
				value += step;
		}
		return res;
	}

	$.is_undef = function( a ){
		return ( typeof a === 'undefined' );
	}
	$.file_extension = function( fn ){
		var ext = fn.split('.').pop();
		return ext.toLowerCase();
	}
	$.copy_object = function( obj ){
		return JSON.parse( JSON.stringify(obj) );
	}

	$.is_attr_on = function( onlist, attrlist ){
		if ( ! Array.isArray(attrlist) )
			return true;
		for ( var i=0; i < attrlist.length; i++ ){
			var ind = attrlist[i];
			if ( ! onlist[ind] )
				return false;
		}
		return true;
	}

	//////////////////////////////

	$.upload_promise = function( up, id, queue ){
		var ext = $.file_extension(up.name);
		switch ( ext ){
			case 'zip':
				return new Promise(function(ok,err){
					var reader = new FileReader;
					reader.onload = function(){
						var list = Q.binary.zipread( reader.result );
						ok(list);
					}
					reader.readAsArrayBuffer(up);
				}).then(function(list){
					var proall = [];
					Object.keys(list).forEach(function(fn){
						var ext = $.file_extension(fn);
						switch ( ext ){
							case 'quad':
								var p = new Promise(function(ok,err){
									var blob = new Blob(
										[ list[fn] ],
										{ type : 'text/plain' }
									);
									var reader = new FileReader;
									reader.onload = function(){
										ok([fn,reader.result]);
									}
									reader.readAsText(blob);
								}).then(function(res){
									queue.push({ id : id , name : res[0] , data : res[1] });
								});
								proall.push(p);
								break;
							case 'png':
								var p = new Promise(function(ok,err){
									var blob = new Blob(
										[ list[fn] ],
										{ type : 'image/png' }
									);
									var reader = new FileReader;
									reader.onload = function(){
										ok([fn,reader.result]);
									}
									reader.readAsDataURL(blob);
								}).then(function(res){
									queue.push({ id : id , name : res[0] , data : res[1] });
								});
								proall.push(p);
								break;
						} // switch ( ext )
					});
					return Promise.all(proall);
				});

			case 'quad':
				return new Promise(function(ok,err){
					var reader = new FileReader;
					reader.onload = function(){
						ok(reader.result);
					}
					reader.readAsText(up);
				}).then(function(text){
					queue.push({ id : id , name : up.name , data : text });
				});

			case 'png':
				return new Promise(function(ok,err){
					var reader = new FileReader;
					reader.onload = function(){
						ok(reader.result);
					}
					reader.readAsDataURL(up);
				}).then(function(data){
					queue.push({ id : id , name : up.name , data : data });
				});
		} // switch ( ext )
	}

	__.is_rect_collide = function( rect, list ){
		function collide( rect1, rect2 ){
			if ( rect1[0] >= rect2[2] )  return false; // r1.x1 >= r2.x2 , over right
			if ( rect1[1] >= rect2[3] )  return false; // r1.y1 >= r2.y2 , over bottom
			if ( rect1[2] <= rect2[0] )  return false; // r1.x2 <= r2.x1 , over left
			if ( rect1[3] <= rect2[1] )  return false; // r1.y2 <= r2.y1 , over top
			return true;
		}
		for ( var i=0; i < list.length; i++ ){
			if ( ! list[i] )
				continue;
			var col = collide(rect, list[i].pos);
			if ( col )
				return true;
		}
		return false;
	}

	__.vram_posrect = function( vram, texsz, list ){
		for ( var y=0; y < vram.h; y += 0x80 ){
			var y2 = y + texsz[1];
			if ( y2 > vram.h )
				continue;

			for ( var x=0; x < vram.w; x += 0x80 ){
				if ( x === 0 && y === 0 ) // reserve white pixel for fog
					continue;
				var x2 = x + texsz[0];
				if ( x2 > vram.w )
					continue;

				var rect = [x , y , x2 , y2];
				var col = __.is_rect_collide(rect, list);
				if ( ! col )
					return rect;
			} // for ( var x = 0x80; x < vram.w; x += 0x80 )
		} // for ( var y = 0x80; y < vram.h; y += 0x80 )

		// failed to allocate VRAM
		return 0;
	}

	$.queue_promise = function( up, qdata ){
		var ext = $.file_extension(up.name);
		switch ( ext ){
			case 'quad':
				var quad   = JSON.parse(up.data);
				qdata.quad = Q.verify.verify_quadfile(quad);
				qdata.name = Q.verify.safename(up.name);

				qdata.colorize = $.array_repeat(qdata.quad.__ATTR.colorize.length , [1.0 ,1.0 , 1.0 , 1.0]);
				qdata.keyattr  = $.array_repeat(qdata.quad.__ATTR.keyframe.length , true);
				qdata.hitattr  = $.array_repeat(qdata.quad.__ATTR.hitbox.length   , true);
				return $.log('UPLOAD quad', up.name);

			case 'png':
				var name = up.name.match(/\.([0-9]+)\./);
				if ( ! name )
					return 0;

				return new Promise(function(ok,err){
					var tid = name[1];

					var img = new Image;
					img.onload = function(){
						ok([tid,img]);
					}
					img.src = up.data;
				}).then(function(res){
					var tid = res[0];
					var img = res[1];

					var tex = Q.gl.create_texture(0);
					Q.gl.update_texture(tex, img);
					return [tid,tex,img.width,img.height];
				}).then(function(res){
					var tid = res[0];
					var tex = res[1];
					var w = res[2];
					var h = res[3];

					// remove loaded texture
					qdata.image[tid] = 0;
					var pos = __.vram_posrect( qdata.vram , [w,h] , qdata.image );
					if ( ! pos )
						return $.error('cannot fit texture into VRAM', [qdata.vram.w,qdata.vram.h] , [w,h] , qdata.image);

					Q.gl.enable_blend(0);
					Q.gl.draw_vram(qdata.vram, tex, pos);
					qdata.image[tid] = {
						pos  : pos,
						name : up.name,
					};
					return $.log('UPLOAD image', tid, qdata.image[tid]);
				});
		} // switch ( ext )
	}

	//////////////////////////////

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

		var color = [
			[1,0,0,1] , [0,1,0,1] , [0,0,1,1] , // rgb
			[0,1,1,1] , [1,0,1,1] , [1,1,0,1] , // cmy
			[0,0,0,1] , [1,1,1,1] ,             // black white
			[0.5,0  ,0  ,1] , [0  ,0.5,0  ,1] , [0  ,0  ,0.5,1] , // 0.5 rgb
			[0  ,0.5,0.5,1] , [0.5,0  ,0.5,1] , [0.5,0.5,0  ,1] , // 0.5 cmy
			[0.5,0.5,0.5,1] , // gray
		];

		Q.gl.enable_blend(0);
		clines.forEach(function(cv,ck){
			var cid = qdata.line_index % color.length;
			Q.gl.draw_line(cv, color[cid]);

			qdata.line_index++;
			qdata.is_draw = true;
		});
	}

	__.draw_hitbox = function( qdata, hid, mat4 ){
		var layer = qdata.quad.hitbox[hid].layer;
		__.draw_lines(qdata, layer, mat4, 'hitquad');
	}

	//////////////////////////////

	__.draw_keyframe_tex = function( qdata, layer, order, mat4, color ){
		var dummysrc = [0,0 , 0,0 , 0,0 , 0,0];

		var zrate = 1.0 / (layer.length + 1);
		var buf_list = {};
		var depth = 1.0;
		//console.log('order',order);

		// draw layers by keyframe order
		order.forEach(function(ov){
			var lv = layer[ov];
			if ( ! lv )
				return;
			if ( ! $.is_attr_on(qdata.keyattr, lv.attribute) )
				return;
			depth -= zrate;

			var bid = lv.blend_id | 0;
			if ( ! buf_list[bid] )
				buf_list[bid] = { dst:[] , src:[] , fog:[] , z:[] };
			var ent = buf_list[bid];

			if ( lv.tex_id < 0 || ! qdata.image[lv.tex_id] || ! lv.srcquad )
				var src = dummysrc;
			else
				var src = Q.math.vram_srcquad(lv.srcquad, qdata.image[lv.tex_id].pos);
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

	__.attach_keyhit_id = function( qdata, attach ){
		var def = {
			key : -1,
			hit   : -1,
		};
		if ( ! attach )
			return def;

		switch ( attach.type ){
			case 'keyframe':
				def.key = attach.id;
				break;
			case 'hitbox':
				def.hit = attach.id;
				break;
			case 'slot':
				var slot = qdata.quad.slot[ attach.id ];
				if ( ! slot )
					break;
				slot.forEach(function(sv,sk){
					var t = __.attach_keyhit_id(qdata, sv);
					if ( t.key >= 0 )  def.key = t.key;
					if ( t.hit >= 0 )  def.hit = t.hit;
				});
				break;
		} // switch ( attach.type )
		return def;
	}

	__.mix_attach = function( qdata, cur, nxt, rate, keymix, hitmix ){
		var cur_id = __.attach_keyhit_id(qdata, cur);
		var nxt_id = __.attach_keyhit_id(qdata, nxt);

		var mixcur = { key : 0 , hit : 0 };
		var mixnxt = { key : 0 , hit : 0 };

		// duplicate current for updated values
		if ( cur_id.key >= 0 )
			mixcur.key = $.copy_object( qdata.quad.keyframe[cur_id.key] );
		if ( cur_id.hit >= 0 )
			mixcur.hit = $.copy_object( qdata.quad.hitbox  [cur_id.hit] );

		// next is read-only, so reference is fine
		if ( nxt_id.key >= 0 )
			mixnxt.key = qdata.quad.keyframe[nxt_id.key];
		if ( nxt_id.hit >= 0 )
			mixnxt.hit = qdata.quad.hitbox  [nxt_id.hit];

		if ( keymix && mixcur.key && mixnxt.key ){
			if ( mixcur.key.layer.length === mixnxt.key.layer.length ){
				for ( var i=0; i < mixcur.key.layer.length; i++ ){
					if ( ! mixcur.key.layer[i] )
						continue;
					if ( ! mixnxt.key.layer[i] )
						continue;
					mixcur.key.layer[i].dstquad = Q.math.quad_mix( rate , mixcur.key.layer[i].dstquad , mixnxt.key.layer[i].dstquad );
					mixcur.key.layer[i].fogquad = Q.math.fog_mix ( rate , mixcur.key.layer[i].fogquad , mixnxt.key.layer[i].fogquad );
				} // for ( var i=0; i < mixcur.key.layer.length; i++ )
			}
		}

		if ( hitmix && mixcur.hit && mixnxt.hit ){
			if ( mixcur.hit.layer.length === mixnxt.hit.layer.length ){
				for ( var i=0; i < mixcur.hit.layer.length; i++ ){
					if ( ! mixcur.hit.layer[i] )
						continue;
					if ( ! mixnxt.hit.layer[i] )
						continue;
					mixcur.hit.layer[i].hitquad = Q.math.quad_mix( rate , mixcur.hit.layer[i].hitquad , mixnxt.hit.layer[i].hitquad );
				} // for ( var i=0; i < mixcur.hit.layer.length; i++ )
			}
		}

		// return attach object
		var id = qdata.quad.__MIX.length;
		qdata.quad.__MIX.push(mixcur);
		return {
			'type' : '__MIX',
			'id'   : id,
		};
	}

	__.anim_time_index = function( fps, anim ){
		var len = anim.timeline.length;
		var cur = 0;
		while (1){
			fps -= anim.timeline[cur].time;
			if ( fps < 0 )
				return [cur,-fps];

			cur++;
			if ( cur >= len ){
				if ( anim.loop_id < 0 )
					return [-1,0];
				cur = anim.loop_id;
			}
		} // while (1)
	}

	__.anim_current = function( qdata, aid, mat4, color ){
		var ret = {
			attach : 0,
			mat4   : mat4,
			color  : color,
		}

		// check for valid range
		if ( qdata.anim_fps < 0 )
			return ret;
		var anim = qdata.quad.animation[aid];

		// get current frame
		var t = __.anim_time_index(qdata.anim_fps, anim);
		var curid = t[0];
		if ( curid < 0 )
			return ret;
		var cur = anim.timeline[curid];

		// get next frame
		var nxtid = curid + 1;
		if ( nxtid >= anim.timeline.length ){
			if ( anim.loop_id < 0 )
				nxtid = curid;
			else
				nxtid = anim.loop_id;
		}
		var nxt = anim.timeline[nxtid];

		// nothing to mix
		ret.attach = cur.attach;
		if ( curid === nxtid ){
			ret.mat4  = Q.math.matrix_multi44( ret.mat4 , cur.matrix );
			ret.color = Q.math.vec4_multi    ( ret.color, cur.color  );
			return ret;
		}

		// mixing tests
		var m4, c4;
		var rate = t[1] / cur.time;

		// mix matrix
		if ( cur.matrix_mix )
			m4 = Q.math.matrix_mix( rate, cur.matrix, nxt.matrix );
		else
			m4 = cur.matrix;
		ret.mat4 = Q.math.matrix_multi44( ret.mat4 , m4 );

		// mix color
		if ( cur.color_mix )
			c4 = Q.math.color_mix( rate, cur.color , nxt.color  );
		else
			c4 = cur.color;
		ret.color = Q.math.vec4_multi( ret.color, c4 );

		// layer mixing test
		if ( ! cur.keyframe_mix && ! cur.hitbox_mix )
			return ret;

		ret.attach = __.mix_attach(qdata, cur.attach, nxt.attach, rate, cur.keyframe_mix, cur.hitbox_mix);
		return ret;
	}

	//////////////////////////////

	__.draw_skeleton = function( qdata, sid, mat4, color ){
		var bone = qdata.quad.skeleton[sid].bone;
		bone.forEach(function(bv,bk){
			__.draw_attach(qdata, bv.attach, mat4, color);
		});
	}

	//////////////////////////////

	$.is_valid_attach = function( qdata, type, id ){
		if ( ! Array.isArray( qdata.quad[ type ] ) )
			return false;
		if ( ! qdata.quad[ type ][ id ] )
			return false;
		return true;
	}

	__.draw_attach = function( qdata, attach, mat4, color ){
		if ( ! $.is_valid_attach(qdata, attach.type, attach.id) )
			return;
		switch ( attach.type ){
			case 'keyframe':
				return __.draw_keyframe( qdata, attach.id, mat4, color );
			case 'animation':
				var t = __.anim_current( qdata, attach.id, mat4, color );
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
				if ( $.is_undef(qid) || qid < 0 )
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

	$.qdata_clear = function( qdata ){
		if ( ! qdata.quad )
			return;
		Q.gl.clear();
		qdata.is_draw    = false;
		qdata.line_index = 0;
		qdata.quad.__MIX = [];
	}

	$.viewer_autozoom = function( qdata ){
		var canvsz  = Q.gl.canvas_size();
		var sprsize = Q.export.rect_attach(qdata, qdata.attach.type, qdata.attach.id);
		if ( ! sprsize )
			return 1.0;

		var symm = Q.math.rect_symmetry(sprsize);
		var zoomx = (canvsz[0] * 1.0) / symm[0];
		var zoomy = (canvsz[1] * 1.5) / symm[1];
		return ( zoomx < zoomy ) ? zoomx : zoomy;
	}

	$.viewer_camera = function( qdata, autozoom ){
		var canvsz = Q.gl.canvas_size();

		qdata.zoom = 1.0;
		var movex = canvsz[0] * 0  ; // no change
		var movey = canvsz[1] * 0.5; // half downward

		if ( autozoom > 0.0 )
			qdata.zoom = autozoom;
		else
			qdata.zoom = $.viewer_autozoom(qdata);

		var m4 = Q.math.matrix4();
		m4[0+3] = movex;
		m4[4+3] = movey;

		m4[0+0] = qdata.zoom;
		m4[4+1] = qdata.zoom;
		if ( qdata.is_flipx )  m4[0+0] = -m4[0+0];
		if ( qdata.is_flipy )  m4[4+1] = -m4[4+1];
		return m4;
	}

	//////////////////////////////

} // function QuadFunc(Q)
