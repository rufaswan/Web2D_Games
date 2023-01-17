'use strict';

function Uint8Base64( uint ){
	var token = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	var BIT6  = 0x3f; // 6-bits = 0011 1111

	var len = uint.byteLength;
	var pos = 0;
	var b64 = '';
	while ( len >= 3 )
	{
		// MSB to LSB
		// 24-bits == 3 * 8-bits
		//         == 4 * 6-bits
		var b = (uint[pos+0] << 16) | (uint[pos+1] << 8) | uint[pos+2];
			len -= 3;
			pos += 3;

		var b1 = (b >> 18) & BIT6;
		var b2 = (b >> 12) & BIT6;
		var b3 = (b >>  6) & BIT6;
		var b4 = (b >>  0) & BIT6;
		b64 += token[b1] + token[b2] + token[b3] + token[b4];
	} // while ( len >= 3 )

	if ( len == 2 )
	{
		// 18-bits == 2 * 8-bits + 2 padding
		//         == 3 * 6-bits
		var b = (uint[pos+0] << 8) | uint[pos+1];
			b <<= 2;

		var b1 = (b >> 12) & BIT6;
		var b2 = (b >>  6) & BIT6;
		var b3 = (b >>  0) & BIT6;
		b64 += token[b1] + token[b2] + token[b3] + '=';
	}
	if ( len == 1 )
	{
		// 12-bits == 1 * 8-bits + 4 padding
		//         == 2 * 6-bits
		var b = uint[pos+0];
			b <<= 4;

		var b1 = (b >> 6) & BIT6;
		var b2 = (b >> 0) & BIT6;
		b64 += token[b1] + token[b2] + '==';
	}
	// no need len == 0
	return b64;
}

// base64 'abcd' == 'YWJjZA=='
//console.log('Uint8Base64', Uint8Base64( new Uint8Array([0x61,0x62,0x63,0x64]) ));
