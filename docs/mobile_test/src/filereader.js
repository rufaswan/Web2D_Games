'use strict';

(function(){
	var DOM_READER = document.getElementById('filereader');

	document.getElementById('upload').addEventListener('change', function(e){
		var elem = this;
		elem.disabled = true;

		var promises = [];
		for ( var up of this.files )
		{
			console.log(up.type, up.name);
			var p1 = new Promise(function(resolve, reject){
				if ( up.type === 'text/plain' || up.type === 'application/json' )
				{
					var reader = new FileReader;
					reader.onload = function(){
						var txt = reader.result;
						try {
							var json = JSON.parse(txt);
							console.log(json);
						} catch(e){
						}

						var tag = document.createElement('p');
						tag.innerHTML = txt;
						DOM_READER.appendChild(tag);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsText(up);
				}
				else
				if ( up.type === 'image/png' )
				{
					var reader = new FileReader;
					reader.onload = function(){
						var img = new Image;
						img.onload = function(){
							// img.width
							// img.height
						}
						img.src = reader.result;
						DOM_READER.appendChild(img);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsDataURL(up);
				}
				else
					resolve(-1);
			});
			promises.push(p1);
		} // for ( var up of this.files )

		Promise.all(promises).then(function(result){
			elem.disabled = false;
			console.log('Promise all then', result);
		}).catch(function(reason){
			elem.disabled = false;
			console.log('Promise all catch', reason);
		});
	});
})();
