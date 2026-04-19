'use strict';

// insert JS for local HTML
// JS is skipped when packing to EPUB
document.querySelectorAll('img').forEach(function(img){
	// https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement
	img.alt     = img.src;
	img.loading = 'lazy';
	img.style.maxHeight = '240px';
	img.style.maxWidth  = '100%';

	// https://developer.mozilla.org/en-US/docs/Web/API/HTMLAnchorElement
	var a = document.createElement('a');
	a.href   = img.src;
	a.target = '_blank';

	img.parentNode.style.textAlign = 'center';
	img.parentNode.insertBefore(a, img);
	a.appendChild(img);
});
