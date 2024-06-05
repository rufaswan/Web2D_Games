<canvas id='canvas'>Canvas not supported</canvas>
<div id='corner0' class='clickable'>A</div>
<div id='corner1' class='clickable'>B</div>
<div id='corner2' class='clickable'>C</div>
<div id='corner3' class='clickable'>D</div>

<script>
QDFN.set_webgl_by_id('canvas');
var APP = {};
APP.dst = [-1,1 , 1,1 , 1,-1 , -1,-1];
APP.src = [ 0,0 , 1,0 , 1, 1 ,  0, 1];

function div_left_top( div, left, top ){
	div.style.left = (left|0) + 'px';
	div.style.top  = (top |0) + 'px';
	return;
}

function init_corners(){
	var rect = QDFN.get_bounding_rect();
	var box;
	//console.log(rect);

	box = document.getElementById('corner0');
	div_left_top(box, rect.left, rect.top);

	box = document.getElementById('corner1');
	div_left_top(box, rect.right, rect.top);

	box = document.getElementById('corner2');
	div_left_top(box, rect.right, rect.bottom);

	box = document.getElementById('corner3');
	div_left_top(box, rect.left, rect.bottom);
	return;
};

function get_dst_corner(){
	var box = QDFN.get_bounding_rect();
	var hw  = box.width  * 0.5;
	var hh  = box.height * 0.5;
	APP.dst = [];
	for ( var i=0; i < 4; i++ ){
		var cnr = document.getElementById('corner'+i).getBoundingClientRect();
		var x = cnr.left - box.left - hw;
		var y = cnr.top  - box.top  - hh;
		APP.dst.push( x, y );
	}
}

init_corners();
get_dst_corner();

APP.is_click  = true;
APP.corner    = 0;
APP.clickable = document.getElementsByClassName('clickable');
window.addEventListener('click', function(e){
	function xy_rect( x, y, rect ){
		if ( x < rect.left   )  return false;
		if ( y < rect.top    )  return false;
		if ( x > rect.right  )  return false;
		if ( y > rect.bottom )  return false;
		return true;
	}

	if ( APP.corner === 0 ){
		for ( var i=0; i < APP.clickable.length; i++ )
		{
			var rect = APP.clickable[i].getBoundingClientRect();
			if ( xy_rect(e.pageX, e.pageY, rect) )
			{
				APP.corner = APP.clickable[i];
				APP.corner.classList.add('activebox');
				return;
			}
		}
		return;
	}
	else {
		div_left_top(APP.corner, e.pageX, e.pageY);
		APP.corner.classList.remove('activebox');
		APP.corner = 0;
		APP.is_click = true;
		return;
	}
});
</script>
