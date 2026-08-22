<?php
$t = [];
$t['dir'] = str_replace('\\', '/', __DIR__);
while ( ! empty($t['dir']) )
{
	$t['inc'] = sprintf('%s/phpinc/tool.inc', $t['dir']);
	if ( is_file($t['inc']) )
	{
		define('TOOL_BASE', $t['dir']);
		require $t['inc'];
		break;
	}
	$t['p'] = strrpos($t['dir'], '/');
	if ( $t['p'] === false )
		break;
	$t['dir'] = substr($t['dir'], 0, $t['p']);
} // while ( ! empty($t['dir']) )
unset($t);
