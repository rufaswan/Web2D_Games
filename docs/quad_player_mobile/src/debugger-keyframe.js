'use strict';

function get_html_id(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id  = eles[i].id;
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
	HTML.layerdata.style.display      = 'block';
	HTML.btn_selectall.style.display  = 'block';
	HTML.btn_selectnone.style.display = 'block';
	HTML.layerlist.innerHTML  = '';
	HTML.layer_name.innerHTML = '';

	var key = QuadList[0].quad.keyframe[key_id];
	if ( ! key )
		return;
	HTML.layer_name.innerHTML = key.name;
	ON_KEY   = key_id;
	ON_LAYER = [];
	QuadList[0].attach.id = key_id;

	var buffer = '';
	key.layer.forEach(function(v,k){
		if ( ! v )
			return;
		ON_LAYER.push(k);

		name = 'layer ' + k + ' (' + v.debug + ')';
		buffer += '<li class="layer_on" data-id="' + k + '"><p onclick="layer_select(this);">' + name + '</p></li>';
	});
	HTML.layerlist.innerHTML = buffer;
	IS_REDRAW = true;
}

function layer_select( elem ){
	var layer_id = elem.parentElement.getAttribute('data-id') | 0;
	var idx = ON_LAYER.indexOf(layer_id);
	if ( idx === -1 )
		ON_LAYER.push(layer_id);
	else
		ON_LAYER.splice(idx, 1);

	var list = document.querySelectorAll('#layerlist li');
	for ( var i=0; i < list.length; i++ ){
		var id  = list[i].getAttribute('data-id') | 0;
		var idx = ON_LAYER.indexOf(id);
		if ( idx === -1 )
			list[i].classList.remove('layer_on');
		else
			list[i].classList.add('layer_on');
	}
	IS_REDRAW = true;
}

function layer_close(){
	HTML.layerdata.style.display      = 'none';
	HTML.btn_selectall.style.display  = 'none';
	HTML.btn_selectnone.style.display = 'none';
}

function keydebug_draw( qdata, mat4, color ){
	var key = qdata.quad.keyframe[ON_KEY];
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
	var did, dbg;
	key.layer.forEach(function(lv,lk){
		if ( ! lv )
			return;
		if ( ON_LAYER.indexOf(lk) === -1 )
			return;

		dbg = JSON.stringify(lv.debug);
		did = debug.indexOf(dbg);
		if ( did < 0 ){
			did = debug.length;
			debug.push(dbg);
			clines[did] = [];
		}

		var dst = QUAD.math.quad_multi4(mat4, lv.dstquad);
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
		if ( ON_LAYER.indexOf(ov) === -1 )
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
