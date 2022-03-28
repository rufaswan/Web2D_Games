'use strict';

(function(){
	function divLeftTop( div, left, top )
	{
		div.style.left = (left|0) + 'px';
		div.style.top  = (top |0) + 'px';
		return;
	}

	function xyRect( x, y, rect )
	{
		if ( x < rect.left   )  return false;
		if ( y < rect.top    )  return false;
		if ( x > rect.right  )  return false;
		if ( y > rect.bottom )  return false;
		return true;
	}

	function initCorners()
	{
		var rect = CANVAS.getBoundingClientRect();
		var box;
		//console.log(rect);

		box = document.getElementById('corner0');
		divLeftTop(box, rect.left, rect.top);

		box = document.getElementById('corner2');
		divLeftTop(box, rect.right, rect.top);

		box = document.getElementById('corner4');
		divLeftTop(box, rect.right, rect.bottom);

		box = document.getElementById('corner6');
		divLeftTop(box, rect.left, rect.bottom);
		return;
	};
	initCorners();

	var CORNER = undefined;
	var CLICK  = document.getElementsByClassName('clickable');
	window.addEventListener('click', function(e){
		if ( CORNER === undefined )
		{
			for ( var i=0; i < CLICK.length; i++ )
			{
				var rect = CLICK[i].getBoundingClientRect();
				if ( xyRect(e.pageX, e.pageY, rect) )
				{
					CORNER = CLICK[i];
					CORNER.classList.add('activebox');
					return;
				}
			}
			return;
		}
		else
		{
			divLeftTop(CORNER, e.pageX, e.pageY);
			CORNER.classList.remove('activebox');
			CORNER = undefined;
			return;
		}
	});
})();
