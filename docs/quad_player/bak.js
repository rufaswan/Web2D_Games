		canvas.toBlob(function(blob){
			a.href = window.URL.createObjectURL(blob);
			a.setAttribute('download', fn);
			a.click();
		}, 'image/png')
