'use strict';

APP.process_uploads_done = function(){
	var qdata = APP.QuadList[ APP.upload_id ];

	APP.qdata_filetable(qdata, APP.html.debugger_files);
	if ( qdata.name ){
		APP.html.keylist.innerHTML = '';
		document.title = '[' + qdata.name + '] ' + APP.html.quad_version.innerHTML;

		if ( ! qdata.quad.keyframe )
			return;
		qdata.attach.type = 'keyframe';

		var buffer = '';
		qdata.quad.keyframe.forEach(function(v,k){
			if ( ! v )
				return;
			var name = v.name + ' (' + v.debug + ')';
			buffer += '<li><p onclick="keyframe_select(' + k + ');">' + name + '</p></li>';
		});
		APP.html.keylist.innerHTML = buffer;
	} // if ( qdata.name )

	APP.autozoom  = 1.0;
	APP.is_redraw = true;
}

APP.button_select_layers = function( text ){
	APP.on_layer = [];
	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id = list[i].getAttribute('data-id') | 0;
		if ( ! text ){ // select all
			list[i].classList.add('layer_on');
			APP.on_layer.push(id);
		}
		else { // select matched
			var debug = list[i].getAttribute('data-debug');
			if ( debug === text ){
				list[i].classList.add('layer_on');
				APP.on_layer.push(id);
			}
			else { // unmatched are unchanged
				if ( list[i].classList.contains('layer_on') )
					APP.on_layer.push(id);
			}
		}
	} // for ( var i=0; i < list.length; i++ )
}

APP.button_unselect_layers = function( text ){
	APP.on_layer = [];
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
					APP.on_layer.push(id);
			}
		}
	} // for ( var i=0; i < list.length; i++ )
}

//////////////////////////////
// function aaa()       + onclick='aaa();'
// APP.aaa = function() + var a = APP.aaa();

function keyframe_select( key_id ){
	APP.html.layerdata.style.display      = 'block';
	APP.html.btn_selectall.style.display  = 'block';
	APP.html.btn_selectnone.style.display = 'block';
	APP.html.layerlist.innerHTML  = '';
	APP.html.layer_name.innerHTML = '';

	var key = APP.QuadList[0].quad.keyframe[key_id];
	if ( ! key )
		return;
	APP.html.layer_name.innerHTML = key.name;
	APP.on_key   = key_id;
	APP.on_layer = [];
	APP.QuadList[0].attach.id = key_id;

	var buffer  = '';
	var dbglist = [];
	key.layer.forEach(function(v,k){
		if ( ! v )
			return;
		APP.on_layer.push(k);

		var dbg = '#' + v.debug.replace(/[^a-zA-Z0-9,]/g, '_');
		if ( dbglist.indexOf(dbg) < 0 )
			dbglist.push(dbg);

		var name = 'layer ' + k + ' (' + dbg + ')';
		buffer += '<li class="layer_on" data-id="' + k + '" data-debug="' + dbg + '"><p onclick="layer_select(this);">' + name + '</p></li>';
	});
	APP.html.layerlist.innerHTML = buffer;

	dbglist.sort();
	dbglist.unshift(0);
	var buffer  = '';
	dbglist.forEach(function(v,k){
		if ( ! v )
			buffer += '<option value="0">ALL</option>';
		else
			buffer += '<option>' + v + '</option>';
	});
	APP.html.debuglist.innerHTML = buffer;

	APP.autozoom  = QUAD.func.viewer_autozoom(APP.QuadList[0]);
	APP.is_redraw = true;
}

function layer_select( elem ){
	var layer_id = elem.parentElement.getAttribute('data-id') | 0;
	var idx = APP.on_layer.indexOf(layer_id);
	if ( idx === -1 )
		APP.on_layer.push(layer_id);
	else
		APP.on_layer.splice(idx, 1);

	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id  = list[i].getAttribute('data-id') | 0;
		var idx = APP.on_layer.indexOf(id);
		if ( idx === -1 )
			list[i].classList.remove('layer_on');
		else
			list[i].classList.add('layer_on');
	}
	APP.is_redraw = true;
}

function layer_close(){
	APP.html.layerdata.style.display      = 'none';
	APP.html.btn_selectall.style.display  = 'none';
	APP.html.btn_selectnone.style.display = 'none';
}

//////////////////////////////
// hook to overwrite default function with debug one

/*
QUAD.draw.hook('qdata_draw', function( qdata, mat4, color ){
	if ( ! qdata.quad )
		return;
	var m4 = QUAD.math.matrix_multi44( mat4, qdata.matrix );
	var c4 = QUAD.math.vec4_multi(color, qdata.color);
	qdata.attach.type = 'keyframe';
	return __.draw_keyframe(qdata, qdata.attach.id, m4, c4);

});

APP.keydebug_draw =
*/

APP.keydebug_draw = function( qdata, mat4, color ){
	var key = qdata.quad.keyframe[APP.on_key];
	if ( ! key )
		return;
	if ( qdata.is_lines )
		return APP.keydebug_drawline(qdata, key, mat4, color);
	else
		return APP.keydebug_drawtex (qdata, key, mat4, color);
}

APP.keydebug_drawline = function( qdata, key, mat4, color ){
	var clines = [];

	var debug = [];
	var dbg_id;
	key.layer.forEach(function(lv,lk){
		if ( ! lv )
			return;
		if ( APP.on_layer.indexOf(lk) < 0 )
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

APP.keydebug_drawtex = function( qdata, key, mat4, color ){
	var zrate = 1.0 / (key.layer.length + 1);
	var buf_list = {};
	var depth = 1.0;
	//console.log('key.order',key.order);

	// draw layers by keyframe order
	key.order.forEach(function(ov){
		var lv = key.layer[ov];
		if ( ! lv )
			return;
		if ( APP.on_layer.indexOf(ov) < 0 )
			return;
		depth -= zrate;

		var bid = lv.blend_id | 0;
		if ( ! buf_list[bid] )
			buf_list[bid] = { dst:[] , src:[] , fog:[] , z:[] };
		var ent = buf_list[bid];

		var src = QUAD.func.vram_srcquad(lv.srcquad, lv.tex_id, qdata.image);
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
	for ( var i = -1; i < qdata.quad.blend.length; i++ ){
		if ( ! buf_list[i] ) // no data to draw
			continue;

		if ( i < 0 ) // disable blending
			QUAD.gl.enable_blend(0);
		else {
			if ( ! qdata.quad.blend[i] ) // invalid or unknown blending
				continue;
			QUAD.gl.enable_blend( qdata.quad.blend[i] );
		}

		var bv = buf_list[i];
		QUAD.gl.draw_keyframe( bv.dst, bv.src, bv.fog, bv.z, qdata.vram );
	} // for ( var i = -1; i < qdata.quad.blend.length; i++ )
	QUAD.gl.enable_depth(0);
}

