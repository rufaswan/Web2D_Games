		canvas.toBlob(function(blob){
			a.href = window.URL.createObjectURL(blob);
			a.setAttribute('download', fn);
			a.click();
		}, 'image/png')

		if ( image === undefined )
			return;

			//if ( QUAD.files.image[texid] === undefined )
				//srcquad = [0,0 , 1,0 , 1,1 , 0,1];
