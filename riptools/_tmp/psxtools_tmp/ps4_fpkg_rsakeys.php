<?php
/*
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
 *
 * Special Thanks
 *   LibOrbisPkg
 *   https://github.com/maxton/LibOrbisPkg
 *     maxton
 *     Zer0xFF
 *     flatz
 *     idc
 */
$c_o = '';
$c_e = -1;
exec("openssl version", $c_o, $c_e);
if ( $c_e !== 0 )
	exit("ERROR openssl not found\n");

// Modulus         [256] n = p * q
// PublicExponent  [  4] e
// PrivateExponent [256] d
// Prime1          [128] p
// Prime2          [128] q
// Exponent1       [128] exp1 = d mod (p - 1)
// Exponent2       [128] exp2 = d mod (q - 1)
// Coefficient     [128] (InverseQ)(q) = 1 mod p
//
// openssl
//   exp1  = dmp1
//   exp2  = dmq1
//   coeff = qimp
//
// Public   = n + e
// Private  = n + p + q
//         += d                   (speedup)
//         += exp1 + exp2 + coeff (NULL or Chinese Remainder Theorem speedup)

$FakeKeyset = <<<_RSA
	[modulus]
		c6 cf 71 e7  e5 9a f0 d1  2a 2c 45 8b  f9 2a 0e c1
		43 05 8b c3  71 17 80 1d  cd 49 7d de  35 9d 25 9b
		a0 d7 a0 f2  7d 6c 08 7e  aa 55 02 68  2b 23 c6 44
		b8 44 18 eb  56 cf 16 a2  48 03 c9 e7  4f 87 eb 3d
		30 c3 15 88  bf 20 e7 9d  ff 77 0c de  1d 24 1e 63
		a9 4f 8a bf  5b be 60 19  68 33 3b fc  ed 9f 47 4e
		5f f8 ea cb  3d 00 bd 67  01 f9 2c 6d  c6 ac 13 64
		e7 67 14 f3  dc 52 69 6a  b9 83 2c 42  30 13 1b b2
		d8 a5 02 0d  79 ed 96 b1  0d f8 cc 0c  df 81 95 4f
		03 58 09 57  0e 80 69 2e  fe ff 52 77  ea 75 28 a8
		fb c9 be bf  9f bb b7 79  8e 18 05 e1  80 bd 50 34
		94 81 d3 53  c2 69 a2 d2  4c cf 6c f4  57 2c 10 4a
		3f fb 22 fd  8b 97 e2 c9  5b a6 2b cd  d6 1b 6b db
		68 7f 4b c2  a0 50 34 c0  05 e5 8d ef  24 67 ff 93
		40 cf 2d 62  a2 a0 50 b1  f1 3a a8 3d  fd 80 d1 f9
		b8 05 22 af  c8 35 45 90  58 8e e3 3a  7c bd 3e 27
	[publicexponent]
		00 01 00 01
	[privateexponent]
		7f 76 cd 0e  e2 d4 de 05  1c c6 d9 a8  0e 8d fa 7b
		ca 1e aa 27  1a 40 f8 f1  22 87 35 dd  db fd ee f8
		c2 bc bd 01  fb 8b e2 3e  63 b2 b1 22  5c 56 49 6e
		11 be 07 44  0b 9a 26 66  d1 49 2c 8f  d3 1b cf a4
		a1 b8 d1 fb  a4 9e d2 21  28 83 09 8a  f6 a0 0b a3
		d6 0f 9b 63  68 cc bc 0c  4e 14 5b 27  a4 a9 f4 2b
		b9 b8 7b c0  e6 51 ad 1d  77 d4 6b b9  ce 20 d1 26
		66 7e 5e 9e  a2 e9 6b 90  f3 73 b8 52  8f 44 11 03
		0c 13 97 39  3d 13 22 58  d5 43 82 49  da 6e 7c a1
		c5 8c a5 b0  09 e0 ce 3d  df f4 9d 3c  97 15 e2 6a
		c7 2b 3c 50  93 23 db ba  4a 22 66 44  ac 78 bb 0e
		1a 27 43 b5  71 67 af f4  ab 48 46 93  73 d0 42 ab
		93 63 e5 6c  9a de 50 24  c0 23 7d 99  79 3f 22 07
		e0 c1 48 56  1b df 83 09  12 b4 2d 45  6b c9 c0 68
		85 99 90 79  96 1a d7 f5  4d 1f 37 83  40 4a ec 39
		37 a6 80 92  7d c5 80 c7  d6 6f fe 8a  79 89 c6 b1
	[prime1]
		fe f6 bf 1d  69 ab 16 25  08 47 55 6b  86 e4 35 88
		72 2a b1 3d  f8 b6 44 ca  b3 ab 19 d1  04 24 28 0a
		74 55 b8 15  45 09 cc 13  1c f2 ba 37  a9 03 90 8f
		02 10 ff 25  79 86 cc 18  50 9a 10 5f  5b 4c 1c 4e
		b0 a7 e3 59  b1 2d a0 c6  b0 20 2c 21  33 12 b3 af
		72 34 83 cd  52 2f af 0f  20 5a 1b c0  e2 a3 76 34
		0f d7 fc c1  41 c9 f9 79  40 17 42 21  3e 9d fd c7
		c1 50 de 44  5a c9 31 89  6a 78 05 be  65 b4 e8 2d
	[prime2]
		c7 9e 47 58  00 7d 62 82  b0 d2 22 81  d4 a8 97 1b
		79 0c 3a b0  d7 c9 30 e3  c3 53 8e 57  ef f0 9b 9f
		b3 90 52 c6  94 22 36 aa  e6 4a 5f 72  1d 70 e8 76
		58 c8 b2 91  ce 9c c3 e9  09 7f 2e 47  97 cc 90 39
		15 35 31 de  1f 0c 8c 0d  c1 c2 92 be  97 bf 2f 91
		a1 8c 7d 50  a8 21 2f d7  a2 9a 7e b5  a7 2a 90 02
		d9 f3 3d d1  eb b8 e0 5a  79 9e 7d 8d  ca 18 6d bd
		9e a1 80 28  6b 2a fe 51  24 9b 6f 4d  84 77 80 23
	[exponent1]
		6d 48 e0 54  40 25 c8 41  29 52 42 27  eb d2 c7 ab
		6b 9c 27 0a  b4 1f 94 4e  fa 42 1d b7  bc b9 ae bc
		04 6f 75 8f  10 5f 89 ac  ab 9c d2 fa  e6 a4 13 83
		68 d4 56 38  fe e5 2b 78  44 9c 34 e6  5a a0 be 05
		70 ad 15 c3  2d 31 ac 97  5d 88 fc c1  62 3d e2 ed
		11 db b6 9e  fc 5a 5a 03  f6 cf 08 d4  5d 90 c9 2a
		b9 9b cf c8  1a 65 f3 5b  e8 7f cf a5  a6 4c 5c 2a
		12 0f 92 a5  e3 f0 17 1e  9a 97 45 86  fd db 54 25
	[exponent2]
		2a 51 ce 02  44 28 50 e8  30 20 7c 9c  55 bf 60 39
		bc d1 f0 e7  68 f8 08 5b  61 1f a7 bf  d0 e8 8b b5
		b1 d5 d9 16  ac 75 0c 6d  f2 e0 b5 97  75 d2 68 16
		1f 00 7d 8b  17 e8 78 48  41 71 2b 18  96 80 11 db
		68 39 9c d6  e0 72 42 86  f0 1b 16 0d  3e 12 94 3d
		25 a8 a9 30  9e 54 5a d6  36 6c d6 8c  20 62 8f a1
		6b 1f 7c 6d  b2 b1 c1 2e  ad 36 02 9c  3a ca 2f 09
		d2 45 9e eb  f2 bc 6c aa  3b 3e 90 bc  38 67 35 4d
	[coefficient]
		0b 67 1c 0d  6c 57 d3 e7  05 65 94 31  56 55 fd 28
		08 fa 05 8a  cc 55 39 61  97 63 a0 16  27 3d ed c1
		16 40 2a 12  ea 6f d9 d8  58 56 a8 56  8b 0d 38 5e
		1e 80 3b 5f  40 80 6f 62  4f 28 a2 69  f3 d3 f7 fd
		b2 c3 52 43  20 92 9d 97  8d a0 15 07  15 6e a4 0d
		56 d3 37 1a  c4 9e df 02  49 b8 0a 84  62 f5 fa b9
		3f a4 09 76  cc aa b9 9b  a6 4f c1 6a  64 ce d8 77
		ab 4b f9 a0  ae da f1 67  87 7c 98 5c  7e b8 73 f5
