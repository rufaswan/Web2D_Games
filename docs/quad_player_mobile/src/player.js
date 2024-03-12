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

function display_viewer( html, toggle ){
	if ( toggle ){
		html.viewer.style.display   = 'block';
		//html.debugger.style.display = 'none';
	}
	else {
		html.viewer.style.display   = 'none';
		//html.debugger.style.display = 'block';
	}
	html.export_div.style.display = 'none';
}

function btn_toggle( elem, turn ){
	var css = elem.classList;
	if ( turn > 0 ){
		css.remove('btn_off');
		css.add('btn_on');
		return;
	}
	if ( turn < 0 ){
		css.remove('btn_on');
		css.add('btn_off');
		return;
	}
	css.remove('btn_on');
	css.remove('btn_off');
}

function btn_prev_next( qdata, adj ){
	adj = adj | 0;
	if ( ! qdata || adj === 0 )
		return;
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		qdata.attach.id += adj;
	else
		qdata.anim_fps  += adj;
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
		if ( quad[tv].length < 1 )
			continue;
		return tv;
	}
	return -1;
}

function qdata_listing( qdata, type, id ){
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
	if ( SELECTED )
		SELECTED.classList.remove('current');

	SELECTED = elem;
	SELECTED.classList.add('current');

	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;

	qdata_attach(QuadList[0], type, id);
	display_viewer(HTML, true);
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

	var div = HTML.export_div;
	div.setAttribute('data-type', type);
	div.setAttribute('data-id'  , id);
	div.style.display = 'block';

	HTML.export_name.innerHTML = type + ' , ' + id;

	var qdata = QuadList[0];
	var time  = QUAD.export.time_attach(qdata, type, id);
	var range = HTML.export_range;
	range.setAttribute('max', time - 1); // index 0
	range.value = 0;
	HTML.export_start.innerHTML = 0;
}

function button_export_type( elem ){
	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;
	var fmt  = elem.innerHTML.toLowerCase();

	var time = HTML.export_start.innerHTML | 0;
	var zoom = 1.0 * HTML.export_zoom.innerHTML;
	QUAD.export.export(fmt, QuadList[0], HTML.canvas, type, id, time, zoom);
}

/*
		<p id='viewer_top_row_1'>
			<button id='btn_debug'>debug</button>
			<button id='btn_option'>options</button>
			<button id='btn_lines'>line</button>
		</p>
		<p id='viewer_top_row_2'>
			<button id='btn_autofit' class='btn_on'>zoom</button>
			<button id='btn_flipx' class='btn_off'>X</button>
			<button id='btn_flipy' class='btn_off'>Y</button>
		</p>

	html.viewer_top_row_2.style.display = 'none';

	HTML.btn_option.addEventListener('click', function(){
		var row2 = HTML.viewer_top_row_2;
		if ( row2.style.display === 'flex' )
			row2.style.display = 'none';
		else
			row2.style.display = 'flex';
	});
	HTML.btn_autofit.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_autofit.classList.contains('btn_on') ){
			btn_toggle(HTML.btn_autofit, -1);
			IS_AUTOZOOM = false;
			QuadList[0].zoom = 1;
		} else {
			btn_toggle(HTML.btn_autofit, 1);
			IS_AUTOZOOM = true;
			QuadList[0].zoom = -1;
		}
	});
	HTML.btn_flipy.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_flipy.classList.contains('btn_on') ){
			btn_toggle(HTML.btn_flipy, -1);
			QuadList[0].is_flipy = false;
		} else {
			btn_toggle(HTML.btn_flipy, 1);
			QuadList[0].is_flipy = true;
		}
	});
*/
