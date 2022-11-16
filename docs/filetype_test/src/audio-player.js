'use strict';

function playaudio( elem )
{
	//console.log(elem);
	document.title = '[AUDIO] ' + elem.innerHTML;
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
		var p1 = new Promise(function(resolve,reject){
			switch ( ext )
			{
				case 'ogg':
				case 'm4a':
				case 'aac':
				case 'wav':
				case 'mp3':
					var reader = new FileReader;
					reader.onload = function(){
						listAddAudio(up.name, reader.result, 0);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsDataURL(up);
					break;

				case 'zip':
					var reader = new FileReader;
					reader.onload = function(){
						var list = ziplist( new Uint8Array(reader.result) );
						PackListAddAudio(list);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsArrayBuffer(up);
					break;

				case 'iso':
					var reader = new FileReader;
					reader.onload = function(){
						var list = isolist( new Uint8Array(reader.result) );
						PackListAddAudio(list);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsArrayBuffer(up);
					break;

				default:
					resolve(-1);
					break;
			} // switch ( ext )
		});
		promises.push(p1);
	} // for ( var up of this.files )

	Promise.all(promises).then(function(resolve){
		elem.disabled = false;
		console.log('promise then', resolve);
	}).catch(function(reject){
		elem.disabled = false;
		console.log('promise catch', reject);
	});
});
