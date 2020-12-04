<?php
/*
[license]
[/license]
 */
require "common.inc";

function xeno_meta0( &$file, $pos )
{
	// sub_8001d968
	while (1)
	{
		$op = ord( $file[$pos] );
			$pos++;
		printf("%2x  ", $op);
		break;

		if ( $op >= 0x80 )
		{
			$op -= 0x80;
			switch ( $op )
			{
				case 0x00:
					break;
				case 0x01:
					break;
				case 0x02:
					break;
				case 0x03:
					break;
				case 0x04:
					break;
				case 0x05:
					break;
				case 0x06:
					break;
				case 0x07:
					break;
				case 0x08:
					break;
				case 0x09:
					break;
				case 0x0a:
					break;
				case 0x0b:
					break;
				case 0x0c:
					break;
				case 0x0d:
					break;
				case 0x0e:
					break;
				case 0x0f:
					break;
				case 0x10:
					break;
				case 0x11:
					break;
				case 0x12:
					break;
				case 0x13:
					break;
				case 0x14:
					break;
				case 0x15:
					break;
				case 0x16:
					break;
				case 0x17:
					break;
				case 0x18:
					break;
				case 0x19:
					break;
				case 0x1a:
					break;
				case 0x1b:
					break;
				case 0x1c:
					break;
				case 0x1d:
					break;
				case 0x1e:
					break;
				case 0x1f:
					break;
				case 0x20:
					break;
				case 0x21:
					break;
				case 0x22:
					break;
				case 0x23:
					break;
				case 0x24:
					break;
				case 0x25:
					break;
				case 0x26:
					break;
				case 0x27:
					break;
				case 0x28:
					break;
				case 0x29:
					break;
				case 0x2a:
					break;
				case 0x2b:
					break;
				case 0x2c:
					break;
				case 0x2d:
					break;
				case 0x2e:
					break;
				case 0x2f:
					break;
				case 0x30:
					break;
				case 0x31:
					break;
				case 0x32:
					break;
				case 0x33:
					break;
				case 0x34:
					break;
				case 0x35:
					break;
				case 0x36:
					break;
				case 0x37:
					break;
				case 0x38:
					break;
				case 0x39:
					break;
				case 0x3a:
					break;
				case 0x3b:
					break;
				case 0x3c:
					break;
				case 0x3d:
					break;
				case 0x3e:
					break;
				case 0x3f:
					break;
				case 0x40:
					break;
				case 0x41:
					break;
				case 0x42:
					break;
				case 0x43:
					break;
				case 0x44:
					break;
				case 0x45:
					break;
				case 0x46:
					break;
				case 0x47:
					break;
				case 0x48:
					break;
				case 0x49:
					break;
				case 0x4a:
					break;
				case 0x4b:
					break;
				case 0x4c:
					break;
				case 0x4d:
					break;
				case 0x4e:
					break;
				case 0x4f:
					break;
				case 0x50:
					break;
				case 0x51:
					break;
				case 0x52:
					break;
				case 0x53:
					break;
				case 0x54:
					break;
				case 0x55:
					break;
				case 0x56:
					break;
				case 0x57:
					break;
				case 0x58:
					break;
				case 0x59:
					break;
				case 0x5a:
					break;
				case 0x5b:
					break;
				case 0x5c:
					break;
				case 0x5d:
					break;
				case 0x5e:
					break;
				case 0x5f:
					break;
				case 0x60:
					break;
				case 0x61:
					break;
				case 0x62:
					break;
				case 0x63:
					break;
				case 0x64:
					break;
				case 0x65:
					break;
				case 0x66:
					break;
				case 0x67:
					break;
				case 0x68:
					break;
				case 0x69:
					break;
				case 0x6a:
					break;
				case 0x6b:
					break;
				case 0x6c:
					break;
				case 0x6d:
					break;
				case 0x6e:
					break;
				case 0x6f:
					break;
				case 0x70:
					break;
				case 0x71:
					break;
				case 0x72:
					break;
				case 0x73:
					break;
				case 0x74:
					break;
				case 0x75:
					break;
				case 0x76:
					break;
				case 0x77:
					break;
				case 0x78:
					break;
				case 0x79:
					break;
				case 0x7a:
					break;
				case 0x7b:
					break;
				case 0x7c:
					break;
				case 0x7d:
					break;
				case 0x7e:
					break;
				case 0x7f:
					break;
			} // switch ( $op )
		}

		if ( $op >= 0x10 )
		{
			if ( $op >= 0x20 )
			{
				if ( $op >= 0x30 )
				{
					return;
				}
			}
		}

		echo "\n";
	} // while (1)
	echo "\n";

	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$cnt = str2int($file, 0, 2);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 2 + ($i * 2);
		$p1 = str2int($file, $p, 2);
		$p2 = str2int($file, $p1+2, 2);
		$p3 = $p1 + 2 + $p2;

		printf("== $fname/$i , %x , %x , %x , %x\n", $p, $p1, $p2, $p3);
		xeno_meta0($file, $p3);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
