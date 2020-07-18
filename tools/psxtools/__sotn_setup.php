<?php
require "common.inc";

$gp_patch = array();

function isofile( $fname )
{
	$iso = array();
	foreach ( file($fname) as $line )
	{
		$line = preg_replace('|[\s]+|', '', $line);
		if ( empty($line) )
			continue;
		$line = explode(',', strtolower($line));
		$lba = hexdec( $line[0] );
		$line[0] = $lba * 0x800;
		$line[2] = hexdec( $line[2] ); // filesize
		$iso[ $lba ] = $line;
	}
	return $iso;
}

function cp_map( &$iso, $dir, $off )
{
	list($st,$ed,$bk) = $off;
	$id = 0;
	$dra = file_get_contents("$dir/dra.bin");
	while ( $st < $ed )
	{
		$b1 = str2int($dra, $st+ 0, 3); // f_xxx.bin
		$b2 = str2int($dra, $st+ 4, 3); // xxx.bin
		//$b3 = str2int($dra, $st+ 8, 3); // filesize xxx.bin
		//$b4 = str2int($dra, $st+12, 3); // sd_xxx.vh
		//$b5 = str2int($dra, $st+16, 3); // filesize sd_xxx.vh
		//$b6 = str2int($dra, $st+20, 3); // filesize sd_xxx.vb

		$d = "$dir/sotn/map_$id";
		@mkdir($d, 0755, true);

		$b13 = $iso[$b1][3];
		$b23 = $iso[$b2][3];
		printf("cp_map() %s -> %s\n", $b13, "$d/st.1");
		printf("cp_map() %s -> %s\n", $b23, "$d/st.2");
		copy("$dir/$b13", "$d/st.1");
		copy("$dir/$b23", "$d/st.2");

		$txt = "";
		$txt .= "st.1 = $b13\n";
		$txt .= "st.2 = $b23\n";
		foreach ( $pat[$b23] as $pk => $pv )
			$txt .= sprintf("%s = %s\n" , $pk, implode(' , ', $pv));
		save_file("$d/setup.txt", $txt);

		$id++;
		$st += $bk;
	}
	return;
}

function cp_servant( &$iso, $dir, $off )
{
	// off1  ft_xxx.bin
	// off2  sd_xxx.vh
	// off3  sd_xxx.vb
	list($ram,$num,$off1,$off2,$off3) = $off;
	$dra = file_get_contents("$dir/dra.bin");
	for ( $i=0; $i < $num; $i++ )
	{
		$b1 = str2int($dra, $off1+$i*4, 3);
		$b13 = $iso[$b1][3];
		$b23 = str_replace('ft_', 'tt_', $b13);

		$d = "$dir/sotn/servant_$i";
		@mkdir($d, 0755, true);

		printf("cp_servant() %s -> %s\n", $b13, "$d/serv.1");
		printf("cp_servant() %s -> %s\n", $b23, "$d/serv.2");
		copy("$dir/$b13", "$d/serv.1");
		copy("$dir/$b23", "$d/serv.2");

		$txt = sprintf("ramint = %x\n", $ram);
		save_file("$d/setup.txt", $txt);
	}
	return;
}

function cp_weapon( $dir, $off )
{
	list($ram,$sz1,$sz2) = $off[0];
	$sz3 = $sz1 + $sz2;
	$id = 0;
	for ( $i=0; $i < 2; $i++ )
	{
		$wpn = file_get_contents("$dir/bin/weapon{$i}.bin");
		$ed = strlen($wpn);
		for ( $st=0; $st < $ed; $st += $sz3 )
		{
			$b1 = substr($wpn, $st+   0, $sz1);
			$b2 = substr($wpn, $st+$sz1, $sz2);

			$d = "$dir/sotn/weapon_$id";
			save_file("$d/serv.1", $b1);
			save_file("$d/serv.2", $b2);

			$txt = sprintf("ramint = %x\n", $ram);
			save_file("$d/setup.txt", $txt);
			$id++;
		}
	}
	return;
}
//////////////////////////////
function sotn( $dir )
{
	if ( ! is_dir($dir) )
		return;

	global $gp_patch;
	$gp_patch = psx_patch($dir, 'sotn');
	if ( empty($gp_patch) )
		return;
	$iso = isofile("$dir/iso.txt");

	arrayhex( $gp_patch['dra.bin']['map'] );
	arrayhex( $gp_patch['dra.bin']['servant'] );
	arrayhex( $gp_patch['dra.bin']['weapon'] );

	cp_map    ( $iso, $dir, $pat, $gp_patch['dra.bin']['map']     );
	cp_servant( $iso, $dir, $pat, $gp_patch['dra.bin']['servant'] );
	cp_weapon ( $dir, $gp_patch['dra.bin']['weapon'] );
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
