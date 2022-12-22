<canvas id='canvas'>Canvas not supported</canvas>
<div id='corner0' class='clickable'>A</div>
<div id='corner1' class='clickable'>B</div>
<div id='corner2' class='clickable'>C</div>
<div id='corner3' class='clickable'>D</div>

<script>
QDFN.setWebGLCanvasById('canvas');
var DST = [-1,1 , 1,1 , 1,-1 , -1,-1];
var SRC = [ 0,0 , 1,0 , 1, 1 ,  0, 1];

function divLeftTop( div, left, top )
{
	div.style.left = (left|0) + 'px';
	div.style.top  = (top |0) + 'px';
	return;
}

function initCorners()
{
	var rect = QDFN.getBoundingClientRect();
	var box;
	//console.log(rect);

	box = document.getElementById('corner0');
	divLeftTop(box, rect.left, rect.top);

	box = document.getElementById('corner1');
	divLeftTop(box, rect.right, rect.top);

	box = document.getElementById('corner2');
	divLeftTop(box, rect.right, rect.bottom);

	box = document.getElementById('corner3');
	divLeftTop(box, rect.left, rect.bottom);
	return;
};

function getDstCorner()
{
	var box = QDFN.getBoundingClientRect();
	var hw  = box.width  * 0.5;
	var hh  = box.height * 0.5;
	DST = [];
	for ( var i=0; i < 4; i++ )
	{
		var cnr = document.getElementById('corner'+i).getBoundingClientRect();
		var x = cnr.left - box.left - hw;
		var y = cnr.top  - box.top  - hh;
		DST.push( x, y );
	}
}

initCorners();
getDstCorner();

var IS_CLICK = true;
var CORNER = undefined;
var CLICK  = document.getElementsByClassName('clickable');
window.addEventListener('click', function(e){
	function xyRect( x, y, rect )
	{
		if ( x < rect.left   )  return false;
		if ( y < rect.top    )  return false;
		if ( x > rect.right  )  return false;
		if ( y > rect.bottom )  return false;
		return true;
	}

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
		IS_CLICK = true;
		return;
	}
});
</script>
