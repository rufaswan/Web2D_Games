'use strict';

document.getElementById('pfile').addEventListener('change', function(e){
	var elem = this;
	elem.disabled = true;

	var promises = [];
	for ( var up of this.files )
	{
		var p = Promise.resolve(up).then(function(res){
			console.log('p = Promise.resolve().then()', res);
			return new Promise(function(ok,err){
				var reader = new FileReader;
				reader.onload = function(){
					ok( [res, reader.result] );
				};
				reader.onerror = err;
				reader.readAsArrayBuffer(res);
			});
		}).then(function(res){
			console.log('p.then()', res);
			//var hex = dec.toString(16);
			//var dec = parseInt(hex, 16);
			var hex = res[1].byteLength.toString(16);
			var li = document.createElement('li');

			li.innerHTML = res[0].type + ' , ' + hex + ' , ' + res[0].name;
			PLIST.appendChild(li);
			return 0;
		});
		promises.push(p);
	} // for ( var up of this.files )

	Promise.all(promises).then(function(){
		console.log('Promise.all().then()');
		elem.disabled = false;
	});
});
