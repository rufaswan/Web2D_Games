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
		html.debugger.style.display = 'none';
	}
	else {
		html.viewer.style.display   = 'none';
		html.debugger.style.display = 'block';
	}
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

function qdata_tagtable( tag, parent ){
	if ( ! tag )
		return '';
	parent.innerHTML = '<h2>tag</h2>';

	function wikilink( tagkey, tagval ){
		if ( tagkey.toLowerCase() === 'comment' || tagval === '-' )
			return tagval;
		var href = tagval.replace(/ /g, '_');
		return '<a href="https://en.m.wikipedia.org/wiki/' +href+ '" target="_blank">' +tagval+ '</a>';
	}

	var table = '<table id="quad_data_tags">';
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

		table += '<tr><td><p>' + t.l + '</p></td><td><p>' + t.r + '</p></td></tr>';
	});
	table += '</table>';
	parent.innerHTML += table;
}

function qdata_attach( qdata, type, id ){
	qdata.attach.type = type;
	qdata.attach.id   = id;
	qdata.anim_fps    = 0;
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