_RSA;

$DebugRifKeyset = <<<_RSA
	[modulus]
		c2 d2 44 bc  dd 84 3f d9  c5 22 af f7  fc 88 8a 33
		80 ed 8e e2  cc 81 f7 ec  f8 1c 79 bf  02 bb 12 8e
		61 68 29 1b  15 b6 5e c6  f8 bf 5a e0  3b 6a 6c d9
		d6 f5 75 ab  a0 6f 34 81  34 9a 5b ad  ed 31 e3 c6
		ea 1a d1 13  22 bb b3 da  b3 b2 53 bd  45 79 87 ad
		0a 01 72 18  10 29 49 f4  41 7f d6 47  0c 72 92 9e
		e9 bb 95 a9  5d 79 eb e4  30 76 90 45  4b 9d 9c cf
		92 03 60 8c  4b 6c b3 7a  3a 05 39 a0  66 a9 35 cf
		b9 fa ad 9c  ab eb e4 6a  8c e9 3b cc  72 12 62 63
		bd 80 c4 ee  37 2b 32 03  a3 09 f7 a0  61 57 ad 0d
		cf 15 98 9e  4e 49 f8 b5  a3 5c 27 ee  45 04 ea e4
		4b bc 8f 87  ed 19 1e 46  75 63 c4 5b  d5 bc 09 2f
		02 73 19 3c  58 55 49 66  4c 11 ec 0f  09 fa a5 56
		0a 5a 63 56  ad a0 0d 86  08 c1 e6 b6  13 22 49 2f
		7c db 4c 56  97 0e c2 d9  2e 87 bc 0e  67 c0 1b 58
		bc 64 2b c2  6e e2 93 2e  b5 6b 70 a4  42 9f 64 c1
	[publicexponent]
		00 01 00 01
	[privateexponent]
		01 61 ad d8  9c 06 89 d0  60 c8 41 f0  b3 83 01 5d
		e3 a2 6b a2  ba 9a 0a 58  cd 1a a0 97  64 ec d0 31
		1f ca 36 0e  69 dd 40 f7  4e c0 c6 a3  73 f0 69 84
		b2 f4 4b 29  14 2a 6d b8  23 d8 1b 61  d4 9e 87 b3
		bb a9 c4 85  4a f8 03 4a  bf fe f9 fe  8b dd 54 83
		ba e0 2f 3f  b1 ef a5 05  5d 28 8b ab  b5 d0 23 2f
		8a cf 48 7c  aa bb c8 5b  36 27 c5 16  a4 b6 61 ac
		0c 28 47 79  3f 38 ae 5e  25 c6 af 35  ae bc b0 f3
		bc bd fd a4  87 0d 14 3d  90 e4 de 5d  1d 46 81 f1
		28 6d 2f 2c  5e 97 2d 89  2a 51 72 3c  20 02 59 b1
		98 93 05 1e  3f a1 8a 69  30 0e 70 84  8b ae 97 a1
		08 95 63 4c  c7 e8 5d 59  ca 78 2a 23  87 ac 6f 04
		33 b1 61 b9  f0 95 da 33  cc e0 4c 82  68 82 14 51
		be 49 1c 58  a2 8b 05 4e  98 37 eb 94  0b 01 22 dc
		b3 19 ca 77  a6 6e 97 ff  8a 53 5a c5  24 e4 af 6e
		a8 2b 53 a4  be 96 a5 7b  ce 22 56 a3  f1 cf 14 a5
	[prime1]
		e5 62 e1 7f  9f 86 08 e2  61 d3 d0 42  e2 c4 b6 a8
		51 09 19 14  a4 3a 11 4c  33 a5 9c 01  5e 34 b6 3f
		02 1a ca 47  f1 4f 3b 35  2a 07 20 ec  d8 c1 15 d9
		ca 03 4f b8  e8 09 73 3f  85 b7 41 d5  51 3e 7b e3
		53 2b 48 8b  8e cb ba f7  e0 60 f5 35  0e 6f b0 d9
		2a 99 d0 ff  60 14 ed 40  ea f8 d7 0b  c3 8d 8c e8
		81 b3 75 93  15 b3 7d f6  39 60 1a 00  e7 c3 27 ad
		a4 33 d5 3e  a4 35 48 6f  22 ef 5d dd  7d 7b 61 05
	[prime2]
		d9 6c c2 0c  f7 ae d1 f3  3b 3b 49 1e  9f 12 9c a1
		78 1f 35 1d  98 26 13 71  f9 09 fd f0  ad 38 55 b7
		ee 61 04 72  51 87 2e 05  84 b1 1d 0c  0d db d4 25
		3e 26 ed ea  b8 f7 49 fe  a2 94 e6 f2  08 92 a7 85
		f5 30 b9 84  22 bf ca f0  5f cb 31 20  34 49 16 76
		34 cc 7a cb  96 fe 78 7a  41 fe 9a a2  23 f7 68 80
		d6 ce 4a 78  a5 b7 05 77  81 1f de 5e  a8 6e 3e 87
		ec 44 d2 69  c6 54 91 6b  5e 13 8a 03  87 05 31 8d
	[exponent1]
		cd 9a 61 b0  b8 d5 b4 e4  e4 f6 ab f7  27 b7 56 59
		6b b9 11 e7  f4 83 af b9  73 99 7f 49  a2 9c f0 b5
		6d 37 82 14  15 f1 04 8a  d4 8e eb 2e  1f e2 81 a9
		62 6e b1 68  75 62 f3 0f  fe d4 91 87  98 78 bf 26
		b5 07 58 d0  ee 3f 21 e8  c8 0f 5f fa  1c 64 74 49
		52 eb e7 ee  de ba 23 26  4a f6 9c 1a  09 3f b9 0b
		36 26 1a be  a9 76 e6 f2  69 de ff af  cc 0c 9a 66
		03 86 0a 1f  49 a4 10 b6  bc c3 7c 88  e8 ce 4b d9
	[exponent2]
		b3 73 a3 59  e6 97 c0 ab  3b 68 fc 39  ac db 44 b1
		b4 9e 35 4d  be c5 36 69  6c 3d c5 fc  fe 4b 2f dc
		86 80 46 96  40 1a 0d 6e  fa 8c e0 47  91 ac ad 95
		2b 8e 1f f2  0a 45 f8 29  95 70 c6 88  5f 71 03 99
		79 bc 84 71  bd e8 84 8c  0e d4 7b 30  74 57 1a 95
		e7 90 19 8d  ad 8b 4c 4e  c3 e7 6b 23  86 01 ee 9b
		e0 2f 15 a2  2c 4c 39 d3  df 9c 39 01  f1 8c 44 4a
		15 44 dc 51  f7 22 d7 7f  41 7f 68 fa  ee 56 e8 05
	[coefficient]
		c0 32 43 d3  8c 3d b4 d2  48 8c 42 41  24 94 6c 80
		c9 c1 79 36  7f ac c3 ff  6a 25 eb 2c  fb d4 2b a0
		eb fe 25 e9  c6 77 ce fe  2d 23 fe d0  f4 0f d9 7e
		d5 a5 7d 1f  c0 e8 e8 ec  80 5b c7 fd  e2 bd 94 a6
		2b dd 6a 60  45 54 ab ca  42 9c 6a 6c  bf 3c 84 f9
		a5 0e 63 0c  51 58 62 6d  5a b7 3c 3f  49 1a d0 93
		b8 4f 1a 6c  5f c5 e5 a9  75 d4 86 9e  df 87 0f 27
		b0 26 78 4e  fb c1 8a 4a  24 3f 7f 8f  9a 12 51 cb
