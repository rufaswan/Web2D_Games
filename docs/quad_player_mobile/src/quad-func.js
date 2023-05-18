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

	$.arrayRepeat = function( val, cnt ){
		var res = [];
		if ( Array.isArray(val) ){
			for ( var i=0; i < cnt; i++ )
				res = res.concat(val);
		}
		else {
			for ( var i=0; i < cnt; i++ )
				res.push(val);
		}
		return res;
	}

	$.intPad = function( int, len ){
		var sign = false;
		int |= 0;
		if ( int < 0 ){
			sign = true;
			int  = -int;
		}
		var str = '00000000' + int.toString();
		str = str.substring(str.length - len);
		if ( sign )
			return '-' + str;
		else
			return str;
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
				var quad = JSON.parse(data);
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
		if ( quad.blend ){
			quad.blend.forEach(function(bv,bk){
				bv.debug = bv.debug || 0;
				bv.color = Q.math.css_color( bv.color );

				var valid = Q.gl.isValidConstant.apply(null, bv.mode);
				if ( bv.mode.length === 6 && valid )
					return;
				if ( bv.mode.length === 3 && valid )
					return;
				quad.blend[bk] = 0;
			});
		} // if ( quad.blend )

		if ( quad.keyframe ){
			quad.keyframe.forEach(function(kv,kk){
				if ( ! Array.isArray(kv.layer) || kv.layer.length < 1 ){
					quad.keyframe[kk] = 0;
					return;
				}
				kv.debug = kv.debug || 0;

				kv.layer.forEach(function(lv,lk){
					if ( ! Array.isArray(lv.dstquad) || lv.dstquad.length !== 8 ){
						kv.layer[lk] = 0;
						return;
					}
					lv.debug = lv.debug || 0;

					lv.fogquad  = m.keyFogQuad( lv.fogquad );
					lv.blend_id = ( lv.blend_id === undefined ) ? -1 : lv.blend_id; // 0 is valid
					lv.tex_id   = ( lv.tex_id   === undefined ) ? -1 : lv.tex_id;   // 0 is valid
					if ( ! Array.isArray(lv.srcquad) || lv.srcquad.length !== 8 )
						lv.srcquad = [0,0 , 0,0 , 0,0 , 0,0];
				});
			});
		} // if ( quad.keyframe )

		if ( quad.hitbox ){
			quad.hitbox.forEach(function(hv,hk){
				if ( ! Array.isArray(hv.layer) || hv.layer.length < 1 ){
					quad.hitbox[hk] = 0;
					return;
				}
				hv.debug = hv.debug || 0;

				hv.layer.forEach(function(lv,lk){
					if ( ! Array.isArray(lv.hitquad) && lv.hitquad.length !== 8 ){
						hv.layer[lk] = 0;
						return;
					}
					lv.debug = lv.debug || 0;
				});
			});
		} // if ( quad.hitbox )

		if ( quad.animation ){
			quad.animation.forEach(function(av,ak){
				if ( ! Array.isArray(av.timeline) || av.timeline.length < 1 ){
					quad.animation[ak] = 0;
					return;
				}
				av.loop_id = ( av.loop_id === undefined ) ? -1 : av.loop_id; // 0 is valid
				av.debug   = av.debug || 0;

				av.timeline.forEach(function(tv,tk){
					tv.debug = tv.debug || 0;
					tv.time  = tv.time  || 0;
						tv.time = Math.abs(tv.time);
					tv.mix   = ( ! tv.mix ) ? 0 : 1; // -1 => 0
					tv.color = Q.math.css_color(tv.color);

					if ( ! Array.isArray(tv.matrix) || tv.matrix.length !== 16 ){
						if ( tv.attach )
							tv.matrix = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
						else
							tv.matrix = [0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0];
					}
				});
			});
		} // if ( quad.animation )

		if ( quad.skeleton ){
			quad.skeleton.forEach(function(sv,sk){
				if ( ! Array.isArray(sv.bone) || sv.bone.length < 1 ){
					quad.skeleton[sk] = 0;
					return;
				}
				sv.debug = sv.debug || 0;

				sv.bone.forEach(function(bv,bk){
					if ( ! Array.isArray(bv.child) ){
						bv.child = 0;
					}
					bv.debug = bv.debug || 0;

					bv.order = ( bv.order === undefined ) ? bk : bv.order;  // 0 is valid
					bv.parent_id = ( bv.parent_id === undefined ) ? -1 : bv.parent_id; // 0 is valid
					if ( bv.parent_id === bk )
						bv.parent_id = -1;
				});
			});
		} // if ( quad.skeleton )

		return quad;
	}

	$.fileExtension = function( fn ){
		var ext = fn.split('.').pop();
		return ext.toLowerCase();
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

	m.drawKeyframeLine = function( qdata, kid, mat4 ){
		var layer = qdata.QUAD.keyframe[kid].layer;
		$.drawLines(qdata, layer, mat4, 'dstquad');
	}

	//////////////////////////////

	m.drawKeyframeTex = function( qdata, kid, mat4, color ){
		var layer = qdata.QUAD.keyframe[kid].layer;
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

			kv.src = kv.src.concat(lv.srcquad);
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
		if ( qdata.is_lines )
			return m.drawKeyframeLine(qdata, kid, mat4);
		else
			return m.drawKeyframeTex(qdata, kid, mat4, color);
	}

	//////////////////////////////

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

		var t = m.animTimeIndex(qdata.anim_fps, anim);
		var curid = t[0];
		if ( curid < 0 )
			return ret;

		var cur = anim.timeline[curid];
		ret.attach = cur.attach;
		if ( ! cur.mix ){
			ret.mat4  = Q.math.matrix_multi44( mat4 , cur.matrix );
			ret.color = Q.math.vec4_multi    ( color, cur.color  );
			return ret;
		}

		var nxtid = curid + 1;
		if ( nxtid >= anim.timeline.length ){
			if ( anim.loop_id < 0 )
				nxtid = curid;
			else
				nxtid = anim.loop_id;
		}

		var nxt  = anim.timeline[nxtid];
		var rate = t[1] / cur.time;
		var m4 = Q.math.matrix_mix( rate, cur.matrix, nxt.matrix );
		var c4 = Q.math.color_mix ( rate, cur.color , nxt.color  );
			ret.mat4  = Q.math.matrix_multi44( mat4 , m4 );
			ret.color = Q.math.vec4_multi    ( color, c4 );
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
	}

	$.isChanged = function( qdata ){
		var c = 0;
		c += ( qdata.prev[0] === qdata.attach.type );
		c += ( qdata.prev[1] === qdata.attach.id );
		c += ( qdata.prev[2] === qdata.anim_fps );
		c += ( qdata.prev[3] === qdata.is_lines );
		c += ( qdata.prev[4] === qdata.is_hits );
		if ( c === 5 )
			return false;

		qdata.prev = [
			qdata.attach.type ,
			qdata.attach.id   ,
			qdata.anim_fps    ,
			qdata.is_lines    ,
			qdata.is_hits     ,
		];
		return true;
	}

	//////////////////////////////


	//////////////////////////////

} // function QuadFunc(Q)
