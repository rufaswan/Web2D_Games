function QuadExport(Q){
	var $ = this; // public
	var __ = {};  // private

	//////////////////////////////

	__.rect_compare = function( rect, xy ){
		if ( rect[0] > xy[0] )  rect[0] = xy[0]; // x1
		if ( rect[1] > xy[1] )  rect[1] = xy[1]; // y1
		if ( rect[2] < xy[2] )  rect[2] = xy[2]; // x2
		if ( rect[3] < xy[3] )  rect[3] = xy[3]; // y2
	}

	$.rect_attach = function( qdata, type, id ){
		if ( ! Q.func.is_valid_attach(qdata, type, id) )
			return 0;

		var max  = 1 << 24;
		var rect = [max,max,-max,-max];
		var is_null = true;

		var cur = qdata.QUAD[ type ][ id ];
		switch ( type ){
			case 'keyframe':
			case 'hitbox':
				if ( cur.__RECT )
					return cur.__RECT;

				var quad = '';
				if ( type === 'keyframe' )  quad = 'dstquad';
				if ( type === 'hitbox'   )  quad = 'hitquad';
				cur.layer.forEach(function(lv,lk){
					if ( ! lv || ! lv[quad] )
						return;
					var dst = lv[quad];
					for ( var i=0; i < 8; i += 2 ){
						if ( rect[0] > dst[i+0] )  rect[0] = dst[i+0]; // x1
						if ( rect[1] > dst[i+1] )  rect[1] = dst[i+1]; // y1
						if ( rect[2] < dst[i+0] )  rect[2] = dst[i+0]; // x2
						if ( rect[3] < dst[i+1] )  rect[3] = dst[i+1]; // y2
					} // for ( var i=0; i < 8; i += 2 )
				});
				cur.__RECT = rect;
				return rect;

			case 'slot':
				cur.forEach(function(sv,sk){
					var xy = $.rect_attach(qdata, sv.type, sv.id);
					if ( ! xy )
						return;
					is_null = false;
					__.rect_compare(rect, xy);
				});
				if ( is_null )
					return 0;
				return rect;

			case 'animation':
				if ( cur.__RECT )
					return cur.__RECT;

				var is_null = true;
				cur.timeline.forEach(function(tv,tk){
					if ( ! tv.attach )
						return;
					var xy = $.rect_attach(qdata, tv.attach.type, tv.attach.id);
					if ( ! xy )
						return;
					is_null = false;
					var xy2 = Q.math.rect_multi4(tv.matrix, xy);
					var t;
					if ( xy2[0] > xy2[2] ){ // if x1 > x2  swap()
						t = xy2[0];
						xy2[0] = xy[2];
						xy2[2] = t;
					}
					if ( xy2[1] > xy[3] ){ // if y1 > y2  swap()
						t = xy2[1];
						xy2[1] = xy2[3];
						xy2[3] = t;
					}
					__.rect_compare(rect, xy2);
				});
				if ( is_null )
					return 0;
				cur.__RECT = rect;
				return rect;

			case 'skeleton':
				if ( cur.__RECT )
					return cur.__RECT;

				cur.bone.forEach(function(bv,bk){
					if ( ! bv || ! bv.attach )
						return;
					var xy = $.rect_attach(qdata, bv.type, bv.id);
					if ( ! xy )
						return;
					is_null = false;
					__.rect_compare(rect, xy);
				});
				if ( is_null )
					return 0;
				cur.__RECT = rect;
				return rect;
		} // switch ( type )
		return 0;
	}

	$.is_loop_attach = function( qdata, type, id ){
		if ( ! Q.func.is_valid_attach(qdata, type, id) )
			return false;
		switch ( type ){
			case 'slot':
				var slot = qdata.QUAD.slot[id];
				for ( var i=0; i < slot.length; i++ ){
					var loop = $.is_loop_attach(qdata, slot[i].type, slot[i].id);
					if ( loop )
						return true;
				}
				return false;
			case 'animation':
				var loop = qdata.QUAD.animation[id].loop_id;
				if ( loop < 0 )
					return false;
				else
					return true;
			case 'skeleton':
				var bone = qdata.QUAD.skeleton[id].bone;
				for ( var i=0; i < bone.length; i++ ){
					if ( ! bone[i] || ! bone[i].attach )
						continue;
					var loop = $.is_loop_attach(qdata, bone[i].attach.type, bone[i].attach.id);
					if ( loop )
						return true;
				}
				return false;
		} // switch ( type )
		return false;
	}

	$.is_mix_attach = function( qdata, type, id ){
		if ( ! Q.func.is_valid_attach(qdata, type, id) )
			return false;
		switch ( type ){
			case 'slot':
				var slot = qdata.QUAD.slot[id];
				for ( var i=0; i < slot.length; i++ ){
					var mix = $.is_mix_attach(qdata, slot[i].type, slot[i].id);
					if ( mix )
						return true;
				}
				return false;
			case 'animation':
				var time = qdata.QUAD.animation[id].timeline;
				for ( var i=0; i < time.length; i++ ){
					var tv = time[i];
					var mix = 0;
					mix |= tv.matrix_mix;
					mix |= tv.color_mix;
					mix |= tv.keyframe_mix;
					mix |= tv.hitbox_mix;
					if ( mix )
						return true;
				}
				return false;
			case 'skeleton':
				var bone = qdata.QUAD.skeleton[id].bone;
				for ( var i=0; i < bone.length; i++ ){
					if ( ! bone[i] || ! bone[i].attach )
						continue;
					var mix = $.is_mix_attach(qdata, bone[i].attach.type, bone[i].attach.id);
					if ( mix )
						return true;
				}
				return false;
		} // switch ( type )
		return false;
	}

	$.time_attach = function( qdata, type, id ){
		if ( ! Q.func.is_valid_attach(qdata, type, id) )
			return 0;
		switch ( type ){
			case 'slot':
				var slot = qdata.QUAD.slot[id];
				var time = 0;
				for ( var i=0; i < slot.length; i++ ){
					var t = $.time_attach(qdata, slot[i].type, slot[i].id);
					if ( t > time )
						time = t;
				}
				return time;
			case 'animation':
				var anim = qdata.QUAD.animation[id].timeline;
				var time = 0;
				anim.forEach(function(av,ak){
					time += av.time;
				});
				return time;
			case 'skeleton':
				var bone = qdata.QUAD.skeleton[id].bone;
				var time = 0;
				bone.forEach(function(bv,bk){
					if ( ! bv || ! bv.attach )
						return;
					var t = $.time_attach(qdata, bv.attach.type, bv.attach.id);
					if ( t > time )
						time = t;
				});
				return time;
		} // switch ( type )
		return 0;
	}


	$.list_attach = function( qdata, type, id ){
		if ( ! Q.func.is_valid_attach(qdata, type, id) )
			return [];

		switch ( type ){
			case 'slot':
				var slot = qdata.QUAD.slot[id];
				var list = [];
				slot.forEach(function(sv,sk){
					list.push( sv.type +','+ sv.id );
				});
				Q.func.array_clean_dups(list);
				return list;
			case 'animation':
				var anim = qdata.QUAD.animation[id].timeline;
				var list = [];
				anim.forEach(function(tv,tk){
					if ( ! tv || ! tv.attach )
						return;
					list.push( tv.attach.type +','+ tv.attach.id );
				});
				Q.func.array_clean_dups(list);
				return list;
			case 'skeleton':
				var bone = qdata.QUAD.skeleton[id].bone;
				var list = [];
				bone.forEach(function(bv,bk){
					if ( ! bv || ! bv.attach )
						return;
					list.push( bv.attach.type +','+ bv.attach.id );
				});
				Q.func.array_clean_dups(list);
				return list;
		} // switch ( type )
		return [];
	}

	//////////////////////////////

	__.bak = {};
	__.backup = function( qdata, canvas, type, id, fps, zoom ){
		__.bak = {
			type  : qdata.attach.type ,
			id    : qdata.attach.id   ,
			fps   : qdata.anim_fps    ,
			zoom  : qdata.zoom        ,
			line  : qdata.is_lines    ,
			hit   : qdata.is_hits     ,
			canvw : canvas.width  ,
			canvh : canvas.height ,
			fname : qdata.name + '_' + type + '_' + id + '_' + fps,
		};
		qdata.attach.type = type;
		qdata.attach.id   = id;
		qdata.anim_fps    = fps;
		qdata.zoom        = zoom;
		qdata.is_lines    = false;
		qdata.is_hits     = false;
	}

	__.restore = function( qdata, canvas ){
		qdata.attach.type = __.bak.type;
		qdata.attach.id   = __.bak.id;
		qdata.anim_fps    = __.bak.fps;
		qdata.zoom        = __.bak.zoom;
		qdata.is_lines    = __.bak.line;
		qdata.is_hits     = __.bak.hit;
		canvas.width      = __.bak.canvw;
		canvas.height     = __.bak.canvh;
	}

	__.download = function( fname, dataurl ){
		if ( ! fname || ! dataurl )
			return;

		var a = document.createElement('a');
		a.href = dataurl;
		a.setAttribute('download', fname);
		a.setAttribute('target'  , '_blank');
		a.click();
	}

	//////////////////////////////

	__.export_sheet = function( qdata, canvas ){
		var line_spacing = 1.15;
		var sprsize = $.rect_attach(qdata, qdata.attach.type, qdata.attach.id);
		var sprwh = [
			(sprsize[2] - sprsize[0]) * line_spacing * qdata.zoom,
			(sprsize[3] - sprsize[1]) * line_spacing * qdata.zoom ,
		];
		var sprmid = [
			(sprsize[2] + sprsize[0]) * 0.5 * qdata.zoom ,
			(sprsize[3] + sprsize[1]) * 0.5 * qdata.zoom ,
		];

		var anim_time = $.time_attach(qdata, qdata.attach.type, qdata.attach.id);
		var texsize = Q.gl.max_texsize();

		var anim_remain = anim_time - qdata.anim_fps;
		var tilecol = 1;
		var tilerow = 1;
		var tile = [
			Math.floor(texsize / sprwh[0]),
			Math.floor(texsize / sprwh[1]),
		];
		if ( tile[0] >= anim_remain )
			tilecol = anim_remain;
		else {
			tilecol = tile[0];
			var y = Math.ceil(anim_remain / tile[0]);
			tilerow = ( y < tile[1] ) ? y : tile[1];
		}
		canvas.width  = tilecol * Math.floor(sprwh[0]);
		canvas.height = tilerow * Math.floor(sprwh[1]);

		// canvas from -halftex to +halftex
		// sprite 0,0 is at center
		var halfpos = [
			canvas.width * 0.5 , canvas.height * 0.5 ,
			sprwh[0]     * 0.5 , sprwh[1]      * 0.5 ,
		];

		var camera = Q.math.matrix4();
		var color  = [1,1,1,1];
		Q.func.qdata_clear(qdata);

		camera[0+0] = ( qdata.is_flipx ) ? -qdata.zoom : qdata.zoom;
		camera[4+1] = ( qdata.is_flipy ) ? -qdata.zoom : qdata.zoom;

		// from -1.0 to +1.0
		for ( var dy = -halfpos[1]; dy < halfpos[1]; dy += sprwh[1] ){
			if ( qdata.anim_fps >= anim_time )
				continue;

			// from -1.0 to +1.0
			for ( var dx = -halfpos[0]; dx < halfpos[0]; dx += sprwh[0] ){
				if ( qdata.anim_fps >= anim_time )
					continue;

				var m4 = Q.math.matrix4();
				m4[0+3] = dx + halfpos[2] - sprmid[0];
				m4[4+3] = dy + halfpos[3] - sprmid[1];
				m4 = Q.math.matrix_multi44(camera, m4);

				qdata.is_draw = false;
				Q.func.qdata_draw(qdata, m4, color);
				qdata.anim_fps++;
			} // for ( var dx = -canvpos[0]; dx < canvpos[0]; dx += sprwh[0] )
		} // for ( var dy = canvpos[1]; dy > -canvpos[1]; dy += sprwh[1] )

		return canvas.toDataURL('image/png');
	}

	__.export_zip = function( qdata, canvas, fmt ){
		var sprsize = $.rect_attach(qdata, qdata.attach.type, qdata.attach.id);
		var symm = Q.math.rect_symmetry(sprsize);

		var line_spacing = 1.15;
		canvas.width  = Math.ceil(symm[0] * 2 * line_spacing * qdata.zoom);
		canvas.height = Math.ceil(symm[1] * 2 * line_spacing * qdata.zoom);

		// same number of sprites as sheet
		var anim_time = $.time_attach(qdata, qdata.attach.type, qdata.attach.id);
		var texsize = Q.gl.max_texsize();
		var len  = Math.floor(texsize / canvas.width) * Math.floor(texsize / canvas.height);
		var list = {};

		var camera = Q.math.matrix4();
		var color  = [1,1,1,1];

		camera[0+0] = ( qdata.is_flipx ) ? -qdata.zoom : qdata.zoom;
		camera[4+1] = ( qdata.is_flipy ) ? -qdata.zoom : qdata.zoom;

		var i = 0;
		while ( i < len ){
			if ( qdata.anim_fps >= anim_time )
				break;
			Q.func.qdata_clear(qdata);
			Q.func.qdata_draw (qdata, camera, color);

			switch ( fmt ){
				case 'png':
					// %06d.png
					var pad = '00000000' + qdata.anim_fps + '.png';
					var fn  = pad.substring( pad.length - 10 );

					list[fn] = QUAD.binary.from_base64( canvas.toDataURL('image/png') );
					break;
				case 'rgba':
					// %06d.rgba
					var pad = '00000000' + qdata.anim_fps + '.rgba';
					var fn  = pad.substring( pad.length - 11 );

					list[fn] = Q.gl.read_RGBA();
					break;
			} // switch ( fmt )

			qdata.anim_fps++;
			i++;
		} // while ( i < len )

		var uint8 = Q.binary.zipwrite(list);
		return 'data:application/zip;base64,' + Q.binary.to_base64(uint8);
	}

	//////////////////////////////

	$.export = function( fmt, qdata, canvas, type, id, fps, zoom ){
		if ( zoom < 0.1 || zoom > 10.0 )
			return;
		var start = performance.now();
		__.backup(qdata, canvas, type, id, fps, zoom);

		switch ( fmt ){
			case 'png':
				var fname   = __.bak.fname + '.png';
				var dataurl = __.export_sheet(qdata, canvas);
				__.download(fname, dataurl);
				break;
			case 'zip':
				var fname   = __.bak.fname + '.png.zip';
				var dataurl = __.export_zip(qdata, canvas, 'png');
				__.download(fname, dataurl);
				break;
			case 'rgba':
				var fname   = __.bak.fname + '.rgba.zip';
				var dataurl = __.export_zip(qdata, canvas, 'rgba');
				__.download(fname, dataurl);
				break;
		} // switch ( fmt )

		__.restore(qdata, canvas);
		Q.func.log('QUAD export' , fmt , 'time' , performance.now()-start);

		// performance
		//   png    1193 ms   1,606,494 byte
		//   zip    4893 ms   3,332,087 byte
		//   rgba  62134 ms  59,437,722 byte
	}

	//////////////////////////////

} // function QuadExport