_RSA;

$PkgDerivedKey3Keyset = <<<_RSA
	[modulus]
		d2 12 fc 33  5f 6d db 83  16 09 62 8b  03 56 27 37
		82 d4 77 85  35 29 39 2d  52 6b 8c 4c  8c fb 06 c1
		84 5b e7 d4  f7 bc d2 4e  62 45 cd 2a  bb d7 77 76
		45 36 55 27  3f b3 f5 f9  8e da 4b ef  aa 59 ae b3
		9b ea 54 98  d2 06 32 6a  58 31 2a e0  d4 4f 90 b5
		0a 7d ec f4  3a 9c 52 67  2d 99 31 8e  0c 43 e6 82
		fe 07 46 e1  2e 50 d4 1f  2d 2f 7e d9  08 ba 06 b3
		bf 2e 20 3f  4e 3f fe 44  ff aa 50 43  57 91 69 94
		49 15 82 82  e4 0f 4c 8d  9d 2c c9 5b  1d 64 bf 88
		8b d4 c5 94  e7 65 47 84  1e e5 79 10  fb 98 93 47
		b9 7d 85 12  a6 40 98 2c  f7 92 bc 95  19 32 ed e8
		90 56 0d 65  c1 aa 78 c6  2e 54 fd 5f  54 a1 f6 7e
		e5 e0 5f 61  c1 20 b4 b9  b4 33 08 70  e4 df 89 56
		ed 01 29 46  77 5f 8c b8  a9 f5 1e 2e  b3 b9 bf e0
		09 b7 8d 28  d4 a6 c3 b8  1e 1f 07 eb  b4 12 0b 95
		b8 85 30 fd  dc 39 13 d0  7c dc 8f ed  f9 c9 a3 c1
	[publicexponent]
		00 01 00 01
	[privateexponent]
		32 d9 03 90  8f bd b0 8f  57 2b 28 5e  0b 8d b3 ea
		5c d1 7e a8  90 88 8c dd  6a 80 bb b1  df c1 f7 0d
		aa 32 f0 b7  7c cb 88 80  0e 8b 64 b0  be 4c d6 0e
		9b 8c 1e 2a  64 e1 f3 5c  d7 76 01 41  5e 93 5c 94
		fe dd 46 62  c3 1b 5a e2  a0 bc 2d eb  c3 98 0a a7
		b7 85 69 70  68 2b 64 4a  b3 1f cc 7d  dc 7c 26 f4
		77 f6 5c f2  ae 5a 44 2d  d3 ab 16 62  04 19 ba fb
		90 ff e2 30  50 89 6e cb  56 b2 eb c0  91 16 92 5e
		30 8e ae c7  94 5d fd 35  e1 20 f8 ad  3e bc 08 bf
		c0 36 74 9f  d5 bb 52 08  fd 06 66 f3  7a b3 04 f4
		75 29 5d e9  5f aa 10 30  b2 0f 5a 1a  c1 2a b3 fe
		cb 21 ad 80  ec 8f 20 09  1c db c5 58  94 c2 9c c6
		ce 82 65 3e  57 90 bc a9  8b 06 b4 f0  72 f6 77 df
		98 64 f1 ec  fe 37 2d bc  ae 8c 08 81  1f c3 c9 89
		1a c7 42 82  4b 2e dc 8e  8d 73 ce b1  cc 01 d9 08
		70 87 3c 44  08 ec 49 8f  81 5a e2 40  ff 77 fc 0d
	[prime1]
		f9 67 ad 99  12 31 0c 56  a2 2e 16 1c  46 b3 4d 5b
		43 be 42 a2  f6 86 96 80  42 c3 c7 3f  c3 42 f5 87
		49 33 9f 07  5d 6e 2c 04  fd e3 e1 b2  ae 0a 0c f0
		c7 a6 1c a1  63 50 c8 09  9c 51 24 52  6c 5e 5e bd
		1e 27 06 bb  bc 9e 94 e1  35 d4 6d b3  cb 3c 68 dd
		68 b3 fe 6c  cb 8d 82 20  76 23 63 b7  e9 68 10 01
		4e dc ba 27  5d 01 c1 2d  80 5e 2b af  82 6b d8 84
		b6 10 52 86  a7 89 8e ae  9a e2 89 c6  f7 d5 87 fb
	[prime2]
		d7 a1 0f 9a  8b f2 c9 11  95 32 9a 8c  f0 d9 40 47
		f5 68 a0 0d  bd c1 fc 43  2f 65 f9 c3  61 0f 25 77
		54 ad d7 58  ac 84 40 60  8d 3f f3 65  89 75 b5 c6
		2c 51 1a 2f  1f 22 e4 43  11 54 be c9  b4 c7 b5 1b
		05 0b bc 56  9a cd 4a d9  73 68 5e 5c  fb 92 b7 8b
		0d ff f5 07  ca b4 c8 9b  96 3c 07 9e  3e 6b 2a 11
		f2 8a b1 8a  d7 2e 1b a5  53 24 06 ed  50 b8 90 67
		b1 e2 41 c6  92 01 ee 10  f0 61 bb fb  b2 7d 4a 73
	[exponent1]
		52 cc 2d a0  9c 9e 75 e7  28 ee 3d de  e3 45 d1 4f
		94 1c cc c8  87 29 45 3b  8d 6e ab 6e  2a a7 c7 15
		43 a3 04 8f  90 5f eb f3  38 4a 77 fa  36 b7 15 76
		b6 01 1a 8e  25 87 82 f1  55 d8 c6 43  2a c0 e5 98
		c9 32 d1 94  6f d9 01 ba  06 81 e0 6d  88 f2 24 2a
		25 01 64 5c  bf f2 d9 99  67 3e f6 72  ee e4 e2 33
		5c f8 00 40  e3 2a 9a f4  3d 22 86 44  3c fb 0a a5
		7c 3f cc f5  f1 16 c4 ac  88 b4 de 62  94 92 6a 13
	[exponent2]
		7c 9d ad 39  e0 d5 60 14  94 48 19 7f  88 95 d5 8b
		80 ad 85 8a  4b 77 37 85  d0 77 bb bf  89 71 4a 72
		cb 72 68 38  ec 02 c6 7d  c6 44 06 33  51 1c c0 ff
		95 8f 0d 75  dc 25 bb 0b  73 91 a9 6d  42 d8 03 b7
		68 d4 1e 75  62 a3 70 35  79 78 00 c8  f5 ef 15 b9
		fc 4e 47 5a  c8 70 70 5b  52 98 c0 c2  58 4a 70 96
		cc b8 10 e1  2f 78 8b 2b  a1 7f f9 ac  de f0 bb 2b
		e2 66 e3 22  92 31 21 57  92 c4 b8 f2  3e 76 20 37
	[coefficient]
		45 97 55 d4  22 08 5e f3  5c b4 05 7a  fd aa 42 42
		ad 9a 8c a0  6c bb 1d 68  54 54 6e 3e  32 e3 53 73
		76 f1 3e 01  ea d3 cf eb  eb 23 3e c0  be ce ec 2c
		89 5f a8 27  3a 4c b7 e6  74 bc 45 4c  26 c8 25 ff
		34 63 25 37  e1 48 10 c1  93 a6 af eb  ba e3 a2 f1
		3d ef 63 d8  f4 fd d3 ee  e2 5d e9 33  cc ad ba 75
		5c 85 af ce  a9 3d d1 a2  17 f3 f6 98  b3 50 8e 5e
		f6 eb 02 8e  a1 62 a7 d6  2c ec 91 ff  15 40 d2 e3
