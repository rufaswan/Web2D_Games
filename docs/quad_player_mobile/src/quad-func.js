function QuadFunc(Q){
	var $ = this;
	var m = {};

	//////////////////////////////

	m.LOGS = [];
	$.log = function(){
		var arg = [].slice.call(arguments);
		var txt = JSON.stringify(arg);
		m.LOGS.unshift( txt );
		while ( m.LOGS.length > 20 )
			m.LOGS.pop();
		return true;
	}
	$.error = function(){
		var arg = [].slice.call(arguments);
		var txt = JSON.stringify(arg);
		m.LOGS.unshift( 'ERROR : ' + txt );
		while ( m.LOGS.length > 20 )
			m.LOGS.pop();
		return false;
	}
	$.console = function(){
		return m.LOGS.join("\n\n");
	}

	$.arrayClean = function( list ){
		if ( ! Array.isArray(list) )
			return;
		var len = list.length;
		while ( len > 0 ){
			len--;
			if ( list[len] === 0 )
				list.splice(len, 1);
		}
		return;
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
								var p = new Promise(function(resolve, reject){
									var src = Q.binary.uint2txt(dat);
									resolve(src);
								}).then(function(text){
									return m.uploadHandler(qdata, 'quad', key[i], text);
								});
								pro.push(p);
								break;
							case 'png':
								var p = new Promise(function(resolve, reject){
									var src = 'data:image/png;base64,' + Q.binary.toBase64(dat);
									resolve(src);
								}).then(function(data){
									return m.uploadHandler(qdata, 'image', key[i], src);
								});
								pro.push(p);
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
					return m.uploadHandler(qdata, 'quad', up.name, text);
				});

			case 'png':
				return new Promise(function(resolve, reject){
					var reader = new FileReader;
					reader.onload = function(){
						resolve(reader.result);
					}
					reader.readAsDataURL(up);
				}).then(function(data){
					return m.uploadHandler(qdata, 'image', up.name, data);
				});
		} // switch ( ext )
		return 0;
	}

	m.uploadHandler = function( qdata, type, fname, data ){
		switch( type ){
			case 'quad':
				var quad   = JSON.parse(data);
				qdata.QUAD = m.quadfileCheck(quad);
				qdata.name = fname.replace(/[^A-Za-z0-9]/g, '_');
				return $.log('UPLOAD quad = ' + fname);

			case 'image':
				return new Promise(function(resolve, reject){
					var fnm = fname.match(/\.([0-9]+)\./);
					var tid = fnm[1];
					var tex = qdata.IMAGE[tid];
					if ( ! tex )
						return 0;

					var img = new Image;
					img.onload = function(){
						resolve([tid,img]);
					}
					img.src = data;
				}).then(function(res){
					var tid = res[0];
					var img = res[1];
					if ( Q.gl.isMaxTextureSize(img.width, img.height) )
						return $.error('OVER Image Max Texture Size = ' + fname);

					var tex = qdata.IMAGE[tid];
					tex.w = img.width;
					tex.h = img.height;
					tex.name = fname;
					Q.gl.updateTexture(tex.tex, img);
					return $.log('UPLOAD image = ' +tid+ ' , ' +tex.w+ 'x' +tex.h+ ' , ' +fname);
				});

			case 'video':
				return new Promise(function(resolve, reject){
					var fnm = fname.match(/\.([0-9]+)\./);
					var tid = fnm[1];
					if ( ! qdata.IMAGE[tid] )
						return;

					var video = document.createElement('video');
					video.onload = function(){
						resolve([tid,video]);
					}
					video.src = data;
				}).then(function(res){
					var tid   = res[0];
					var video = res[1];
					video.pause();
					video.currentTime = 0; // in seconds
					video.addEventListener('seeked', function(){
						if ( Q.gl.isMaxTextureSize(video.videoWidth, video.videoHeight) )
							return $.error('OVER Video Max Texture Size = ' + fname);

						var tex = qdata.IMAGE[tid];
						tex.w = video.videoWidth;
						tex.h = video.videoHeight;
						tex.name = fname;
						Q.gl.updateTexture(tex.tex , video);
					});

					qdata.VIDEO[tid] = video;
					return $.log('UPLOAD video = ' + tid + ' , ' + fname);
				});
		} // switch( type )
		return 0;
	}

	m.keyFogQuad = function( fog ){
		if ( typeof fog === 'string' ){
			var c = Q.math.css_color(fog);
			return [].concat(c, c, c, c);
		}
		if ( Array.isArray(fog) && fog.length === 4 ){
			var c0 = Q.math.css_color( fog[0] );
			var c1 = Q.math.css_color( fog[1] );
			var c2 = Q.math.css_color( fog[2] );
			var c3 = Q.math.css_color( fog[3] );
			return [].concat(c0, c1, c2, c3);
		}
		// default solid white
		return [1,1,1,1 , 1,1,1,1 , 1,1,1,1 , 1,1,1,1];
	}

	m.quadfileCheck = function( quad ){
		quad.blend     = quad.blend     || [];
		quad.keyframe  = quad.keyframe  || [];
		quad.hitbox    = quad.hitbox    || [];
		quad.animation = quad.animation || [];
		quad.skeleton  = quad.skeleton  || [];
		quad.__MIX = [];

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

		quad.keyframe.forEach(function(kv,kk){
			if ( ! Array.isArray(kv.layer) )
				return nullent(quad.keyframe, kk);
			kv.layer.forEach(function(lv,lk){
				if ( ! Array.isArray(lv.dstquad) || lv.dstquad.length !== 8 )
					return nullent(kv.layer, lk);
				ent = {
					'debug'    : lv.debug || 0,
					'dstquad'  : lv.dstquad,
					'tex_id'   : -1,
					'blend_id' : -1,
					'fogquad'  : m.keyFogQuad( lv.fogquad ),
					'srcquad'  : 0,
				};
				if ( ! $.isUndef(lv.blend_id) )  ent.blend_id = lv.blend_id; // 0 is valid
				if ( ! $.isUndef(lv.tex_id  ) )  ent.tex_id   = lv.tex_id;   // 0 is valid
				if ( Array.isArray(lv.srcquad) && lv.srcquad.length === 8 )
					ent.srcquad = lv.srcquad;
				kv.layer[lk] = ent;
			});
			quad.keyframe[kk] = {
				'debug'  : kv.debug || 0,
				'name'   : kv.name  || 'keyframe '+ kk,
				'layer'  : kv.layer,
				'__RECT' : 0,
			};
		}); // quad.keyframe.forEach

		quad.hitbox.forEach(function(hv,hk){
			if ( ! Array.isArray(hv.layer) )
				return nullent(quad.hitbox, hk);
			hv.layer.forEach(function(lv,lk){
				if ( ! Array.isArray(lv.hitquad) && lv.hitquad.length !== 8 )
					return nullent(hv.layer, lk);
				hv.layer[lk] = {
					'debug'   : lv.debug || 0,
					'hitquad' : lv.hitquad,
				};
			});
			quad.hitbox[hk] = {
				'debug' : hv.debug || 0,
				'name'  : hv.name  || 'hitbox '+ hk,
				'layer' : hv.layer,
			};
		}); // quad.hitbox.forEach

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
				if ( Array.isArray(tv.matrix) && tv.matrix.length === 16 )
					ent.matrix = tv.matrix;
				av.timeline[tk] = ent;
			});
			$.arrayClean(av.timeline);
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
					'parent_id' : -1,
					'order'     : bk,
				};
				if ( ! $.isUndef(bv.parent_id) && bv.parent_id !== bk )
					ent.parent_id = bv.parent_id; // 0 is valid
				if ( ! $.isUndef(bv.order) )
					ent.order = bv.order; // 0 is valid
				sv.bone[bk] = ent;
			});
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

	$.drawLines = function( qdata, layer, mat4, quad ){
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

		clines.forEach(function(cv,ck){
			var cid = qdata.line_index % color.length;
			Q.gl.drawLine(cv, color[cid]);

			qdata.line_index++;
			qdata.is_draw = true;
		});
	}

	$.drawHitbox = function( qdata, hid, mat4 ){
		var layer = qdata.QUAD.hitbox[hid].layer;
		$.drawLines(qdata, layer, mat4, 'hitquad');
	}

	m.drawKeyframeLine = function( qdata, layer, mat4 ){
		$.drawLines(qdata, layer, mat4, 'dstquad');
	}

	//////////////////////////////

	m.drawKeyframeTex = function( qdata, layer, mat4, color ){
		var ctexs = [];

		layer.forEach(function(lv,lk){
			if ( ! lv )
				return;
			var bid = lv.blend_id;
			if ( ! ctexs[bid] )
				ctexs[bid] = { dst:[] , src:[] , fog:[] , tid:[] };

			var kv = ctexs[bid];

			var dst = Q.math.quad_multi4(mat4, lv.dstquad);
			var xyz = Q.math.perspective_quad(dst);
			kv.dst = kv.dst.concat(xyz);

			var clr = Q.math.fog_multi4(color, lv.fogquad);
			kv.fog = kv.fog.concat(clr);

			if ( lv.srcquad )
				kv.src = kv.src.concat(lv.srcquad);
			else
				kv.src = kv.src.concat([0,0 , 0,0 , 0,0 , 0,0]);
			kv.tid = kv.tid.concat([lv.tex_id , lv.tex_id , lv.tex_id , lv.tex_id]);
		});

		qdata.QUAD.blend.forEach(function(bv,bk){
			if ( ! ctexs[bk] )
				return;
			qdata.is_draw = true;
			var kv = ctexs[bk];
			Q.gl.enableBlend ( bv );
			Q.gl.drawKeyframe( kv.dst, kv.src, kv.fog, kv.tid, qdata.IMAGE );
		});
	}

	$.drawKeyframe = function( qdata, kid, mat4, color ){
		var layer = qdata.QUAD.keyframe[kid].layer;
		if ( qdata.is_lines )
			return m.drawKeyframeLine(qdata, layer, mat4);
		else
			return m.drawKeyframeTex(qdata, layer, mat4, color);
	}

	//////////////////////////////

	$.drawMIX = function( qdata, id, mat4, color ){
		var mix = qdata.QUAD.__MIX[id];
		mix.forEach(function(mv,mk){
			switch ( mv.type ){
				case 'keyframe':
					if ( qdata.is_lines )
						return m.drawKeyframeLine(qdata, mv.layer, mat4);
					else
						return m.drawKeyframeTex(qdata, mv.layer, mat4, color);
				case 'hitbox':
					return $.drawLines(qdata, mv.layer, mat4, 'hitquad');
			} // switch ( mv.type )
		});
	}

	m.mixAttach = function( qdata, cur, nxt, rate, keymix, hitmix ){
		var MIX = qdata.QUAD.__MIX;
		var mixslot = [];

		function nextlayer( next, s ){
			if ( next.type === s )
				return qdata.QUAD[s][ next.id ].layer;
			if ( next.type === 'slot' ){
				var slot = qdata.QUAD.slot[ next.id ];
				slot.forEach(function(sv,sk){
					if ( sv.type === s )
						return qdata.QUAD[s][ sv.id ].layer;
				});
			}
			return 0;
		}
		function addmixslot( mcur, mnxt ){
			switch( mcur.type ){
				case 'keyframe':
				case 'hitbox':
					var curlayer = qdata.QUAD[ mcur.type ][ mcur.id ].layer;
					if ( ! curlayer )
						return;

					var ent = {
						'type'  : mcur.type,
						'layer' : $.copyObject( curlayer ),
					};

					var mix = 0;
					mix |= ( mcur.type === 'keyframe' && keymix );
					mix |= ( mcur.type === 'hitbox'   && hitmix );
					if ( ! mix )
						return mixslot.push(ent);

					var nxtlayer = nextlayer(mnxt, mcur.type);
					if ( ! nxtlayer )
						return mixslot.push(ent);
					if ( ent.layer.length !== nxtlayer.length )
						return mixslot.push(ent);

					var quad = '';
					if ( mcur.type === 'keyframe' )  quad = 'dstquad';
					if ( mcur.type === 'hitbox'   )  quad = 'hitquad';

					ent.layer.forEach(function(lv,lk){
						if ( ! lv )
							return;
						ent.layer[lk][quad] = Q.math.quad_mix( rate, curlayer[lk][quad], nxtlayer[lk][quad] );
					});
					return mixslot.push(ent);
			} // switch( mcur.type )
		}

		if ( cur.type === 'slot' ){
			var slot = qdata.QUAD.slot[ cur.id ];
			slot.forEach(function(sv,sk){
				addmixslot(sv, nxt);
			});
		}
		else
			addmixslot(cur, nxt);

		// return attach object
		var id = MIX.length;
		MIX.push(mixslot);
		return {
			'type' : '__MIX',
			'id'   : id,
		};
	}

	m.animTimeIndex = function( fps, anim ){
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

	m.animCurrent = function( qdata, aid, mat4, color ){
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
		var t = m.animTimeIndex(qdata.anim_fps, anim);
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

		if ( ! cur.keyframe_mix && ! cur.hitbox_mix )
			return ret;

		ret.attach = m.mixAttach(qdata, cur.attach, nxt.attach, rate, cur.keyframe_mix, cur.hitbox_mix);
		return ret;
	}

	//////////////////////////////

	m.skeletonTree = function( qdata, bone, bid, transform, mat4, color ){
		if ( transform[bid] )
			return true;

		var bv  = bone[bid];
		var cur = {
			id     : bid,
			attach : 0,
			mat4   : mat4,
			color  : color,
			order  : bv.order,
		};

		// if has parent , calculate it first
		// then inherit its mat4 and color
		if ( bv.parent_id >= 0 ){
			if ( ! transform[ bv.parent_id ] )
				return false;
			else {
				var par = transform[ bv.parent_id ];
				cur.mat4  = par.mat4;
				cur.color = par.color;
			}
		}

		// if dummy bone = done
		if ( ! bv.attach ){
			transform[bid] = cur;
			return true;
		}

		// mat4 + color from 'animation' needs to be recalculated
		// *any* is done
		cur.attach = bv.attach;
		if ( bv.attach.type !== 'animation' ){
			transform[bid] = cur;
			return true;
		}

		var t = m.animCurrent(qdata, bv.attach.id, cur.mat4, cur.color);
		t.id    = bid;
		t.order = bv.order;
		transform[bid] = t;
		return true;
	}

	$.drawSkeleton = function( qdata, sid, mat4, color ){
		var bone = qdata.QUAD.skeleton[sid].bone;

		// we want parent * current = nested matrix
		// save pre-computed to transform array
		// recursive function = out of memory
		var transform = [];
		var is_done = false;
		while ( ! is_done ){
			is_done = true;
			bone.forEach(function(bv,bk){
				var res = m.skeletonTree( qdata, bone, bk, transform, mat4, color );
				if ( ! res )
					is_done = false;
			});
		} // while ( ! is_done )

		transform.sort(function(a,b){
			return a.order - b.order;
		});
		transform.forEach(function(tv){
			if ( ! tv.attach )
				return;
			$.drawAttach(qdata, tv.attach, tv.mat4, tv.color);
		});

		transform = null;
	}

	//////////////////////////////

	$.drawAttach = function( qdata, attach, mat4, color ){
		if ( ! Array.isArray( qdata.QUAD[ attach.type ] ) )
			return;
		if ( ! qdata.QUAD[ attach.type ][ attach.id ] )
			return;
		switch ( attach.type ){
			case 'keyframe':
				return $.drawKeyframe( qdata, attach.id, mat4, color );
			case 'animation':
				var t = m.animCurrent( qdata, attach.id, mat4, color );
				if ( ! t.attach )
					return;
				return $.drawAttach( qdata, t.attach, t.mat4, t.color );
			case 'slot':
				qdata.QUAD.slot[ attach.id ].forEach(function(sv,sk){
					$.drawAttach(qdata, sv, mat4, color);
				});
				return;
			case 'hitbox':
				if ( ! qdata.is_hits )
					return;
				return $.drawHitbox( qdata, attach.id, mat4 );
			case 'skeleton':
				return $.drawSkeleton( qdata, attach.id, mat4, color );
			case '__MIX':
				return $.drawMIX( qdata, attach.id, mat4, color );
			case 'quad':
				var qid = qdata.QUAD.quad[ attach.id ];
				if ( qid === undefined || qid < 0 )
					return;
				return $.qdata_draw( qdata.LIST[qid], mat4, color );
		} // switch ( attach.type )
	}

	$.qdata_draw = function( qdata, mat4, color ){
		if ( ! qdata.QUAD )
			return;
		var m4 = Q.math.matrix_multi44( mat4, qdata.matrix );
		var c4 = Q.math.vec4_multi(color, qdata.color);
		return $.drawAttach(qdata, qdata.attach, m4, c4);
	}

	$.qdata_clear = function( qdata ){
		if ( ! qdata.QUAD )
			return;
		Q.gl.clear();
		qdata.is_draw = false;
		qdata.line_index = 0;
		qdata.QUAD.__MIX = [];
	}

	$.autofitZoom = function( qdata, autozoom ){
		if ( ! autozoom )
			return 1;
		var canvsz  = Q.gl.canvasSize();
		var sprsize = Q.export.rectAttach(qdata, qdata.attach.type, qdata.attach.id);
		if ( sprsize ){
			var symm = Q.export.sizeSymmetry(sprsize);
			var zoomx = canvsz[0] / symm[0];
			var zoomy = canvsz[1] / symm[1];
			return ( zoomx < zoomy ) ? zoomx : zoomy;
		}
		return 1;
	}

	$.viewerCamera = function(qdata, autofit=false){
		var m4 = Q.math.matrix4();
		var canvsz = Q.gl.canvasSize();

		m4[0+3] = canvsz[0] *   0; // move x , no change
		m4[4+3] = canvsz[1] * 0.5; // move y , half downward

		if ( qdata.is_flipx )  m4[0+0] = -m4[0+0];
		if ( qdata.is_flipy )  m4[4+1] = -m4[4+1];

		m4[0+0] *= qdata.zoom;
		m4[4+1] *= qdata.zoom;
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
