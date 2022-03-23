'use strict';

(function(){
	var DIV = document.createElement('div');
	DIV.classList.add('section');

	var p = document.createElement('p');
	p.innerHTML = navigator.userAgent;
	DIV.appendChild(p);

	DOM_MAIN.appendChild(DIV);
})();
