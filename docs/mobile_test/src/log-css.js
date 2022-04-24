'use strict';

(function(){
	var DIV = document.createElement('div');
	DIV.classList.add('section');

	function logCSS( key, val ){
		var p = document.createElement('p');
		p.innerHTML = 'CSS "' + key + ' : ' + val + '" support ... ';
		if ( CSS.supports(key, val) )
			p.innerHTML += '<span class="ok">[OK]</span>';
		else
			p.innerHTML += '<span class="err">[ERROR]</span>';
		DIV.appendChild(p);
		return;
	}

	logCSS('display', 'flex');
	//logCSS('display', 'contents');
	//logCSS('display', 'grid');
	logCSS('width'  , '1vw');
	logCSS('width'  , '1em');
	logCSS('width'  , '1rem');
	logCSS('width'  , 'calc(50% - 200px)');

	DOM_MAIN.appendChild(DIV);
})();
