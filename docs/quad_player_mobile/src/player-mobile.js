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

function display_viewer( html, toggle ){
	if ( toggle )
		html.viewer.style.display = 'block';
	else
		html.viewer.style.display = 'none';
	html.export_menu.style.display = 'none';
}

function button_toggle( elem, turn ){
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

function button_prev_next( qdata, adj ){
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

function qdata_toggle( qdata, elem, key ){
	var t = elem.classList.contains('btn_on');
	if ( qdata )
		t |= qdata[key];

	if ( t ) // if ON, turn OFF
		button_toggle(elem, -1);
	else // if OFF, turn ON
		button_toggle(elem, 1);

	if ( qdata )
		qdata[key] = !t;
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

function qdata_tagtable( tag ){
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

function qdata_attach( qdata, type, id ){
	qdata.attach.type = type;
	qdata.attach.id   = id;
	qdata.anim_fps    = 0;
}

function quad_mainlist( quad ){
	var type = ['skeleton','animation','slot','keyframe','hitbox'];
	for ( var i=0; i < type.length; i++ ){
		var tv = type[i];
		if ( quad[tv].length > 0 )
			return tv;
	}
	return -1;
}

function qdata_listing( qdata, type, id ){
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
			html += qdata_listing(qdata, cv[0], cv[1]);
		});
		html += '</ul>';
	}

	html += '</li>';
	return html;
}
//////////////////////////////
// TODO : remove all global var

function button_select( elem ){
	if ( __.SELECTED )
		__.SELECTED.classList.remove('current');

	__.SELECTED = elem;
	__.SELECTED.classList.add('current');

	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;

	qdata_attach(QuadList[0], type, id);
	display_viewer(__.HTML, true);
	__.IS_REDRAW = true;
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

	var div = __.HTML.export_menu;
	div.setAttribute('data-type', type);
	div.setAttribute('data-id'  , id);
	div.style.display = 'block';

	__.HTML.export_name.innerHTML = type + ' , ' + id;

	var qdata = QuadList[0];
	var time  = QUAD.export.time_attach(qdata, type, id);
	var range = __.HTML.export_range;
	range.setAttribute('max', time - 1); // index 0
	range.value = 0;
	__.HTML.export_start.innerHTML = 0;
}

function button_export_type( elem ){
	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;
	var fmt  = elem.innerHTML.toLowerCase();

	var time = __.HTML.export_start.innerHTML | 0;
	var zoom = 1.0 * __.HTML.export_zoom.innerHTML;
	QUAD.export.export(fmt, QuadList[0], __.HTML.canvas, type, id, time, zoom);
}

function viewer_btn_menu( qdata ){
	__.HTML.btn_hitattr.style.display = 'none';
	__.HTML.hitattr_list.innerHTML = '';
	if ( qdata.quad.hitbox.length > 0 )
		__.HTML.btn_hitattr.style.display = 'block';

	__.HTML.btn_keyattr.style.display = 'none';
	__.HTML.keyattr_list.innerHTML = '';
	if ( qdata.quad.__ATTR.keyframe.length > 0 ){
		qdata.keyattr = -1;
		__.HTML.btn_keyattr.style.display = 'block';
		var buffer = '';
		qdata.quad.__ATTR.keyframe.forEach(function(ev,ek){
			var mask = 1 << ek;
			buffer += '<button class="btn_on" onclick="qdata_attr(this,\'keyattr\',' + mask + ');">' + ev + '</button>';
		});
		__.HTML.keyattr_list.innerHTML = buffer;
		QUAD.func.log('keyframe attr', qdata.quad.__ATTR.keyframe);
	}

	__.HTML.btn_colorize.style.display = 'none';
	__.HTML.colorize_list.innerHTML = '';
	if ( qdata.quad.__ATTR.colorize.length > 0 ){
		qdata.colorize = [];
		__.HTML.btn_colorize.style.display = 'block';
		var buffer = '';
		qdata.quad.__ATTR.colorize.forEach(function(cv,ck){
			var mask = 1 << ck;
			qdata.colorize[mask] = [1,1,1,1];
			buffer += cv + ' = <input type="color" value="#ffffff" onchange="qdata_colorize(this,' + mask + ');">&nbsp;';
		});
		__.HTML.colorize_list.innerHTML = buffer;
		QUAD.func.log('colorize attr', qdata.quad.__ATTR.colorize);
	}
}

function qdata_attr( elem, name, mask ){
	if ( elem.classList.contains('btn_on') ){
		button_toggle(elem, -1);
		QuadList[0][name] &= ~mask;
	}
	else {
		button_toggle(elem, 1);
		QuadList[0][name] |= mask;
	}
	__.IS_REDRAW = true;
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
	QuadList[0].colorize[id] = rgb;
	__.IS_REDRAW = true;
}
