function QuadExport(Q){
	var $ = this;
	var m = {}; // all public

	//////////////////////////////

	m.sizeCompare = function( base, size ){
		if ( size[0] < base[0] )  base[0] = size[0]; // x1
		if ( size[1] < base[1] )  base[1] = size[1]; // y1
		if ( size[2] > base[2] )  base[2] = size[2]; // x2
		if ( size[3] > base[3] )  base[3] = size[3]; // y2
	}

	$.sizeAttach = function( qdata, type, id ){
		if ( ! Array.isArray( qdata.QUAD[ type ] ) )
			return 0;
		if ( ! qdata.QUAD[ type ][ id ] )
			return 0;

		var cur = qdata.QUAD[ type ][ id ];
		switch ( type ){
			case 'keyframe':
				if ( cur.__SIZE )
					return cur.__SIZE;

				var siz = [0,0,0,0];
				cur.layer.forEach(function(lv,lk){
					if ( ! lv )
						return;
					var dst = lv.dstquad;
					for ( var i=0; i < 8; i += 2 ){
						if ( dst[i+0] < siz[0] )  siz[0] = dst[i+0];
						if ( dst[i+1] < siz[1] )  siz[1] = dst[i+1];
						if ( dst[i+0] > siz[2] )  siz[2] = dst[i+0];
						if ( dst[i+1] > siz[3] )  siz[3] = dst[i+1];
					}
				});
				cur.__SIZE = siz;
				return siz;

			case 'slot':
				var siz = [0,0,0,0];
				cur.forEach(function(sv,sk){
					var xy = $.sizeAttach(qdata, sv.type, sv.id);
					if ( ! xy )
						return;
					m.sizeCompare(siz, xy);
				});
				return siz;

			case 'animation':
				if ( cur.__SIZE )
					return cur.__SIZE;

				var siz = [0,0,0,0];
				cur.timeline.forEach(function(tv,tk){
					if ( ! tv.attach )
						return;

					var xy = $.sizeAttach(qdata, tv.attach.type, tv.attach.id);
					if ( ! xy )
						return;
					var xy2 = Q.math.quad_multi4(tv.matrix, xy);
					var t;
					if ( xy2[0] > xy2[2] ){
						t = xy2[0];
						xy2[0] = xy2[2];
						xy2[2] = t;
					}
					if ( xy2[1] > xy2[3] ){
						t = xy2[1];
						xy2[1] = xy2[3];
						xy2[3] = t;
					}
					m.sizeCompare(siz, xy2);
				});
				cur.__SIZE = siz;
				return siz;

			case 'skeleton':
				if ( cur.__SIZE )
					return cur.__SIZE;

				var res = [];
				var is_done = false;
				while ( ! is_done ){
					is_done = true;
					cur.bone.forEach(function(bv,bk){
						if ( res[bk] )
							return;

						is_done = false;
						var xy  = 0;
						if ( bv.parent_id < 0 ){
							if ( bv.attach )
								xy = $.sizeAttach(qdata, bv.attach.type, bv.attach.id);

							if ( xy ){
								res[bk] = xy;
								res[bk][4] = (xy[0] + xy[2]) * 0.5; // mid x
								res[bk][5] = (xy[1] + xy[3]) * 0.5; // mid y
							}
							else
								res[bk] = [0,0,0,0 , 0,0];

							return;
						}

						var par = res[bv.parent_id];
						if ( ! par )
							return;

						if ( bv.attach )
							xy = $.sizeAttach(qdata, bv.attach.type, bv.attach.id);

						if ( xy ){
							res[bk] = [
								xy[0] + par[4] , xy[1] + par[5] ,
								xy[2] + par[4] , xy[3] + par[5] ,
							];
							res[bk][4] = (res[bk][0] + res[bk][2]) * 0.5; // mid x
							res[bk][5] = (res[bk][1] + res[bk][3]) * 0.5; // mid y
						}
						else
							res[bk] = par;
					});
				} // while ( ! is_done )

				var siz = [0,0,0,0];
				res.forEach(function(rv,rk){
					m.sizeCompare(siz, rv);
				});
				cur.__SIZE = siz;
				return siz;
		} // switch ( type )
		return 0;
	}

	$.sizeSymmetry = function( size ){
		var abs = [
			Math.abs(size[0]) , Math.abs(size[1]) ,
			Math.abs(size[2]) , Math.abs(size[3]) ,
		];
		var maxx = ( abs[0] > abs[2] ) ? abs[0] : abs[2];
		var maxy = ( abs[1] > abs[3] ) ? abs[1] : abs[3];
		return [ maxx , maxy ];
	}

	//////////////////////////////

	m.bak = {};
	m.backup = function( qdata, canvas, type, id, fps ){
		m.bak = {
			type  : qdata.attach.type ,
			id    : qdata.attach.id   ,
			fps   : qdata.anim_fps    ,
			line  : qdata.is_lines    ,
			hit   : qdata.is_hits     ,
			canvw : canvas.width  ,
			canvh : canvas.height ,
			fname : qdata.name + '_' + type + '_' + id + '_' + fps,
		};
		qdata.attach.type = type;
		qdata.attach.id   = id;
		qdata.anim_fps    = fps;
		qdata.is_lines    = false;
		qdata.is_hits     = false;
	}

	m.restore = function( qdata, canvas ){
		qdata.attach.type = m.bak.type;
		qdata.attach.id   = m.bak.id;
		qdata.anim_fps    = m.bak.fps;
		qdata.is_lines    = m.bak.line;
		qdata.is_hits     = m.bak.hit;
		canvas.width      = m.bak.canvw;
		canvas.height     = m.bak.canvh;
	}

	m.download = function( fname, dataurl ){
		if ( ! fname || ! dataurl )
			return;

		var a = document.createElement('a');
		a.href = dataurl;
		a.setAttribute('download', fname);
		a.setAttribute('target'  , '_blank');
		a.click();
	}

	//////////////////////////////

	m.exportSheet = function( qdata, canvas ){
		var line_spacing = 1.15;
		var sprsize = $.sizeAttach(qdata, qdata.attach.type, qdata.attach.id);
		var sprwh = [
			(sprsize[2] - sprsize[0]) * line_spacing ,
			(sprsize[3] - sprsize[1]) * line_spacing ,
		];
		var sprmid = [
			(sprsize[2] + sprsize[0]) * 0.5 ,
			(sprsize[3] + sprsize[1]) * 0.5 ,
		];

		var texsize = Q.gl.maxTextureSize() >> 1;
		canvas.width  = Math.floor(texsize / sprwh[0]) * Math.floor(sprwh[0]);
		canvas.height = Math.floor(texsize / sprwh[1]) * Math.floor(sprwh[1]);

		// canvas from -halftex to +halftex
		// sprite 0,0 is at center
		var halfpos = [
			canvas.width * 0.5 , canvas.height * 0.5 ,
			sprwh[0]     * 0.5 , sprwh[1]      * 0.5 ,
		];

		var is_skip = false;
		var camera = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
		var color  = [1,1,1,1];
		Q.func.qdata_clear(qdata);

		if ( qdata.is_flipx )  camera[0+0] = -camera[0+0];
		if ( qdata.is_flipy )  camera[4+1] = -camera[4+1];

		// from -1.0 to +1.0
		for ( var dy = -halfpos[1]; dy < halfpos[1]; dy += sprwh[1] ){
			if ( is_skip )
				continue;

			// from -1.0 to +1.0
			for ( var dx = -halfpos[0]; dx < halfpos[0]; dx += sprwh[0] ){
				if ( is_skip )
					continue;

				camera[0+3] = dx + halfpos[2] - sprmid[0];
				camera[4+3] = dy + halfpos[3] - sprmid[1];

				qdata.is_draw = false;
				Q.func.qdata_draw(qdata, camera, color);
				if ( ! qdata.is_draw )
					is_skip = true;
				qdata.anim_fps++;
			} // for ( var dx = -canvpos[0]; dx < canvpos[0]; dx += sprwh[0] )
		} // for ( var dy = canvpos[1]; dy > -canvpos[1]; dy += sprwh[1] )

		return canvas.toDataURL('image/png');
	}

	m.exportZip = function( qdata, canvas, fmt ){
		var sprsize = $.sizeAttach(qdata, qdata.attach.type, qdata.attach.id);
		var symm = $.sizeSymmetry(sprsize);

		var line_spacing = 1.15;
		canvas.width  = Math.ceil(symm[0] * 2 * line_spacing);
		canvas.height = Math.ceil(symm[1] * 2 * line_spacing);

		var texsize = Q.gl.maxTextureSize() >> 1;
		var len  = Math.floor(texsize / canvas.width) * Math.floor(texsize / canvas.height);
		var list = {};

		var camera = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
		var color  = [1,1,1,1];

		if ( qdata.is_flipx )  camera[0+0] = -camera[0+0];
		if ( qdata.is_flipy )  camera[4+1] = -camera[4+1];

		for ( var i=0; i < len; i++ ){
			Q.func.qdata_clear(qdata);
			Q.func.qdata_draw (qdata, camera, color);
			if ( ! qdata.is_draw )
				break;

			switch ( fmt ){
				case 'png':
					// %06d.png
					var pad = '00000000' + qdata.anim_fps + '.png';
					var fn  = pad.substring( pad.length - 10 );

					list[fn] = QUAD.binary.fromBase64( canvas.toDataURL('image/png') );
					break;

				case 'rgba':
					// %06d.rgba
					var pad = '00000000' + qdata.anim_fps + '.rgba';
					var fn  = pad.substring( pad.length - 11 );

					list[fn] = Q.gl.readRGBA();
					break;
			} // switch ( fmt )

			qdata.anim_fps++;
		} // for ( var i=0; i < len; i++ )

		var uint8 = Q.binary.zipwrite(list);
		return 'data:application/zip;base64,' + Q.binary.toBase64(uint8);
	}

	//////////////////////////////

	$.export = function( fmt, qdata, canvas, type, id, fps ){
		var start = performance.now();
		m.backup(qdata, canvas, type, id, fps);

		switch ( fmt ){
			case 'png':
				var fname   = m.bak.fname + '.png';
				var dataurl = m.exportSheet(qdata, canvas);
				m.download(fname, dataurl);
				break;
			case 'zip':
				var fname   = m.bak.fname + '.png.zip';
				var dataurl = m.exportZip(qdata, canvas, 'png');
				m.download(fname, dataurl);
				break;
			case 'rgba':
				var fname   = m.bak.fname + '.rgba.zip';
				var dataurl = m.exportZip(qdata, canvas, 'rgba');
				m.download(fname, dataurl);
				break;
		} // switch ( fmt )

		m.restore(qdata, canvas);
		Q.func.log('QUAD export' , fmt , 'time' , performance.now()-start);

		// performance
		//   png    1193 ms   1,606,494 byte
		//   zip    4893 ms   3,332,087 byte
		//   rgba  62134 ms  59,437,722 byte
	}

	//////////////////////////////

} // function QuadExport