_RSA;

$keyset = ['FakeKeyset' , 'DebugRifKeyset' , 'PkgDerivedKey3Keyset'];
//////////////////////////////
function keyint( $key )
{
	$hex = hex2bin($key);

	// is positive number
	$b = ord( $hex[0] );
	if ( ($b & 0x80) === 0 )
		return '0x'.$key;

	// is negative number
	// convert 0xff to -0x01
	//         c7 ff
	// NOT   = 38 00
	// ADD 1 = 38 01
	$len = strlen($hex);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $hex[$i] );
		$b = ~$b & 0xff;
		$hex[$i] = chr($b);
	}

	$c = 1;
	$i = $len;
	while ( $c && $i > 0 )
	{
		$i--;
		$b  = ord( $hex[$i] );
		$b += $c;
		$c  = $b >> 8;
		$b &= 0xff;
		$hex[$i] = chr($b);
	} // while ( $c && $i > 0 )

	return '-0x'.bin2hex($hex);
}

//////////////////////////////
// https://www.openssl.org/docs/man1.1.1/man1/asn1parse.html
// https://www.openssl.org/docs/man1.1.1/man3/ASN1_generate_nconf.html
// https://www.openssl.org/docs/man1.1.1/man1/rsa.html
// https://www.openssl.org/docs/man1.1.1/man3/RSA_check_key.html
foreach ( $keyset as $ks )
{
	$ksv = explode("\n", $$ks);

	$buf = [];
	$h = '';
	foreach ( $ksv as $s )
	{
		$s = preg_replace('|[\s]+|', '', $s);
		if ( empty($s) )
			continue;
		if ( $s[0] === '[' )
		{
			$s = str_replace(['[',']'], '', $s);
			$h = $s;
		}
		else
		{
			if ( ! isset( $buf[$h] ) )
				$buf[$h] = '';
			$buf[$h] .= $s;
		}
	} // foreach ( $ks as $s )

	$n     = keyint( $buf['modulus']         );
	$e     = keyint( $buf['publicexponent']  );
	$d     = keyint( $buf['privateexponent'] );
	$p     = keyint( $buf['prime1']          );
	$q     = keyint( $buf['prime2']          );
	$exp1  = keyint( $buf['exponent1']       );
	$exp2  = keyint( $buf['exponent1']       );
	$coeff = keyint( $buf['coefficient']     );

$asncfg = <<<_ASN
asn1=SEQUENCE:private_key
[private_key]
version=INT:0
n=INT:$n
e=INT:$e
d=INT:$d
p=INT:$p
q=INT:$q
exp1=INT:$exp1
exp2=INT:$exp2
coeff=INT:$coeff
_ASN;

/*
d=INT:$d
exp1=INT:$exp1
exp2=INT:$exp2
coeff=INT:$coeff
 */

	$asn = "$ks.asn";
	$der = "$ks.der";
	file_put_contents($asn, $asncfg);

	$c_e = -1;
	$c_o = '';
	exec("openssl asn1parse -genconf $asn -out $der"           , $c_o, $c_e);
	exec("openssl rsa -in $der -inform der -noout -text -check", $c_o, $c_e);
	print_r($c_o);
} // foreach ( $keyset as $ks )
