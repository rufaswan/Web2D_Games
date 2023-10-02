function BinaryReader(){
	var $ = this;
	//var m = {};

	//////////////////////////////

	$.uint2txt = function( buf ){
		buf = new Uint8Array(buf);
		return $.getstr(buf, 0, buf.byteLength);
	}

	$.catUint8 = function( a, b ){
		var c = new Uint8Array( a.byteLength + b.byteLength );
		c.set(a, 0);
		c.set(b, a.byteLength);
		return c;
	}

	//////////////////////////////

	$.getint = function( buf, pos, len ){
		var int = 0;
		for ( var i=0; i < len; i++ )
			int |= (buf[pos+i] << (i*8));
		return int;
	}

	$.getstr = function( buf, pos, len ){
		var str = '';
		for ( var i=0; i < len; i++ )
			str += String.fromCharCode( buf[pos+i] );
		return str;
	}

	$.getsub = function( buf, pos, len ){
		var sub = new Uint8Array(len);
		for ( var i=0; i < len; i++ )
			sub[i] = buf[pos+i];
		return sub;
	}

	$.zipread = function( zipbuf ){
		zipbuf = new Uint8Array(zipbuf)
		var list = {};
		var pos  = 0;
		while (1)
		{
			var mgc = $.getint(zipbuf, pos, 4);
			if ( mgc !== 0x04034b50 )  // PK34
				break;

			var sz1 = $.getint(zipbuf, pos + 0x12, 4); // data size
			var sz2 = $.getint(zipbuf, pos + 0x1a, 2); // filename length
			var sz3 = $.getint(zipbuf, pos + 0x1c, 2); // extra length

			// uncompressed file/store only
			if ( zipbuf[pos + 8] === 0 ){
				var fn = $.getstr(zipbuf, pos + 0x1e            , sz2);
				var dt = $.getsub(zipbuf, pos + 0x1e + sz2 + sz3, sz1);
				list[fn] = dt;
			}

			pos += (0x1e + sz1 + sz2 + sz3);
		} // while (1)
		return list;
	}

	//////////////////////////////

	$.setint = function( buf, pos, len, int ){
		for ( var i=0; i < len; i++ ){
			var b = int >> (i*8);
			buf[pos+i] = b & 0xff;
		}
	}

	$.setstr = function( buf, pos, str ){
		for ( var i=0; i < str.length; i++ )
			buf[pos+i] = str.charCodeAt(i);
	}

	$.setsub = function( buf, pos, sub ){
		for ( var i=0; i < sub.byteLength; i++ )
			buf[pos+i] = sub[i];
	}

	$.zipwrite = function( list ){
		var key = Object.keys(list);
		var pk34len = 0;
		var pk12len = 0;
		var pk56len = 0x16;

		var fnlen, dtlen, dtcrc;
		for ( var i=0; i < key.length; i++ ){
			fnlen = key[i].length;
			dtlen = list[ key[i] ].byteLength;
			pk34len += (0x1e + fnlen + dtlen);
			pk12len += (0x2e + fnlen);
		} // for ( var i=0; i < key.length; i++ ){
		var zipbuf = new Uint8Array( pk34len + pk12len + pk56len );

		var pos34 = 0;
		var pos12 = pk34len;
		var pos56 = pk34len + pk12len;

		for ( var i=0; i < key.length; i++ ){
			fnlen = key[i].length;
			dtlen = list[ key[i] ].byteLength;
			dtcrc = $.crc32( list[ key[i] ] );

			$.setint(zipbuf , pos12 , 4 , 0x02014b50);  // PK12
			zipbuf[ pos12 + 0x04 ] = 10;  // ver 1.0
			zipbuf[ pos12 + 0x06 ] = 10;  // ver 1.0
			$.setsub(zipbuf , pos12 + 0x10 , dtcrc);
			$.setint(zipbuf , pos12 + 0x14 , 4 , dtlen);  // compressed
			$.setint(zipbuf , pos12 + 0x18 , 4 , dtlen);  // uncompressed
			$.setint(zipbuf , pos12 + 0x1c , 2 , fnlen);
			$.setint(zipbuf , pos12 + 0x2a , 4 , pos34);
				pos12 += 0x2e;
			$.setstr(zipbuf , pos12 , key[i]);
				pos12 += fnlen;

			$.setint(zipbuf , pos34 , 4 , 0x04034b50);  // PK34
			zipbuf[ pos34 + 0x04 ] = 10;  // ver 1.0
			$.setsub(zipbuf , pos34 + 0x0e , dtcrc);
			$.setint(zipbuf , pos34 + 0x12 , 4 , dtlen);  // compressed
			$.setint(zipbuf , pos34 + 0x16 , 4 , dtlen);  // uncompressed
			$.setint(zipbuf , pos34 + 0x1a , 2 , fnlen);
				pos34 += 0x1e;
			$.setstr(zipbuf , pos34 , key[i]);
				pos34 += fnlen;
			$.setsub(zipbuf , pos34 , list[ key[i] ]);
				pos34 += dtlen;
		} // for ( var i=0; i < key.length; i++ ){

		$.setint(zipbuf , pos56 , 4, 0x06054b50);  // PK56
		$.setint(zipbuf , pos56 + 0x08 , 2, key.length);  // disk entry
		$.setint(zipbuf , pos56 + 0x0a , 2, key.length);  // total entry
		$.setint(zipbuf , pos56 + 0x0c , 4, pk12len);  // PK12 length
		$.setint(zipbuf , pos56 + 0x10 , 4, pk34len);  // PK12 pos
		return zipbuf;
	}

	//////////////////////////////

	$.crc32 = function( uint8 ){
		uint8 = new Uint8Array(uint8);
		var crc = new Uint16Array([255,255,255,255]);

		// https://stackoverflow.com/questions/21001659/crc32-algorithm-implementation-in-c-without-a-look-up-table-and-with-a-public-li
		// https://web.archive.org/web/20190108202303/http://www.hackersdelight.org/hdcodetxt/crc.c.txt
		// https://web.archive.org/web/20190716204559/http://www.hackersdelight.org/permissions.htm
		function shift(){
			var carry = 0;
			[3,2,1,0].forEach(function(e){
				crc[e] |= (carry << 8);
				carry = crc[e] & 1;
				crc[e] >>= 1;
			});
			return carry;
		}
		function xor( int ){
			var b;
			[0,1,2,3].forEach(function(e){
				b = (int >> (e*8)) & 0xff;
				crc[e] ^= b;
			});
		}

		var len = uint8.byteLength;
		for ( var i=0; i < len; i++ ){
			xor( uint8[i] );
			for ( var j=0; j < 8; j++ ){
				var mask = shift();
				if ( mask )
					xor( 0xedb88320 );
			}
		}
		xor( 0xffffffff );
		return new Uint8Array(crc);
	}

	//////////////////////////////

	$.toBase64 = function( uint8 ){
		uint8 = new Uint8Array(uint8);
		var token = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		var BIT6  = 0x3f;
		var len = uint8.byteLength;
		var pos = 0;

		var b, b1, b2, b3, b4;
		var b64 = '';
		while ( len >= 3 ){
			b = (uint8[pos+0] << 16) | (uint8[pos+1] << 8) | uint8[pos+2];
				pos += 3;
				len -= 3;

			b1 = (b >> 18) & BIT6;
			b2 = (b >> 12) & BIT6;
			b3 = (b >>  6) & BIT6;
			b4 = (b >>  0) & BIT6;
			b64 += token[b1] + token[b2] + token[b3] + token[b4];
		} // while ( len >= 3 ){

		if ( len === 2 ){
			b = (uint8[pos+0] << 8) | uint8[pos+1];
				b <<= 2

			b1 = (b >> 12) & BIT6;
			b2 = (b >>  6) & BIT6;
			b3 = (b >>  0) & BIT6;
			b64 += token[b1] + token[b2] + token[b3] + '=';
		}
		if ( len === 1 ){
			b = uint8[pos+0];
				b <<= 4;

			b1 = (b >>  6) & BIT6;
			b2 = (b >>  0) & BIT6;
			b64 += token[b1] + token[b2] + '==';
		}
		return b64;
	}

	$.fromBase64 = function( b64 ){
		// data URL handling
		var pos = b64.indexOf('base64,');
		if ( pos !== -1 )
			b64 = b64.substring(pos + 7);

		// must be length % 4 === 0
		if ( (b64.length & 3) !== 0 )
			return '';

		// Uint8Array.reserve()
		var declen = 0;
		for ( var pos=0; pos < b64.length; pos += 4 ){
			if ( b64[pos+2] === '=' )
				declen += 1;
			else
			if ( b64[pos+3] === '=' )
				declen += 2;
			else
				declen += 3;
		} // for ( var pos=0; pos < b64.length; pos += 4 )
		var uint8 = new Uint8Array(declen);

		var token = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		var BIT8  = 0xff;

		var b, b1, b2, b3, b4;
		var dpos = 0;
		for ( var pos=0; pos < b64.length; pos += 4 ){
			b1 = token.indexOf( b64[pos+0] );
			b2 = token.indexOf( b64[pos+1] );

			if ( b64[pos+2] === '=' ){
				b = (b1 << 6) | b2;
					b >>= 4;
				uint8[dpos+0] = (b >> 0) & BIT8;
					dpos += 1;
				continue;
			}

			b3 = token.indexOf( b64[pos+2] );
			if ( b64[pos+3] === '=' ){
				b = (b1 << 12) | (b2 << 6) | b3;
					b >>= 2;
				uint8[dpos+0] = (b >> 8) & BIT8;
				uint8[dpos+1] = (b >> 0) & BIT8;
					dpos += 2;
				continue;
			}

			b4 = token.indexOf( b64[pos+3] );
			b = (b1 << 18) | (b2 << 12) | (b3 << 6) | b4;

			uint8[dpos+0] = (b >> 16) & BIT8;
			uint8[dpos+1] = (b >>  8) & BIT8;
			uint8[dpos+2] = (b >>  0) & BIT8;
				dpos += 3;
		} // for ( var pos=0; pos < b64.length; pos += 4 ){
		return uint8;
	}

	//////////////////////////////

} // function BinaryReader
