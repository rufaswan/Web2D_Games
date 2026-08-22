<?php
function sectsum( $fname )
{
	echo "== $fname\n";
	$data = [];
	$h1 = '';
	foreach ( file($fname) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		if ( strpos($line, '=') === false )
			continue;

		if ( $line[0] == '=' )
		{
			$h1 = '';
			if ( strpos($line, 'sect_sum') === false )
				continue;
			$line = preg_split('|[\s]+|', $line);
			$h1 = $line[2];
		}
		else
		{
			$line = preg_split('|[\s]+|', $line);

			$k = hexdec( $line[0] );
			$v = hexdec( $line[2] );
			if ( ! isset( $data[$h1][$k] ) )
				$data[$h1][$k] = 0;
			$data[$h1][$k] += $v;
		}
	} // foreach ( $file as $line )

	//print_r($data);
	foreach ( $data as $k => $v )
	{
		foreach ( $v as $vk => $vv )
		{
			printf("%s , %x , %x\n", $k, $vk, $vv);
		} // foreach ( $v as $vk => $vv )
	} // foreach ( $data as $k => $v )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	sectsum( $argv[$i] );
