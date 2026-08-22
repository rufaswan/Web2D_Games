<?php

function meta26_opcode( &$m, $pos )
{
	$loop = -1;
	$time = [];
	$line = [];
	$ret = [];
	while (1)
	{
		if ( isset($line[$pos]) )
		{
			$loop = $line[$pos];
			goto done;
		}

		$op = str2int($m, $pos, 2);
			$pos += 2;
		if ( $op >= 0x8000 )
		{
			switch ( $op )
			{
				case 0x8000: // end
					printf("%8x end  = %x\n", $pos-2, $op);
					goto done;
				case 0x8001:
					printf("%8x op 8000 = %x\n", $pos-2, $op);
					break;
				case 0x8002:  // branch/loop
					printf("%8x jump = %x\n", $pos-2, $op);
					$b = str2int($m, $pos, 2);
					$pos = $b << 1;
					break;
				case 0x8003:  // jump and link
					printf("%8x call = %x\n", $pos-2, $op);
					$b = str2int($m, $pos, 2);
					array_unshift($ret, $pos + 2);
					$pos = $b << 1;
					break;
				case 0x8004:  // return
					printf("%8x return = %x\n", $pos-2, $op);
					if ( empty($ret) )
						return php_error('8000 return empty');
					$pos = array_shift($ret);
					break;

				case 0x8005:  $pos += 4; break;
				case 0x8006:  break;
				case 0x8007:  break;
				case 0x8008:  $pos += 2; break;
				case 0x8009:  $pos += 2; break;
				case 0x800a:  $pos += 2; break;
				case 0x800b:  $pos += 2; break;
				case 0x800c:  $pos += 4; break;
				case 0x800d:  $pos += 2; break;
				case 0x800e:  $pos += 2; break;
				case 0x800f:  $pos += 2; break;

				case 0x8010:  $pos += 0x10; break;
				case 0x8011:  $pos += 0xc ; break;
				case 0x8012:  $pos += 0x10; break;
				case 0x8013:  $pos += 0xe ; break;
				case 0x8014:  break;
				case 0x8015:  $pos += 0xc ; break;
				case 0x8016:  break;
				case 0x8017:  break;
				case 0x8018:  $pos += 2; break;
				case 0x8019:  $pos += 2; break;
				case 0x801a:  break;
				case 0x801b:  $pos += 2; break;
				case 0x801c:  $pos += 4; break;
				case 0x801d:  $pos += 4; break;
				case 0x801e:  $pos += 2; break;
				case 0x801f:  $pos += 2; break;

				case 0x8020:  $pos += 2; break;
				case 0x8021:  $pos += 2; break;
				case 0x8022:  $pos += 2; break;
				case 0x8023:  $pos += 2; break;
				case 0x8024:  $pos += 2; break;
				case 0x8025:  $pos += 2; break;
				case 0x8026:  $pos += 2; break;
				case 0x8027:  $pos += 2; break;
				case 0x8028:  $pos += 2; break;
				case 0x8029:  $pos += 2; break;
				case 0x802a:  break;
				case 0x802b:  $pos += 2; break;
				case 0x802c:  $pos += 2; break;
				case 0x802d:  $pos += 2; break;
				case 0x802e:  $pos += 2; break;
				case 0x802f:  $pos += 2; break;

				case 0x8030:  $pos += 8; break;
				case 0x8031:  $pos += 0xa; break;
				case 0x8032:  $pos += 0xa; break;
				case 0x8033:  break;
				case 0x8034:  $pos += 8; break;
				case 0x8035:  break;
				case 0x8036:  break;
				case 0x8037:  break;
				case 0x8038:  $pos += 0xc; break;
				case 0x8039:  $pos += 0xe; break;
				case 0x803a:  break;
				case 0x803b:  break;
				case 0x803c:  break;
				case 0x803d:  break;
				case 0x803e:  break;
				case 0x803f:  break;

				case 0x8040:  break;
				case 0x8041:  break;
				case 0x8042:  break;
				case 0x8043:  break;
				case 0x8044:  $pos += 2; break;
				case 0x8045:  $pos += 2; break;
				case 0x8046:  break;
				case 0x8047:  $pos += 4; break;
				case 0x8048:  $pos += 2; break;
				case 0x8049:  $pos += 2; break;
				case 0x804a:  $pos += 2; break;
				case 0x804b:  $pos += 2; break;
				case 0x804c:  $pos += 2; break;
				case 0x804d:  $pos += 2; break;
				case 0x804e:  $pos += 2; break;
				case 0x804f:  $pos += 2; break;

				case 0x8050:  $pos += 2; break;
				case 0x8051:  $pos += 10; break;
				case 0x8052:  $pos += 2; break;
				case 0x8053:  break;
				case 0x8054:  break;
				case 0x8055:  $pos -= 2; break; // loop last frame
				case 0x8056:  $pos += 2; break;
				case 0x8057:  $pos += 2; break;
				case 0x8058:  $pos += 2; break;
				case 0x8059:  $pos += 2; break;
				case 0x805a:  break;
				case 0x805b:  break;
				case 0x805c:  break;
				case 0x805d:  break;
				case 0x805e:  break;
				case 0x805f:  break;
			} // switch ( $op )
		}
		else
		if ( $op >= 0x6000 )
		{
			printf("%8x op 6000 = %x\n", $pos-2, $op);
			$op -= 0x6000;
			$b1 = str2int($m, $pos+0, 2);
			$b2 = str2int($m, $pos+2, 2);
			$b3 = str2int($m, $pos+4, 2);
			$b4 = str2int($m, $pos+6, 2);
			$b5 = str2int($m, $pos+8, 2);
				$pos += 10;
			if ( $b2 >= 0x200 || $b4 >= 0x200 )
				return php_error('%8x skel id >= 200 , %x  %x', $pos-12, $b2, $b6);
			$time[] = [$op,$b1,$b2,$b3,$b4,$b5];
		}
		else
		if ( $op >= 0x5000 )
		{
			printf("%8x op 5000 = %x\n", $pos-2, $op);
			$op -= 0x5000;
			$b1 = str2int($m, $pos+0, 2);
			$b2 = str2int($m, $pos+2, 2);
			$b3 = str2int($m, $pos+4, 2);
			$b4 = str2int($m, $pos+6, 2);
				$pos += 8;
			if ( $b3 >= 0x200 || $b4 >= 0x200 )
				return php_error('%8x skel id >= 200 , %x  %x', $pos-10, $b3, $b4);
			$time[] = [$op,$b1,$b2,$b3,$b4];
		}
		else
		if ( $op >= 0x4000 )
		{
			printf("%8x op 4000 = %x\n", $pos-2, $op);
			$op -= 0x4000;
			$b1 = str2int($m, $pos+0, 2);
			$b2 = str2int($m, $pos+2, 2);
				$pos += 4;
			if ( $b1 >= 0x200 || $b2 >= 0x200 )
				return php_error('%8x skel id >= 200 , %x  %x', $pos-6, $b1, $b2);
			$time[] = [$op,$b1,$b2];
		}
		else
		{
			$line[$pos-2] = count($time);
			$b1 = str2int($m, $pos, 2);
				$pos += 2;
			if ( $b1 >= 0x200 )
				return php_error('%8x skel id >= 200 , %x', $pos-4, $b1);
			$time[] = [$op,$b1];
		}
	} // while (1)

done:
	return ['time'=>$time , 'loop'=>$loop];
}

function meta26( &$m )
{
	$anim = [];

	$st  = str2int($m,   0, 2);
	$dmy = str2int($m, $st, 2);
		$anim[0] = meta26_opcode($m, $dmy << 1);

	$ed = $dmy << 1;
		$st += 2;
	$id = 0;

	while ( $st < $ed )
	{
		$off = str2int($m, $st, 2);
			$st += 2;
		if ( $off > $dmy )
		{
			printf("%x = %x\n", $st-2, $id);
			$anim[$id] = meta26_opcode($m, $off << 1);
		}
		$id++;
	} // while ( $st < $ed )

	print_r($anim);
	return $anim;
}

function meta25( &$m )
{
	$max = str2int($m, 0, 3) - 8;
		$max >>= 3;

	for ( $i=0; $i < $max; $i++ )
	{
		$p = $i << 3;
		$pos = str2int($m, $p, 3);

		$cnt = str2int($m, $pos, 4);
			$pos += 4;
		for ( $j=0; $j < $cnt; $j++ )
		{
			$s = substr($m, $pos, 0x14);
				$pos += 0x14;
			echo debug($s, "$i $j");
		}
	} // for ( $i=0; $i < $max; $i++ )
	return;
}

