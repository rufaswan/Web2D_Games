'use strict';

function playaudio( elem )
{
	//console.log(elem);
	for ( var i=0; i < PLIST.childNodes.length; i++ )
		PLIST.childNodes[i].classList.remove('current');

	document.title = '[AUDIO] ' + elem.innerHTML;
	elem.classList.add('current');

	PLAYER.src = elem.getAttribute('data-src');
	PLAYER.play();
}

function PackListAddAudio( list )
{
	//console.log(list);
	var key = Object.keys(list).sort();
	for ( var i=0; i < key.length; i++ )
	{
		var fn  = key[i];
		var ext = fn.toLowerCase().split('.').pop();

		var dat = '';
		switch ( ext )
		{
			case 'ogg':  dat = 'data:audio/ogg;base64,'; break;
			case 'm4a':  dat = 'data:audio/mp4;base64,'; break;
			case 'aac':  dat = 'data:audio/aac;base64,'; break;
			case 'wav':  dat = 'data:audio/wav;base64,'; break;
			case 'mp3':  dat = 'data:audio/mpeg;base64,'; break;

			default:  continue;
		} // switch ( ext )

		listAddAudio(fn, dat + Uint8Base64( list[fn] ), i);
	} // for ( var i=0; i < key.length; i++ )
}

function listAddAudio( fname, fdata, index )
{
	var li = document.createElement('li');

	li.innerHTML = fname;
	li.setAttribute('class'    , 'psrc');
	li.setAttribute('data-src' , fdata);
	li.setAttribute('data-idx' , index);
	li.setAttribute('onclick', 'playaudio(this);');

	PLIST.appendChild(li);
}

document.getElementById('pfile').addEventListener('change', function(e){
	var elem = this;
	elem.disabled = true;
	PLIST.innerHTML = '';

	var promises = [];
	for ( var up of this.files )
	{
		console.log(up.type, up.name);
		var ext = up.name.toLowerCase().split('.').pop();
		switch ( ext ){
			case 'ogg':
			case 'm4a':
			case 'aac':
			case 'wav':
			case 'mp3':
				var p = Promise.resolve(up).then(function(res){
					console.log('readAsDataURL', ext);
					return new Promise(function(ok,err){
						var reader = new FileReader;
						reader.onload = function(){
							ok( [res, reader.result] );
						}
						reader.onerror = err;
						reader.readAsDataURL(res);
					});
				}).then(function(res){
					return listAddAudio(res[0].name, res[1], 0);
				});
				promises.push(p);
				break;

			case 'zip':
			case 'iso':
				var p = Promise.resolve( [ext,up] ).then(function(res){
					console.log('readAsArrayBuffer', ext);
					return new Promise(function(ok,err){
						var reader = new FileReader;
						reader.onload = function(){
							ok( [res[0], new Uint8Array(reader.result)] );
						}
						reader.onerror = err;
						reader.readAsArrayBuffer(res[1]);
					});
				}).then(function(res){
					var list = [];
					if ( res[0] === 'zip' )  list = ziplist( res[1] );
					if ( res[0] === 'iso' )  list = isolist( res[1] );
					if ( list.length < 1 )
						return 0;
					return PackListAddAudio(list);
				});
				promises.push(p);
				break;
		} // switch ( ext )
	} // for ( var up of this.files )

	Promise.all(promises).then(function(res){
		console.log('Promise.all().then()', res);
		elem.disabled = false;
	});
});
