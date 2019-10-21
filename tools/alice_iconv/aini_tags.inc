<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
function ainitags( &$file, &$st, $dir )
{
	global $gp_pc;
	printf("%8x : ", $st);
	$tag = substr($file, $st, 4);
	switch ( $tag )
	{
		case "HEL0":
			echo "HEL0\n";
			$st += 8;

			$buf = "";
			$dllno = sint32($file, $st);
			for ( $dn=0; $dn < $dllno; $dn++ )
			{
				$dll = utf8txt($file, $st);

				$funcno = sint32($file, $st);
				for ( $fn=0; $fn < $funcno; $fn++ )
				{
					$func = utf8txt($file, $st);
					$buf .= "$dn,$fn,$dll,$func";

					$parno = sint32($file, $st);
					for ( $pn=0; $pn < $parno; $pn++ )
					{
						$par = sint32($file, $st);
						$buf .= ",$par";
					}
					$buf .= "\n";
				}
			}
			file_put_contents("$dir/HEL0", $buf);
			return;
		case "FUNC":
			echo "FUNC\n";
			$st += 8;

			$buf = "";
			$funcno = sint32($file, $st);
			for ( $fn=0; $fn < $funcno; $fn++ )
			{
				$func = utf8txt($file, $st);
				$scon = sint16($file, $st);
				$addr = sint32($file, $st);
				$buf .= "$fn,$func,$scon,$addr\n";
			}
			file_put_contents("$dir/FUNC", $buf);
			return;
		case "VARI":
		case "MSGI":
			echo "$tag\n";
			$st += 8;

			$buf = "";
			$varno = sint32($file, $st);
			for ( $vn=0; $vn < $varno; $vn++ )
			{
				$var = utf8txt($file, $st);
				$buf .= "$vn,$var\n";
			}
			file_put_contents("$dir/$tag", $buf);
			return;
	}
	return;
}
