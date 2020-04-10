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
//////////////////////////////
$APP_NAME = "Web2D Games";
$AUTHOR   = "Rufas Wan";
$GITHUB   = "https://github.com/rufaswan/Web2D_Games";

$LICENSE = <<<_TXT
Copyright (C) 2019 by {$AUTHOR}

This file is part of {$APP_NAME}.
    <{$GITHUB}>

{$APP_NAME} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

{$APP_NAME} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {$APP_NAME}.  If not, see <http://www.gnu.org/licenses/>.

_TXT;

function set_license( $fname )
{
	global $LICENSE;

	$skip = false;
	$text = "";
	foreach ( file($fname) as $line )
	{
		$l = trim($line);
		if ( $l == "[license]" )
		{
			$skip  = true;
			$text .= $line;
		}
		else
		if ( $l == "[/license]" )
		{
			$skip  = false;
			$text .= $LICENSE . $line;
		}
		else
		{
			if ( ! $skip )
				$text .= $line;
		}
	} // foreach ( $file as $line )

	file_put_contents($fname, $text);
}

if ( $argc == 1 )  exit();
for ( $i=1; $i < $argc; $i++ )
	set_license( $argv[$i] );
