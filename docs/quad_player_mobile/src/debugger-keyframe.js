'use strict';

function get_html_id(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id   = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

function qdata_filetable( qdata, files ){
	files.innerHTML = '';
	if ( qdata.name )
		files.innerHTML += '<li>[QUAD] ' + qdata.name + '</li>';
	for ( var i=0; i < qdata.image.length; i++ ){
		if ( ! qdata.image[i] || ! qdata.image[i].name )
			continue;
		var img = qdata.image[i];
		files.innerHTML += '<li>[IMAGE][' + i + '] ' + img.name + ' (' + JSON.stringify(img.pos) + ')</li>';
	}
}

//////////////////////////////
// TODO : remove all global var

function keyframe_select( key_id ){
	__.HTML.layerdata.style.display      = 'block';
	__.HTML.btn_selectall.style.display  = 'block';
	__.HTML.btn_selectnone.style.display = 'block';
	__.HTML.layerlist.innerHTML  = '';
	__.HTML.layer_name.innerHTML = '';

	var key = QuadList[0].quad.keyframe[key_id];
	if ( ! key )
		return;
	__.HTML.layer_name.innerHTML = key.name;
	__.ON_KEY   = key_id;
	__.ON_LAYER = [];
	QuadList[0].attach.id = key_id;

	var buffer  = '';
	var dbglist = [];
	key.layer.forEach(function(v,k){
		if ( ! v )
			return;
		__.ON_LAYER.push(k);

		var dbg = '#' + v.debug.replace(/[^a-zA-Z0-9,]/g, '_');
		if ( dbglist.indexOf(dbg) < 0 )
			dbglist.push(dbg);

		var name = 'layer ' + k + ' (' + dbg + ')';
		buffer += '<li class="layer_on" data-id="' + k + '" data-debug="' + dbg + '"><p onclick="layer_select(this);">' + name + '</p></li>';
	});
	__.HTML.layerlist.innerHTML = buffer;

	dbglist.sort();
	dbglist.unshift(0);
	var buffer  = '';
	dbglist.forEach(function(v,k){
		if ( ! v )
			buffer += '<option value="0">ALL</option>';
		else
			buffer += '<option>' + v + '</option>';
	});
	__.HTML.debuglist.innerHTML = buffer;

	__.AUTOZOOM  = QUAD.func.viewer_autozoom(QuadList[0]);
	__.IS_REDRAW = true;
}

function layer_select( elem ){
	var layer_id = elem.parentElement.getAttribute('data-id') | 0;
	var idx = __.ON_LAYER.indexOf(layer_id);
	if ( idx === -1 )
		__.ON_LAYER.push(layer_id);
	else
		__.ON_LAYER.splice(idx, 1);

	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id  = list[i].getAttribute('data-id') | 0;
		var idx = __.ON_LAYER.indexOf(id);
		if ( idx === -1 )
			list[i].classList.remove('layer_on');
		else
			list[i].classList.add('layer_on');
	}
	__.IS_REDRAW = true;
}

function layer_close(){
	__.HTML.layerdata.style.display      = 'none';
	__.HTML.btn_selectall.style.display  = 'none';
	__.HTML.btn_selectnone.style.display = 'none';
}

function button_select_layers( text ){
	__.ON_LAYER = [];
	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id = list[i].getAttribute('data-id') | 0;
		if ( ! text ){ // select all
			list[i].classList.add('layer_on');
			__.ON_LAYER.push(id);
		}
		else { // select matched
			var debug = list[i].getAttribute('data-debug');
			if ( debug === text ){
				list[i].classList.add('layer_on');
				__.ON_LAYER.push(id);
			}
			else { // unmatched are unchanged
				if ( list[i].classList.contains('layer_on') )
					__.ON_LAYER.push(id);
			}
		}
	} // for ( var i=0; i < list.length; i++ )
}

function button_unselect_layers( text ){
	__.ON_LAYER = [];
	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id = list[i].getAttribute('data-id') | 0;
		if ( ! text ){ // unselect all
			list[i].classList.remove('layer_on');
		}
		else { // unselect matched
			var debug = list[i].getAttribute('data-debug');
			if ( debug === text ){
				list[i].classList.remove('layer_on');
			}
			else { // unmatched are unchanged
				if ( list[i].classList.contains('layer_on') )
					__.ON_LAYER.push(id);
			}
		}
	} // for ( var i=0; i < list.length; i++ )
}

function keydebug_draw( qdata, mat4, color ){
	var key = qdata.quad.keyframe[__.ON_KEY];
	if ( ! key )
		return;
	if ( qdata.is_lines )
		return keydebug_drawline(qdata, key, mat4, color);
	else
		return keydebug_drawtex (qdata, key, mat4, color);
}

function keydebug_drawline( qdata, key, mat4, color ){
	var clines = [];

	var debug = [];
	var dbg_id;
	key.layer.forEach(function(lv,lk){
		if ( ! lv )
			return;
		if ( __.ON_LAYER.indexOf(lk) < 0 )
			return;

		dbg_id = debug.indexOf(lv.debug);
		if ( dbg_id < 0 ){
			dbg_id = debug.length;
			debug.push(lv.debug);
			clines[dbg_id] = [];
		}

		var dst = QUAD.math.quad_multi4(mat4, lv.dstquad);
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

	QUAD.gl.enable_blend(0);
	clines.forEach(function(cv,ck){
		var cid = qdata.line_index % color.length;
		QUAD.gl.draw_line(cv, color[cid]);
		qdata.line_index++;
	});
}

function keydebug_drawtex( qdata, key, mat4, color ){
	var dummysrc = [0,0 , 0,0 , 0,0 , 0,0];

	var zrate = 1.0 / (key.layer.length + 1);
	var buf_list = [];
	var depth = 1.0;
	//console.log('key.order',key.order);

	// draw layers by keyframe order
	key.order.forEach(function(ov){
		var lv = key.layer[ov];
		if ( ! lv )
			return;
		if ( __.ON_LAYER.indexOf(ov) < 0 )
			return;
		depth -= zrate;

		var bid = lv.blend_id | 0;
		if ( ! buf_list[bid] )
			buf_list[bid] = { dst:[] , src:[] , fog:[] , z:[] };
		var ent = buf_list[bid];

		if ( lv.tex_id < 0 || ! qdata.image[lv.tex_id] )
			var src = dummysrc;
		else
			var src = QUAD.math.vram_srcquad(lv.srcquad, qdata.image[lv.tex_id].pos);
		ent.src = ent.src.concat(src);

		var dst = QUAD.math.quad_multi4(mat4, lv.dstquad);
		var xyz = QUAD.math.perspective_quad(dst);
		ent.dst = ent.dst.concat(xyz);

		var clr = QUAD.math.fog_multi4(color, lv.fogquad);
		ent.fog = ent.fog.concat(clr);

		ent.z = ent.z.concat([depth , depth , depth , depth]);
	});
	//console.log('buf_list',buf_list);

	QUAD.gl.enable_depth('LESS');
	buf_list.forEach(function(bv,bk){
		if ( ! bv || ! qdata.quad.blend[bk] )
			return;
		QUAD.gl.enable_blend ( qdata.quad.blend[bk] );
		QUAD.gl.draw_keyframe( bv.dst, bv.src, bv.fog, bv.z, qdata.vram );
	});
	QUAD.gl.enable_depth(0);
}
