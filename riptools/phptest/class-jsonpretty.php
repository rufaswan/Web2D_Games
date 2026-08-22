<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-jsonpretty');

$json = [
	'int'    => 500 ,
	'float'  => 123.45 ,
	'string' => '43fa0000' ,
	'array'  => [1,2,3,4,5] ,
	'bool'   => true ,
	'nested' => [
		[10,20,30,40,50],
		[60,70,80,90,100],
	] ,
];

$txt = jsonpretty::encode($json);
echo $txt . "\n";

//$json = (object)[];
$json = new stdClass;
$json->int    = 500;
$json->float  = 123.45;
$json->string = '43fa0000';
$json->bool   = true;

$txt = jsonpretty::encode($json);
echo $txt . "\n";
