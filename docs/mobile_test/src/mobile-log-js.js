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

	e = ( window.Promise !== undefined );
	logJS('new Promise', e);

	e = ( window.FileReader !== undefined );
	logJS('new FileReader', e);

	e = ( window.Promise.all !== undefined );
	logJS('Promise.all()', e);

	e = ( window.JSON.parse !== undefined );
	logJS('JSON.parse()', e);

	e = ( window.CSS.supports !== undefined );
	logJS('CSS.supports()', e);

	//   mousedown    mousemove    mouseup   mouseuout
	//  touchstart    touchmove   touchend           -
	// pointerdown  pointermove  pointerup  pointerout
	e = ( window.onmousedown !== undefined );
	logJS('addEventListener("mousedown")', e);

	e = ( window.ontouchstart !== undefined );
	logJS('addEventListener("touchstart")', e);

	e = ( window.onpointerdown !== undefined );
	logJS('addEventListener("pointerdown")', e);

	e = ( window.atob !== undefined );
	logJS('base64 atob()/btoa()', e);

	// https://stackoverflow.com/questions/36312150/mousedown-event-not-firing-on-tablet-mobile-html5-canvas
	// mouse          touch
	// event.clientX  (event.targetTouches[0] ? event.targetTouches[0].pageX : event.changedTouches[event.changedTouches.length-1].pageX)
	// event.clientY  (event.targetTouches[0] ? event.targetTouches[0].pageY : event.changedTouches[event.changedTouches.length-1].pageY)

	//e = ( window.WebAssembly.validate !== undefined );
	//logJS('WebAssembly.validate()', e);

	e = ( navigator.mediaDevices.getUserMedia !== undefined );
	logJS('navigator.mediaDevices.getUserMedia', e);

	DOM_MAIN.appendChild(DIV);
})();
