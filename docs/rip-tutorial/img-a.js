'use strict';
document.querySelectorAll('img').forEach(function(img){
	img.setAttribute('alt'   , img.src);
	img.setAttribute('height', 240);

	var a = document.createElement('a');
	a.setAttribute('href'  , img.src);
	a.setAttribute('target', '_blank');

	img.parentNode.insertBefore(a, img);
	a.appendChild(img);
});
