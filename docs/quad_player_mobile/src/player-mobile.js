'use strict';

var APP = {};

APP.get_html_id = function(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id   = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

APP.display_viewer = function( html, toggle ){
	if ( toggle )
		html.viewer.style.display = 'block';
	else
		html.viewer.style.display = 'none';
	html.export_menu.style.display = 'none';
}

APP.bgcontrast = function( light ){
	light &= 0xff;
	var dark  = 0xff - light;
	var root  = document.documentElement;
	root.style.setProperty('--body-bg-color'   , `rgb(${light},${light},${light})`);
	root.style.setProperty('--body-text-color' , `rgb(${dark} ,${dark} ,${dark} )`);
	if ( light & 0x80 ){
		root.style.setProperty('--button-bg-color'   , '#121212');
		root.style.setProperty('--button-text-color' , '#ededed');
	}
	else {
		root.style.setProperty('--button-bg-color'   , '#ededed');
		root.style.setProperty('--button-text-color' , '#121212');
	}
}

APP.divrange_span_value = function( elem ){
	var span = elem.parentElement.getElementsByTagName('span');
	span[0].innerHTML = elem.value;
}

APP.button_toggle = function( elem, turn ){
	if ( turn > 0 ){ // +1 = turn ON
		elem.classList.remove('btn_off');
		elem.classList.add('btn_on');
		return;
	}
	if ( turn < 0 ){ // -1 = turn OFF
		elem.classList.remove('btn_on');
		elem.classList.add('btn_off');
		return;
	}
	// 0 = not a ON/OFF button
	elem.classList.remove('btn_on');
	elem.classList.remove('btn_off');
}

APP.button_prev_next = function( qdata, adj ){
	adj = adj | 0;
	if ( ! qdata || adj === 0 )
		return;
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		qdata.attach.id += adj;
	else
		qdata.anim_fps  += adj;
}

function button_close( elem ){
	var par2 = elem.parentElement.parentElement;
	par2.style.display = 'none';
}

APP.qdata_toggle = function( qdata, elem, key ){
	var t = elem.classList.contains('btn_on');
	if ( qdata )
		t |= qdata[key];

	if ( t ) // if ON, turn OFF
		APP.button_toggle(elem, -1);
	else // if OFF, turn ON
		APP.button_toggle(elem, 1);

	if ( qdata )
		qdata[key] = !t;
}

APP.qdata_filetable = function( qdata, files ){
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

APP.qdata_tagtable = function( tag ){
	if ( ! tag )
		return '';
	function wikilink( tagkey, tagval ){
		if ( tagkey.toLowerCase() === 'comment' || tagval === '-' )
			return tagval;
		var href = tagval.replace(/ /g, '_');
		return '<a href="https://en.m.wikipedia.org/wiki/' + href + '" target="_blank">' + tagval + '</a>';
	}

	var buffer = '<h2>tag</h2>';
	buffer += '<div id="quad_data_tags">';

	var keys = Object.keys(tag);
	keys.forEach(function(k){
		var t = {};
		t.l = k;
		if ( Array.isArray(tag[k]) ){
			t.v = [];
			tag[k].forEach(function(tv){
				t.v.push( wikilink( k, tv ) );
			});
			t.r = t.v.join(' , ');
		}
		else {
			t.r = wikilink( k, tag[k] );
		}

		buffer += '<h3>' + t.l + '</h3>';
		buffer += '<p>' + t.r + '</p>';
	});

	buffer += '</div>';
	return buffer;
}

APP.qdata_attach = function( qdata, type, id ){
	qdata.attach.type = type;
	qdata.attach.id   = id;
	qdata.anim_fps    = 0;
}

APP.quad_mainlist = function( quad ){
	var type = ['skeleton','animation','slot','keyframe','hitbox'];
	for ( var i=0; i < type.length; i++ ){
		var tv = type[i];
		if ( quad[tv].length > 0 )
			return tv;
	}
	return -1;
}

APP.qdata_listing = function( qdata, type, id ){
	if ( ! qdata.quad[type] || ! qdata.quad[type][id] )
		return '';
	var qchild = QUAD.export.list_attach(qdata, type, id);
	var qtype  = qdata.quad[type][id];

	var qname  = qtype.name || type + ' ' + id;
	if ( QUAD.export.is_loop_attach(qdata, type, id) )
		qname += ' <strong>[LOOP]</strong>';
	if ( QUAD.export.is_mix_attach(qdata, type, id) )
		qname += ' <strong>[MIX]</strong>';

	var html = '<li data-type="' + type + '" data-id="' + id + '"><p>';
	if ( qchild.length > 0 )
		html += '<span class="liexpand" onclick="button_expand(this);">+</span>';

	html += '<span class="liname" onclick="button_select(this);">' + qname + '</span>';

	if ( ['skeleton','animation'].indexOf(type) !== -1 )
		html += '<span class="liexport" onclick="button_export(this);">export</span>';
	html += '</p>';

	if ( qchild.length > 0 ){
		html += '<ul style="display:none;">';
		qchild.forEach(function(cv,ck){
			cv = cv.split(',');
			html += APP.qdata_listing(qdata, cv[0], cv[1]);
		});
		html += '</ul>';
	}

	html += '</li>';
	return html;
}
//////////////////////////////
// function aaa()       + onclick='aaa();'
// APP.aaa = function() + var a = APP.aaa();

function button_select( elem ){
	if ( APP.selected )
		APP.selected.classList.remove('current');

	APP.selected = elem;
	APP.selected.classList.add('current');

	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;

	APP.qdata_attach(APP.QuadList[0], type, id);
	APP.display_viewer(APP.html, true);
	APP.is_redraw = true;
}

function button_expand( elem ){
	var par2  = elem.parentElement.parentElement;
	var ulist = par2.getElementsByTagName('ul');
	if ( ulist.length < 1 )
		return;

	var dis = ulist[0].style.display;
	if ( dis === 'block' )
		ulist[0].style.display = 'none';
	else
		ulist[0].style.display = 'block';
}

function button_export( elem ){
	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;

	var div = APP.html.export_menu;
	div.setAttribute('data-type', type);
	div.setAttribute('data-id'  , id);
	div.style.display = 'block';

	APP.html.export_name.innerHTML = type + ' , ' + id;

	var qdata = APP.QuadList[0];
	var time  = QUAD.export.time_attach(qdata, type, id);
	var range = APP.html.export_range;
	range.setAttribute('max', time - 1); // index 0
	range.value = 0;

	APP.divrange_span_value(APP.html.export_range);
	APP.divrange_span_value(APP.html.export_times);
}

function button_export_type( elem ){
	var fmt  = elem.innerHTML.toLowerCase();

	var div  = APP.html.export_menu;
	var type = div.getAttribute('data-type');
	var id   = div.getAttribute('data-id') | 0;

	var time = APP.html.export_range.value | 0;
	var zoom = APP.html.export_times.value * 1.0;
	QUAD.export.export(fmt, APP.QuadList[0], APP.html.canvas, type, id, time, zoom);
	APP.html.logger.innerHTML = QUAD.func.console();
}

APP.viewer_btn_menu = function( qdata ){
	APP.html.btn_hitattr.style.display = 'none';
	APP.html.hitattr_list.innerHTML = '';
	if ( qdata.quad.hitbox.length > 0 )
		APP.html.btn_hitattr.style.display = 'block';

	APP.html.btn_keyattr.style.display = 'none';
	APP.html.keyattr_list.innerHTML = '';
	if ( qdata.quad.__ATTR.keyframe.length > 0 ){
		qdata.keyattr = -1;
		APP.html.btn_keyattr.style.display = 'block';
		var buffer = '';
		qdata.quad.__ATTR.keyframe.forEach(function(ev,ek){
			var mask = 1 << ek;
			buffer += '<button class="btn_on" onclick="qdata_attr(this,\'keyattr\',' + mask + ');">' + ev + '</button>';
		});
		APP.html.keyattr_list.innerHTML = buffer;
		QUAD.func.log('keyframe attr', qdata.quad.__ATTR.keyframe);
	}

	APP.html.btn_colorize.style.display = 'none';
	APP.html.colorize_list.innerHTML = '';
	if ( qdata.quad.__ATTR.colorize.length > 0 ){
		qdata.colorize = [];
		APP.html.btn_colorize.style.display = 'block';
		var buffer = '';
		qdata.quad.__ATTR.colorize.forEach(function(cv,ck){
			var mask = 1 << ck;
			qdata.colorize[mask] = [1,1,1,1];
			buffer += cv + ' = <input type="color" value="#ffffff" onchange="qdata_colorize(this,' + mask + ');">&nbsp;';
		});
		APP.html.colorize_list.innerHTML = buffer;
		QUAD.func.log('colorize attr', qdata.quad.__ATTR.colorize);
	}
}

function qdata_attr( elem, name, mask ){
	if ( elem.classList.contains('btn_on') ){
		APP.button_toggle(elem, -1);
		APP.QuadList[0][name] &= ~mask;
	}
	else {
		APP.button_toggle(elem, 1);
		APP.QuadList[0][name] |= mask;
	}
	APP.is_redraw = true;
}

function qdata_colorize( elem, id ){
	var color = elem.value;
	var div   = 1.0 / 255;
	var rgb   = [
		parseInt( color.substring(1,3) , 16 ) * div ,
		parseInt( color.substring(3,5) , 16 ) * div ,
		parseInt( color.substring(5,7) , 16 ) * div ,
		1.0,
	];
	APP.QuadList[0].colorize[id] = rgb;
	APP.is_redraw = true;
}
