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

	$.isArray = function( array, size ){
		if ( ! Array.isArray(array) )
			return false;
		if ( array.length !== size )
			return false;
		return true;
	}
	$.isArrayUnique = function( array ){
		if ( ! Array.isArray(array) )
			return false;
		for ( var i=0; i < array.length; i++){
			var idx = array.indexOf( array[i] );
			if ( idx !== i )
				return false;
		}
		return true;
	}
	$.arrayCleanNull = function( list ){
		if ( ! Array.isArray(list) )
			return;
		var len = list.length;
		while ( len > 0 ){
			len--;
			if ( list[len] === 0 )
				list.splice(len, 1);
		} // while ( len > 0 )
	}
	$.arrayCleanDups = function( list ){
		if ( ! Array.isArray(list) )
			return;
		var len = list.length;
		while ( len > 0 ){
			len--;
			if ( list.indexOf( list[len] ) !== len )
				list.splice(len, 1);
		} // while ( len > 0 )
	}
	$.arrayNumber = function( size ){
		var array = [];
		for ( var i=0; i < size; i++ )
			array.push(i);
		return array;
	}

	$.isUndef = function( a ){
		return ( typeof a === 'undefined' );
	}
	$.fileExtension = function( fn ){
		var ext = fn.split('.').pop();
		return ext.toLowerCase();
	}
	$.copyObject = function( obj ){
		return JSON.parse( JSON.stringify(obj) );
	}

	//////////////////////////////

	$.uploadPromise = function( up, qdata ){
		var ext = $.fileExtension(up.name);
		switch ( ext ){
			case 'zip':
				return new Promise(function(resolve, reject){
					var reader = new FileReader;
					reader.onload = function(){
						var list = Q.binary.zipread( reader.result );
						resolve(list);
					}
					reader.readAsArrayBuffer(up);
				}).then(function(list){
					var key = Object.keys(list);
					var pro = [];
					for ( var i=0; i < key.length; i++ ){
						var ext = $.fileExtension( key[i] );
						var dat = list[ key[i] ];

						switch ( ext ){
							case 'quad':
								var text = Q.binary.uint2txt(dat);
								pro[i] = __.uploadHandler(qdata, 'quad', key[i], text);
								break;
							case 'png':
								var data = 'data:image/png;base64,' + Q.binary.toBase64(dat);
								pro[i] = __.uploadHandler(qdata, 'image', key[i], data);
								break;
						} // switch ( ext )
					} // for ( var i=0; i < key.length; i++ )
					return Promise.all(pro);
				});

			case 'quad':
				return new Promise(function(resolve, reject){
					var reader = new FileReader;
					reader.onload = function(){
						resolve(reader.result);
					}
					reader.readAsText(up);
				}).then(function(text){
					return __.uploadHandler(qdata, 'quad', up.name, text);
				});

			case 'png':
				return new Promise(function(resolve, reject){
					var reader = new FileReader;
					reader.onload = function(){
						resolve(reader.result);
					}
					reader.readAsDataURL(up);
				}).then(function(data){
					return __.uploadHandler(qdata, 'image', up.name, data);
				});
		} // switch ( ext )
		return 0;
	}

	__.isRectCollide = function( rect, list ){
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

	__.VRAMposrect = function( xy, vram, texsz, list ){
		var x2 = xy[0] + texsz[0];
		var y2 = xy[1] + texsz[1];
		if ( x2 > vram.w )  return 0;
		if ( y2 > vram.h )  return 0;

		var rect = [xy[0] , xy[1] , x2 , y2];
		var col = __.isRectCollide(rect, list);
		if ( ! col )
			return rect;

		// morton VRAM allocation
		var halfx = xy[0] + ((vram.w - xy[0]) >> 1);
		var halfy = xy[1] + ((vram.h - xy[1]) >> 1);

		// in 100x100 slots
		if ( halfx & 0xff )  return 0;
		if ( halfy & 0xff )  return 0;

		col = __.VRAMposrect( [halfx,xy[1]], vram, texsz, list);
		if ( col )
			return col;
		col = __.VRAMposrect( [xy[0],halfy], vram, texsz, list);
		if ( col )
			return col;
		col = __.VRAMposrect( [halfx,halfy], vram, texsz, list);
		if ( col )
			return col;

		// failed to allocate VRAM
		return 0;
	}

	__.uploadHandler = function( qdata, type, fname, data ){
		switch( type ){
			case 'quad':
				var quad   = JSON.parse(data);
				qdata.QUAD = __.quadfileCheck(quad);
				qdata.name = fname.replace(/[^A-Za-z0-9]/g, '_');
				return $.log('UPLOAD quad', fname);

			case 'image':
				return new Promise(function(resolve, reject){
					var dummytex = Q.gl.createPixel(255);
					var fnm = fname.match(/\.([0-9]+)\./);
					var tid = fnm[1];

					var img = new Image;
					img.onload = function(){
						resolve([tid,img]);
					}
					img.src = data;
				}).then(function(res){
					var tid = res[0];
					var img = res[1];

					var tex = Q.gl.createTexture();
					Q.gl.updateTexture(tex, img);
					return [tid,tex,img.width,img.height];
				}).then(function(res){
					var tid = res[0];
					var tex = res[1];
					var w = res[2];
					var h = res[3];

					// remove loaded texture
					qdata.IMAGE[tid] = 0;
					var pos = __.VRAMposrect( [0,0], qdata.VRAM , [w,h] , qdata.IMAGE );
					if ( ! pos )
						return $.error('cannot fit texture into VRAM', [qdata.VRAM.w,qdata.VRAM.h] , [w,h] , qdata.IMAGE);

					Q.gl.drawVRAM(qdata.VRAM, tex, pos);
					qdata.IMAGE[tid] = {
						pos  : pos,
						name : fname,
					};
					return $.log('UPLOAD image', tid, qdata.IMAGE[tid]);
				});
		} // switch( type )
		return 0;
	}

	__.keyFogQuad = function( fog ){
		if ( typeof fog === 'string' ){
			var c = Q.math.css_color(fog);
			return [].concat(c, c, c, c);
		}
		if ( $.isArray(fog, 4) ){
			var c0 = Q.math.css_color( fog[0] );
			var c1 = Q.math.css_color( fog[1] );
			var c2 = Q.math.css_color( fog[2] );
			var c3 = Q.math.css_color( fog[3] );
			return [].concat(c0, c1, c2, c3);
		}
		// default solid white
		return [1,1,1,1 , 1,1,1,1 , 1,1,1,1 , 1,1,1,1];
	}

	__.quadfileCheck = function( quad ){
		quad.blend     = quad.blend     || [];
		quad.slot      = quad.slot      || [];
		quad.hitbox    = quad.hitbox    || [];
		quad.keyframe  = quad.keyframe  || [];
		quad.animation = quad.animation || [];
		quad.skeleton  = quad.skeleton  || [];
		quad.__MIX     = [];

		var ent;
		function nullent(arr,k){
			arr[k] = 0;
		}

		quad.blend.forEach(function(bv,bk){
			var valid = Q.gl.isValidConstant.apply(null, bv.mode);
			if ( ! valid )
				return nullent(quad.blend, bk);
			if ( bv.mode.length !== 6 && bv.mode.length !== 3 )
				return nullent(quad.blend, bk);
			quad.blend[bk] = {
				'mode'  : bv.mode,
				'name'  : bv.name || 'blend ' + bk,
				'color' : Q.math.css_color( bv.color ),
			};
		}); // quad.blend.forEach

		quad.hitbox.forEach(function(hv,hk){
			if ( ! Array.isArray(hv.layer) )
				return nullent(quad.hitbox, hk);
			var is_null = true;
			hv.layer.forEach(function(lv,lk){
				if ( ! $.isArray(lv.hitquad, 8) )
					return nullent(hv.layer, lk);
				is_null = false;
				hv.layer[lk] = {
					'debug'   : lv.debug || 0,
					'hitquad' : lv.hitquad,
				};
			}); // hv.layer.forEach
			if ( is_null )
				return nullent(quad.hitbox, hk);
			ent = {
				'debug' : hv.debug || 0,
				'name'  : hv.name  || 'hitbox '+ hk,
				'layer' : hv.layer,
			};
			quad.hitbox[hk] = ent;
		}); // quad.hitbox.forEach

		quad.keyframe.forEach(function(kv,kk){
			if ( ! Array.isArray(kv.layer) )
				return nullent(quad.keyframe, kk);
			var is_null = true;
			kv.layer.forEach(function(lv,lk){
				if ( ! $.isArray(lv.dstquad, 8) )
					return nullent(kv.layer, lk);
				is_null = false;
				ent = {
					'debug'    : lv.debug || 0,
					'dstquad'  : lv.dstquad,
					'tex_id'   : -1,
					'blend_id' : -1,
					'fogquad'  : __.keyFogQuad( lv.fogquad ),
					'srcquad'  : 0,
				};
				if ( ! $.isUndef(lv.blend_id) )  ent.blend_id = lv.blend_id | 0; // 0 is valid
				if ( ! $.isUndef(lv.tex_id  ) )  ent.tex_id   = lv.tex_id   | 0; // 0 is valid
				if ( $.isArray(lv.srcquad, 8) )
					ent.srcquad = lv.srcquad;
				kv.layer[lk] = ent;
			}); // kv.layer.forEach
			if ( is_null )
				return nullent(quad.keyframe, kk);
			ent = {
				'debug'  : kv.debug || 0,
				'name'   : kv.name  || 'keyframe '+ kk,
				'layer'  : kv.layer,
				'order'  : kv.order,
				'__RECT' : 0,
			};
			if ( ! $.isArrayUnique(ent.order) )
				ent.order = $.arrayNumber( kv.layer.length );
			quad.keyframe[kk] = ent;
		}); // quad.keyframe.forEach

		quad.animation.forEach(function(av,ak){
			if ( ! Array.isArray(av.timeline) )
				return nullent(quad.animation, ak);
			av.timeline.forEach(function(tv,tk){
				if ( tv.time < 1 )
					return nullent(av.timeline, tk);
				ent = {
					'debug'        : tv.debug  || 0,
					'time'         : tv.time,
					'attach'       : tv.attach || 0 ,
					'matrix'       : 0,
					'color'        : Q.math.css_color(tv.color),
					'matrix_mix'   : ( tv.matrix_mix   ) ? true : false,
					'color_mix'    : ( tv.color_mix    ) ? true : false,
					'keyframe_mix' : ( tv.keyframe_mix ) ? true : false,
					'hitbox_mix'   : ( tv.hitbox_mix   ) ? true : false,
				};
				if ( $.isArray(tv.matrix, 16) )
					ent.matrix = tv.matrix;
				av.timeline[tk] = ent;
			}); // av.timeline.forEach
			$.arrayCleanNull(av.timeline);
			if ( av.timeline.length < 1 )
				return nullent(quad.animation, ak);
			ent = {
				'debug'    : av.debug || 0,
				'name'     : av.name  || 'animation' + ak,
				'timeline' : av.timeline,
				'loop_id'  : -1,
				'__RECT'   : 0,
			};
			if ( ! $.isUndef(av.loop_id) )  ent.loop_id = av.loop_id; // 0 is valid
			quad.animation[ak] = ent;
		}); // quad.animation.forEach

		quad.skeleton.forEach(function(sv,sk){
			if ( ! Array.isArray(sv.bone) || sv.bone.length < 1 )
				return nullent(quad.skeleton, sk);
			sv.bone.forEach(function(bv,bk){
				ent = {
					'debug'     : bv.debug  || 0,
					'name'      : bv.name   || 'bone ' + bk,
					'attach'    : bv.attach || 0,
				};
				sv.bone[bk] = ent;
			}); // sv.bone.forEach
			quad.skeleton[sk] = {
				'debug'  : sv.debug || 0,
				'name'   : sv.name  || 'skeleton ' + sk,
				'bone'   : sv.bone,
				'__RECT' : 0,
			};
		}); // quad.skeleton.forEach

		return quad;
	}

	//////////////////////////////

	__.drawLines = function( qdata, layer, mat4, quad ){
		var clines = [];

		var debug = [];
		var did, dbg;
		layer.forEach(function(lv,lk){
			if ( ! lv )
				return;

			dbg = JSON.stringify(lv.debug);
			did = debug.indexOf(dbg);
			if ( did < 0 ){
				did = debug.length;
				debug.push(dbg);
				clines[did] = [];
			}

			var dst = Q.math.quad_multi4(mat4, lv[quad]);
			clines[did] = clines[did].concat(dst);
		});

		var color = [
			[1,0,0,1] , [0,1,0,1] , [0,0,1,1] , // rgb
			[0,1,1,1] , [1,0,1,1] , [1,1,0,1] , // cmy
			[0,0,0,1] , [1,1,1,1] ,             // black white
			[0.5,0  ,0  ,1] , [0  ,0.5,0  ,1] , [0  ,0  ,0.5,1] , // 0.5 rgb
			[0  ,0.5,0.5,1] , [0.5,0  ,0.5,1] , [0.5,0.5,0  ,1] , // 0.5 cmy
			[0.5,0.5,0.5,1] , // gray
		];

		Q.gl.enableBlend(0);
		clines.forEach(function(cv,ck){
			var cid = qdata.line_index % color.length;
			Q.gl.drawLine(cv, color[cid]);

			qdata.line_index++;
			qdata.is_draw = true;
		});
	}

	__.drawHitbox = function( qdata, hid, mat4 ){
		var layer = qdata.QUAD.hitbox[hid].layer;
		__.drawLines(qdata, layer, mat4, 'hitquad');
	}

	//////////////////////////////

	__.drawKeyframeTex = function( qdata, layer, order, mat4, color ){
		var dummysrc = [0,0 , 0,0 , 0,0 , 0,0];

		var zrate = 1.0 / (layer.length + 1);
		var buf_list = [];
		var depth = 1.0;
		//console.log('order',order);

		// draw layers by keyframe order
		order.forEach(function(ov){
			var lv = layer[ov];
			if ( ! lv )
				return;
			depth -= zrate;

			var bid = lv.blend_id | 0;
			if ( ! buf_list[bid] )
				buf_list[bid] = { dst:[] , src:[] , fog:[] , z:[] };
			var ent = buf_list[bid];

			if ( lv.tex_id < 0 || ! qdata.IMAGE[lv.tex_id] )
				var src = dummysrc;
			else
				var src = Q.math.vram_srcquad(lv.srcquad, qdata.IMAGE[lv.tex_id].pos);
			ent.src = ent.src.concat(src);

			var dst = Q.math.quad_multi4(mat4, lv.dstquad);
			var xyz = Q.math.perspective_quad(dst);
			ent.dst = ent.dst.concat(xyz);

			var clr = Q.math.fog_multi4(color, lv.fogquad);
			ent.fog = ent.fog.concat(clr);

			ent.z = ent.z.concat([depth , depth , depth , depth]);
		});
		//console.log('buf_list',buf_list);

		Q.gl.enableDepth('LESS');
		buf_list.forEach(function(bv,bk){
			if ( ! bv || ! qdata.QUAD.blend[bk] )
				return;
			qdata.is_draw = true;
			Q.gl.enableBlend ( qdata.QUAD.blend[bk] );
			Q.gl.drawKeyframe( bv.dst, bv.src, bv.fog, bv.z, qdata.VRAM );
		});
		Q.gl.enableDepth(0);
	}

	__.drawKeyframe = function( qdata, kid, mat4, color ){
		var key = qdata.QUAD.keyframe[kid];
		if ( qdata.is_lines )
			return __.drawLines(qdata, key.layer, mat4, 'dstquad');
		else
			return __.drawKeyframeTex(qdata, key.layer, key.order, mat4, color);
	}

	//////////////////////////////

	__.drawMIX = function( qdata, id, mat4, color ){
		var mix = qdata.QUAD.__MIX[id];
		mix.forEach(function(mv,mk){
			switch ( mv.type ){
				case 'keyframe':
					if ( qdata.is_lines )
						return __.drawLines(qdata, mv.layer, mat4, 'dstquad');
					else
						return __.drawKeyframeTex(qdata, mv.layer, mv.order, mat4, color);
				case 'hitbox':
					if ( ! qdata.is_hits )
						return;
					return __.drawLines(qdata, mv.layer, mat4, 'hitquad');
			} // switch ( mv.type )
		});
	}

	__.mixAttach = function( qdata, cur, nxt, rate, keymix, hitmix ){
		var mixslot = [];

		function nextlayer( next, s ){
			if ( next.type === s )
				return qdata.QUAD[s][ next.id ].layer;
			if ( next.type === 'slot' ){
				var slot = qdata.QUAD.slot[ next.id ];
				for ( var i=0; i < slot.length; i++ ){
					if ( slot[i].type === s )
						return qdata.QUAD[s][ slot[i].id ].layer;
				}
			}
			return 0;
		}
		function addmixslot( mcur, mnxt ){
			switch( mcur.type ){
				case 'slot':
					var slot = qdata.QUAD.slot[ cur.id ];
					slot.forEach(function(sv,sk){
						addmixslot(sv, mnxt);
					});
					return;
				case 'keyframe':
				case 'hitbox':
					var curlayer = qdata.QUAD[ mcur.type ][ mcur.id ].layer;
					if ( ! curlayer )
						return;
					var ent = {
						'type'  : mcur.type,
						'layer' : $.copyObject( curlayer ),
					};
					if ( mcur.type === 'keyframe' )
						ent.order = qdata.QUAD[ mcur.type ][ mcur.id ].order;

					var mix = 0;
					mix |= ( mcur.type === 'keyframe' && keymix );
					mix |= ( mcur.type === 'hitbox'   && hitmix );
					if ( ! mix )
						return mixslot.push(ent);

					var nxtlayer = nextlayer(mnxt, mcur.type);
					if ( ! nxtlayer )
						return mixslot.push(ent);
					if ( curlayer.length !== nxtlayer.length )
						return mixslot.push(ent);

					ent.layer.forEach(function(lv,lk){
						if ( ! lv || ! nxtlayer[lk] )
							return;
						if ( keymix && lv.dstquad && nxtlayer[lk].dstquad )
							lv.dstquad = Q.math.quad_mix( rate, curlayer[lk].dstquad, nxtlayer[lk].dstquad );
						if ( hitmix && lv.hitquad && nxtlayer[lk].hitquad )
							lv.hitquad = Q.math.quad_mix( rate, curlayer[lk].hitquad, nxtlayer[lk].hitquad );
						if ( keymix && lv.fogquad && nxtlayer[lk].fogquad )
							lv.fogquad = Q.math.fog_mix ( rate, curlayer[lk].fogquad, nxtlayer[lk].fogquad );
					});
					return mixslot.push(ent);
			} // switch( mcur.type )
		}
		addmixslot(cur, nxt);

		// return attach object
		var id = qdata.QUAD.__MIX.length;
		qdata.QUAD.__MIX.push(mixslot);
		return {
			'type' : '__MIX',
			'id'   : id,
		};
	}

	__.animTimeIndex = function( fps, anim ){
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

	__.animCurrent = function( qdata, aid, mat4, color ){
		var ret = {
			attach : 0,
			mat4   : mat4,
			color  : color,
		}

		// check for valid range
		if ( qdata.anim_fps < 0 )
			return ret;
		var anim = qdata.QUAD.animation[aid];

		// get current frame
		var t = __.animTimeIndex(qdata.anim_fps, anim);
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

		ret.attach = __.mixAttach(qdata, cur.attach, nxt.attach, rate, cur.keyframe_mix, cur.hitbox_mix);
		return ret;
	}

	//////////////////////////////

	__.drawSkeleton = function( qdata, sid, mat4, color ){
		var bone = qdata.QUAD.skeleton[sid].bone;
		bone.forEach(function(bv,bk){
			__.drawAttach(qdata, bv.attach, mat4, color);
		});
	}

	//////////////////////////////

	$.isValidAttach = function( qdata, type, id ){
		if ( ! Array.isArray( qdata.QUAD[ type ] ) )
			return false;
		if ( ! qdata.QUAD[ type ][ id ] )
			return false;
		return true;
	}

	__.drawAttach = function( qdata, attach, mat4, color ){
		if ( ! $.isValidAttach(qdata, attach.type, attach.id) )
			return;
		switch ( attach.type ){
			case 'keyframe':
				return __.drawKeyframe( qdata, attach.id, mat4, color );
			case 'animation':
				var t = __.animCurrent( qdata, attach.id, mat4, color );
				if ( ! t.attach )
					return;
				return __.drawAttach( qdata, t.attach, t.mat4, t.color );
			case 'slot':
				qdata.QUAD.slot[ attach.id ].forEach(function(sv,sk){
					__.drawAttach(qdata, sv, mat4, color);
				});
				return;
			case 'hitbox':
				if ( ! qdata.is_hits )
					return;
				return __.drawHitbox( qdata, attach.id, mat4 );
			case 'skeleton':
				return __.drawSkeleton( qdata, attach.id, mat4, color );
			case '__MIX':
				return __.drawMIX( qdata, attach.id, mat4, color );
			case 'quad':
				var qid = qdata.QUAD.quad[ attach.id ];
				if ( $.isUndef(qid) || qid < 0 )
					return;
				return $.qdata_draw( qdata.LIST[qid], mat4, color );
		} // switch ( attach.type )
	}

	$.qdata_draw = function( qdata, mat4, color ){
		if ( ! qdata.QUAD )
			return;
		var m4 = Q.math.matrix_multi44( mat4, qdata.matrix );
		var c4 = Q.math.vec4_multi(color, qdata.color);
		return __.drawAttach(qdata, qdata.attach, m4, c4);
	}

	$.qdata_clear = function( qdata ){
		if ( ! qdata.QUAD )
			return;
		Q.gl.clear();
		qdata.is_draw = false;
		qdata.line_index = 0;
		qdata.QUAD.__MIX = [];
	}

	$.viewerCamera = function( qdata, autozoom ){
		var canvsz = Q.gl.canvasSize();

		qdata.zoom = 1.0;
		var movex = canvsz[0] * 0  ; // no change
		var movey = canvsz[1] * 0.5; // half downward

		if ( autozoom ){
			var sprsize = Q.export.rectAttach(qdata, qdata.attach.type, qdata.attach.id);
			if ( sprsize ){
				if ( sprsize[0] < 0 || sprsize[1] < 0 ){
					// is sprite , 0,0 is at center
					// x1 or y1 is negative
					var symm = Q.math.rect_symmetry(sprsize);
					var zoomx = canvsz[0] / symm[0];
					var zoomy = canvsz[1] / symm[1];
					qdata.zoom = ( zoomx < zoomy ) ? zoomx : zoomy;
				}
				else {
					// is map , 0,0 is top-left
					// x1 and y1 is positive
					var hw = (sprsize[2] - sprsize[0]) * 0.5;
					var hh = (sprsize[3] - sprsize[1]) * 0.5;
					var zoomx = canvsz[0] / hw;
					var zoomy = canvsz[1] / hh;
					qdata.zoom = ( zoomx < zoomy ) ? zoomx : zoomy;
					movex = (sprsize[0] - hw) * qdata.zoom;
					movey = (sprsize[1] - hh) * qdata.zoom;
				}
			} // if ( sprsize )
		} // if ( autozoom )

		var m4 = Q.math.matrix4();
		m4[0+3] = movex;
		m4[4+3] = movey;

		m4[0+0] = qdata.zoom;
		m4[4+1] = qdata.zoom;
		if ( qdata.is_flipx )  m4[0+0] = -m4[0+0];
		if ( qdata.is_flipy )  m4[4+1] = -m4[4+1];
		return m4;
	}

	$.isChanged = function( qdata ){
		var c = 0;
		c |= ( qdata.prev[0] !== qdata.attach.type );
		c |= ( qdata.prev[1] !== qdata.attach.id   );
		c |= ( qdata.prev[2] !== qdata.anim_fps    );
		c |= ( qdata.prev[3] !== qdata.is_lines    );
		c |= ( qdata.prev[4] !== qdata.is_hits     );
		c |= ( qdata.prev[5] !== qdata.is_flipx    );
		c |= ( qdata.prev[6] !== qdata.is_flipy    );
		c |= ( qdata.prev[7] !== qdata.zoom        );

		if ( c ){
			qdata.prev = [
				qdata.attach.type ,
				qdata.attach.id   ,
				qdata.anim_fps    ,
				qdata.is_lines    ,
				qdata.is_hits     ,
				qdata.is_flipx    ,
				qdata.is_flipy    ,
				qdata.zoom        ,
			];
		}
		return c;
	}

	//////////////////////////////

} // function QuadFunc(Q)
