'use strict';

(function(){
	var DIV = document.createElement('div');
	DIV.classList.add('section');

	var scr_w = window.innerWidth;
	var scr_h = window.innerHeight;
	DIV.innerHTML += '<p>screen size = '+ scr_w +' x '+ scr_h +'</p>';

	var scr_sz = scr_w * scr_h;
	if ( scr_sz > (16384 * 8640) )  DIV.innerHTML += '<p>16K UHD compatible</p>';
	if ( scr_sz > ( 8192 * 4320) )  DIV.innerHTML += '<p>8K UHD compatible</p>';
	if ( scr_sz > ( 4096 * 2160) )  DIV.innerHTML += '<p>4K UHD compatible</p>';
	if ( scr_sz > ( 2048 * 1080) )  DIV.innerHTML += '<p>2K UHD compatible</p>';
	if ( scr_sz > ( 1024 *  540) )  DIV.innerHTML += '<p>UHD compatible</p>';
	if ( scr_sz > (  512 *  270) )  DIV.innerHTML += '<p>qUHD compatible</p>';
	if ( scr_sz > (  256 *  135) )  DIV.innerHTML += '<p>qqUHD compatible</p>';

	DOM_MAIN.appendChild(DIV);
})();

