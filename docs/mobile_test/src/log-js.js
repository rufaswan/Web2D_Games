'use strict';

(function(){
	var DIV = document.createElement('div');
	DIV.classList.add('section');

	function logJS( name, test ){
		var p = document.createElement('p');
		p.innerHTML = 'checking for "' + name + '" support ... ';
		if ( test )
			p.innerHTML += '<span class="ok">[OK]</span>';
		else
			p.innerHTML += '<span class="err">[ERROR]</span>';
		DIV.appendChild(p);
		return;
	}

	var e;

	e = document.createElement('canvas').getContext('webgl');
	logJS('CANVAS.getContext("webgl")', e);

	e = ( window['Promise'] !== undefined );
	logJS('new Promise', e);

	e = ( window['FileReader'] !== undefined );
	logJS('new FileReader', e);

	e = ( window['Promise']['all'] !== undefined );
	logJS('Promise.all()', e);

	e = ( window['JSON']['parse'] !== undefined );
	logJS('JSON.parse()', e);

	e = ( window['CSS']['supports'] !== undefined );
	logJS('CSS.supports()', e);

	e = ( window['atob'] !== undefined );
	logJS('base64 atob()/btoa()', e);

	//e = ( window['WebAssembly']['validate'] !== undefined );
	//logJS('WebAssembly.validate()', e);

	DOM_MAIN.appendChild(DIV);
})();
