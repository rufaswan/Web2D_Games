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
