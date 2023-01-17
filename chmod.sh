#!/bin/bash
<<'////'
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
////

a0=$(realpath "$0")
d=$(dirname "$a0")

function chmodfile
{
	case "$1" in
		*'.sh')  chmod -c 755  "$1";;
		*)  chmod -c 644  "$1";;
	esac
}
export -f chmodfile

find "$d" -type f -print0 | xargs -0 -I {} bash -c 'chmodfile "$@"' _ {}
find "$d" -type d -print0 | xargs -0 -I {} chmod -c 755 {}
