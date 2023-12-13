'use strict';

function getHtmlIds(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id  = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

function displayViewer( html, toggle ){
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

function btnToggle( elem, turn ){
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

function btnPrevNext( qdata, adj ){
	adj = adj | 0;
	if ( ! qdata || adj === 0 )
		return;
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		qdata.attach.id += adj;
	else
		qdata.anim_fps  += adj;
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

function qdata_listing( qdata, type, id, visible=false ){
	var qchild = QUAD.export.listAttach(qdata, type, id, false);
	var qtype  = qdata.QUAD[type][id];

	var qname  = qtype.name || type + ' ' + id;
	if ( QUAD.export.isLoopAttach(qdata, type, id) )
		qname += ' <strong>[LOOP]</strong>';
	if ( QUAD.export.isMixAttach(qdata, type, id) )
		qname += ' <strong>[MIX]</strong>';

	var html = '<li data-type="' + type + '" data-id="' + id + '"><p>';
	if ( qchild.length > 0 )
		html += '<span class="liexpand" onclick="button_expand(this);">+</span>';

	html += '<span class="liname" onclick="button_select(this);">' + qname + '</span>';

	if ( ['skeleton','animation'].indexOf(type) !== -1 )
		html += '<span class="liexport" onclick="button_export(this);">export</span>';
	html += '</p>';

	if ( qchild.length > 0 ){
		if ( visible )
			html += '<ul style="display:block;">';
		else
			html += '<ul style="display:none;">';

		qchild.forEach(function(cv,ck){
			html += qdata_listing(qdata, cv[0], cv[1], false);
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
	displayViewer(HTML, true);
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
	var time  = QUAD.export.timeAttach(qdata, type, id);
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
		m.GL.enable(m.GL.DEPTH_TEST);
		m.GL.depthFunc(m.GL.LESS);

			$.vec_resize(2, c0);
			$.vec_resize(2, c1);
			$.vec_resize(2, c2);
			$.vec_resize(2, c3);
		return [].concat(c0,c1,c2,c3);
*/
