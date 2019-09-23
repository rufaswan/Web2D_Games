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
// Sys42IDE.exe v1.02 , System 4.0 SDK
$code42 = <<<_CODE

PUSH  POP  REF  REFREF  PUSHGLOBALPAGE
PUSHLOCALPAGE  INV  NOT  COMPL  ADD

SUB  MUL  DIV  MOD  AND
OR  XOR  LSHIFT  RSHIFT  LT

GT  LTE  GTE  NOTE  EQUALE
ASSIGN  PLUSA  MINUSA  MULA  DIVA

MODA  ANDA  ORA  XORA  LSHIFTA
RSHIFTA  F_ASSIGN  F_PLUSA  F_MINUSA  F_MULA

F_DIVA  DUP2  DUP_X2  ???  JUMP
IFZ  IFNZ  RETURN  CALLFUNC  INC

DEC  FTOI  ITOF  F_INV  F_ADD
F_SUB  F_MUL  F_DIV  F_LT  F_GT

F_LTE  F_GTE  F_NOTE  F_EQUALE  F_PUSH
S_PUSH  S_POP  S_ADD  S_ASSIGN  S_PLUSA

S_REF  S_REFREF  S_NOTE  S_EQUALE  SF_CREATE
SF_CREATEPIXEL  SF_CREATEALPHA  SR_POP  SR_ASSIGN  SR_REF

SR_REFREF  A_ALLOC  A_REALLOC  A_FREE  A_NUMOF
A_COPY  A_FILL  C_REF  C_ASSIGN  MSG

CALLHLL  PUSHSTRUCTPAGE  CALLMETHOD  SH_GLOBALREF  SH_LOCALREF
SWITCH  STRSWITCH  FUNC  EOF  CALLSYS

SJUMP  CALLONJUMP  SWAP  SH_STRUCTREF  S_LENGTH
S_LENGTHBYTE  I_STRING  CALLFUNC2  CMD_DUP2_X1  R_ASSIGN

FT_ASSIGNS  ASSERT  S_LT  S_GT  S_LTE
S_GTE  S_LENGTH2  S_LENGTHBYTE2  NEW  DELETE

CHECKUDO  A_REF  ???  DUP_U2  SP_INC
SP_DEC  ENDFUNC  R_EQUALE  R_NOTE  SH_LOCALCREATE

SH_LOCALDELETE  STOI  A_PUSHBACK  A_POPBACK  S_EMPTY
A_EMPTY  A_ERASE  A_INSERT  SH_LOCALINC  SH_LOCALDEC

SH_LOCALASSIGN  ITOB  S_FIND  S_GETPART  A_SORT
S_PUSHBACK  S_POPBACK  FTOS  S_MOD  S_PLUSA2

OBJSWAP  S_ERASE  SR_REF2  S_ERASE2  S_PUSHBACK2
S_POPBACK2

_CODE;
$gp_code = preg_split("|[\s]+|", trim($code42));
//print_r( $gp_code );
//exit();

function code2inst( &$file, &$st )
{
	global $gp_code;
	$code = str2int($file, $st, 2);
	if ( ! isset( $gp_code[$code] ) )
		return "";

	$c = $gp_code[$code];
	switch ( $c )
	{
		case "PUSH":  case "S_PUSH":  case "F_PUSH":
		case "JUMP":
		case "IFZ":  case "IFNZ":
		case "FUNC":  case "ENDFUNC":
		case "CALLFUNC":  case "CALLMETHOD":  case "CALLSYS":
		case "SR_REF":  case "SR_REF2":
		case "SH_GLOBALREF":  case "SH_STRUCTREF":  case "SH_LOCALREF":
		case "SH_LOCALDELETE":
		case "SH_LOCALINC":  case "SH_LOCALDEC":
		case "SWITCH":  case "STRSWITCH":
		case "MSG":
		case "EOF":
			$n1 = sint32($file, $st);
			return sprintf("$c %x", $n1);
		case "CALLHLL":
		case "SH_LOCALCREATE":
		case "SH_LOCALASSIGN":
			$n1 = sint32($file, $st);
			$n2 = sint32($file, $st);
			return sprintf("$c %x %x", $n1, $n2);
		default:
			return sprintf("$c");
	}
	return "";
}
