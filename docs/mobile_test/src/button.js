'use strict';

(function(){
	var MAIN = document.getElementById('main');

	['px','mm'].forEach(function(unit){
		[70, 60, 50, 40, 30, 20, 10].forEach(function(size){

			var div = document.createElement('div');
			div.style.width      = size + unit;
			div.style.height     = size + unit;
			div.style.background = '#800';
			div.style.margin     = '1em';
			div.innerHTML        = size + unit;
			MAIN.appendChild(div);

		});
	});
})();
