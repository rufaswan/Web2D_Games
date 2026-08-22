'use strict';

function buf2int( buffer, pos, byte )
{
	var int = 0;
	for ( var i=0; i < byte; i++ )
		int |= (buffer[pos+i] << (i*8));
	return int;
}

function buf2str( buffer, pos, byte )
{
	var str = '';
	for ( var i=0; i < byte; i++ )
		str += String.fromCharCode( buffer[pos+i] );
	return str;
}

function bufpart( buffer, pos, byte )
{
	var part = new Uint8Array(byte);
	for ( var i=0; i < byte; i++ )
		part[i] = buffer[pos+i];
	return part;
}

function isolist( uint8 )
{
	// list['fname'] = binary data
	var list = {};

	if ( buf2str(uint8,0x8001,5) !== 'CD001' )
		return list;
	if ( buf2str(uint8,0x8801,5) !== 'CD001' )
		return list;

	function loopdir_lba( lba, siz, par )
	{
		for ( var i=0; i < siz; i += 0x800 )
		{
			var j = 0;
			while ( j < 0x800 )
			{
				var pos = lba + i + j;
				if ( uint8[pos+0] === 0 )
				{
					j += 0x800;
					continue;
				}

				if ( uint8[pos+32] === 1 )
				{
					j += uint8[pos+0];
					continue;
				}

				var flba = buf2int(uint8, pos +  2, 4);
				var fsiz = buf2int(uint8, pos + 10, 4);
				var fnam = buf2str(uint8, pos + 33, uint8[pos+32]);
					flba *= 0x800;
					fnam  = par + '/' + fnam;

				if ( uint8[pos+25] & 2 )
					loopdir_lba(flba, fsiz, fnam);
				else
				{
					fnam = fnam.split(';')[0].toLowerCase().replace(/_/g, ' ');
					list[fnam] = bufpart(uint8, flba, fsiz);
				}

				j += uint8[pos+0];
			} // while ( j < 0x800 )
		} // for ( var i=0; i < siz; i += 0x800 )
	}

	var root_lba = buf2int(uint8, 0x809c +  2, 4);
	var root_siz = buf2int(uint8, 0x809c + 10, 4);
	loopdir_lba(root_lba * 0x800, root_siz, '.');

	return list;
}

function ziplist( uint8 )
{
	// list['fname'] = binary data
	var list = {};
	var pos = 0;
	while (1)
	{
		// PK\x03\x04
		if ( uint8[pos+0] !== 0x50 || uint8[pos+1] !== 0x4b || uint8[pos+2] !== 3 || uint8[pos+3] !== 4 )
			break;

		var sz1 = buf2int(uint8, pos+0x12, 4); // data size
		var sz2 = buf2int(uint8, pos+0x1a, 2); // fname len
		var sz3 = buf2int(uint8, pos+0x1c, 2); // extra len

		// store only
		if ( uint8[pos+8] === 0 )
		{
			var fn = buf2str(uint8, pos + 0x1e, sz2);
			var dt = bufpart(uint8, pos + 0x1e + sz2 + sz3, sz1);
			list[fn] = dt;
		}

		pos += (0x1e + sz1 + sz2 + sz3);
	} // while (1)
	return list;
}
