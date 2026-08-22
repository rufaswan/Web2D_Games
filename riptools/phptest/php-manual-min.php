<?php
// to copy only defined functions for current PHP version
// from PHP offline reference manual

$gp_src = './php-chunked-xhtml';
	if ( $argc > 1 && is_dir( $argv[1] ) )
		$gp_src = sprintf('%s/php-chunked-xhtml', $argv[1]);

	if ( ! is_dir($gp_src) )
		exit("DIR $gp_src not found\n");

$gp_dst = sprintf('php_funcs/%s', PHP_VERSION);
//$gp_dst = sprintf('php_funcs/%06d', PHP_VERSION_ID);
	@mkdir($gp_dst, 0755, true);

$funcs = get_defined_functions();
foreach ( $funcs['internal'] as $f )
{
	$s = str_replace('_', '-', $f);
	$html = sprintf('function.%s.html', $s);

	echo "COPY $html\n";
	copy("$gp_src/$html", "$gp_dst/$html");
}
