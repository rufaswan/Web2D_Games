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

	e = ( window.Promise );
	logJS('new Promise', e);

	e = ( window.Promise.all );
	logJS('Promise.all()', e);

	e = ( window.FileReader );
	logJS('new FileReader', e);

	e = ( window.MediaRecorder );
	logJS('new MediaRecorder', e);

	e = ( window.JSON.parse );
	logJS('JSON.parse()', e);

	e = ( window.CSS.supports );
	logJS('CSS.supports()', e);

	//   mousedown    mousemove    mouseup   mouseuout
	//  touchstart    touchmove   touchend           -
	// pointerdown  pointermove  pointerup  pointerout
	e = ( window.onmousedown );
	logJS('addEventListener("mousedown")', e);

	e = ( window.ontouchstart );
	logJS('addEventListener("touchstart")', e);

	e = ( window.onpointerdown );
	logJS('addEventListener("pointerdown")', e);

	e = ( window.atob );
	logJS('base64 atob()/btoa()', e);

	// https://stackoverflow.com/questions/36312150/mousedown-event-not-firing-on-tablet-mobile-html5-canvas
	// mouse          touch
	// event.clientX  (event.targetTouches[0] ? event.targetTouches[0].pageX : event.changedTouches[event.changedTouches.length-1].pageX)
	// event.clientY  (event.targetTouches[0] ? event.targetTouches[0].pageY : event.changedTouches[event.changedTouches.length-1].pageY)

	//e = ( window.WebAssembly.validate !== undefined );
	//logJS('WebAssembly.validate()', e);

	e = ( navigator.mediaDevices.getUserMedia );
	logJS('navigator.mediaDevices.getUserMedia', e);

	DOM_MAIN.appendChild(DIV);
})();
