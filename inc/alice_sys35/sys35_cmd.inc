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

// ABCDEFGHIJKLMNO PQRSTUVWXYZ
// 123456789abcdef 0123456789a
//
// c = int8   ,400
// v = operand,401,402
// s = string ,403,405
// h = int16
// w = int32
$sco_opcode35 = array(
	'S350' => 'w',
	'S351' => 'w',
	'153S' => 'w',
	'S360' => 'w',
	'S380' => 'w',

	'A' => '',
	'CB' => 'vvvvv',
	'CC' => 'vvvvvv',
	'CD' => 'vvvvvvvvvv',
	'CE' => 'vvvvvvvvv',
	'CF' => 'vvvvv',
	'CL' => 'vvvvv',
	'CM' => 'vvvvvvvvv',
	'CP' => 'vvv',
	'CS' => 'vvvvvvv',
	'CT' => 'vvv',
	'CU' => 'vvvvvv',
	'CV' => 'vvvvvv',
	'CX' => 'vvvvvvvv',
	'CY' => 'vvvvv',
	'CZ' => 'vvvvvvv',
	'DC' => 'vvv',
	'DF' => 'vvv',
	'DI' => 'vvv',
	'DR' => 'v',
	'DS' => 'vvvv',
	'EC' => 'v',
	'EG' => 'vvvvv',
	'EM' => 'vvvv',
	'EN' => 'vvvvv',
	'ES' => 'vvvvvv',
	'GS' => 'vv',
	'GX' => 'vv',
	'H' => 'cv',
	'IC' => 'vv',
	'IE' => 'vv',
	'IG' => 'vvvv',
	'IM' => 'vv',
	'IX' => 'v',
	'IY' => 'v',
	'IZ' => 'vv',
	'KI' => 'vvv',
	'KK' => 'v',
	'KN' => 'v',
	'KP' => 'v',
	'KQ' => 'vv',
	'KR' => 'v',
	'KW' => 'vv',
	'LD' => 'v',
	'LP' => 'vvv',
	'LT' => 'vv',
	'LXO' => 'vvv',
	'LXC' => 'v',
	'LXL' => 'vvv',
	'LXS' => 'vvv',
	'LXP' => 'vvv',
	'LXR' => 'vvv',
	'LXW' => 'vvv',
	'LXX' => 'vvv',
	'MA' => 'vv',
	'MC' => 'vv',
	'MD' => 'vvv',
	'ME' => 'vvvvv',
	'MF' => 'vvvv',
	'MH' => 'vvv',
	'MJ' => 'vvvvv',
	'ML' => 'vv',
	'MM' => 'vv',
	'MP' => 'vv',
	'MV' => 'v',
	'N+' => 'vvv',
	'N-' => 'vvv',
	'N*' => 'vvv',
	'N/vvv',
	'N>vvvv',
	'N<vvvv',
	'N=vvv',
	'N\vv',
	'N&vvv',
	'N|vvv',
	'N^vvv',
	'N~vv',
	'NBvvv',
	'NCvv',
	'NIvvvv',
	'NPvvvv',
	'NRvv',
	'ND+vvv',
	'ND-vvv',
	'ND*vvv',
	'ND/vvv',
	'NDAvv',
	'NDCvv',
	'NDDvv',
	'NDHvv',
	'NDMvv',
	'PCv',
	'PDv',
	'PGvvv',
	'PNv',
	'PPvvv',
	'PSvvvv',
	'QCvv',
	'QDv',
	'QPvvv',
	'R',
	'SCv',
	'SLv',
	'SMv',
	'SOv',
	'SPvv',
	'SQvvv',
	'SSv',
	'STv',
	'SUvv',
	'SWvvvv',
	'Tvv',
	'UDv',
	'UGvv',
	'URv',
	'USvv',
	'VBvvvvvvv',
	'VCvvvvvvv',
	'VEvvvvvv',
	'VF',
	'VGvvvv',
	'VHvvvvvv',
	'VICvvvv',
	'VIPvvvv',
	'VJvvvv',
	'VPvvvvvv',
	'VRvvv',
	'VSvvvvv',
	'VTvvvvvvvvvv',
	'VVvv',
	'VWvvv',
	'VXvvvv',
	'WVvvvv',
	'WWvvv',
	'WXvvvv',
	'Xv',
	'Yvv',
	'ZBv',
	'ZCvv',
	'ZEv',
	'ZFv',
	'ZGv',
	'ZHv',
	'ZIvv',
	'ZLv',
	'ZMv',
	'ZRvv',
	'ZSv',
	'ZWv',

);

/*

-1,{,v,w
-1,#,w,v
-1,]
-1,>,w
-1,@,w
-1,\,w
-1,%,v
-1,&,v

124,AIN_MSGI,w
125,AIN_125,w,v
126,AIN_126,w,v
127,AIN_127,w,v
128,AIN_128,h,w
129,AIN_129,v,v
130,AIN_130,v,v
# 131,AIN_131,
# 132,AIN_132,
133,AIN_133,v
# 134,AIN_134
# 135,AIN_135
136,AIN_136,v,v
137,AIN_137,v,v,v,v
# 138,AIN_138
# 139,???_DM.dat [custom]








0,TOC
1,TOS
2,TPC,v
3,TPS,v
4,TOP
5,TPP
6,inc,v
7,dec,v
8,TAA,v
9,TAB,v
10,wavLoad,v,v
11,wavPlay,v,v
12,wavStop,v
13,wavUnload,v
14,wavIsPlay,v,v
15,wavFade,v,v,v,v
16,wavIsFade,v,v
17,wavStopFade,v
18,trace,s
19,wav3DSetPos,v,v,v,v
20,wav3DCommit
21,wav3DGetPos,v,v,v,v
22,wav3DSetPosL,v,v,v
23,wav3DGetPosL,v,v,v
24,wav3DFadePos,v,v,v,v,v
25,wav3DIsFadePos,v,v
26,wav3DStopFadePos,v
27,wav3DFadePosL,v,v,v,v
28,wav3DIsFadePosL,v
29,wav3DStopFadePosL
30,sndPlay,v,v
31,sndStop
32,sndIsPlay,v
33,msg,s
34,HH,400,v
35,LC,v,v,s
  # 36  LE [multi]
37,LXG,v,s,s
38,MI,v,v,s
39,MS,v,s
40,MT,s
41,NT,s
  # 42  QE [multi]
43,UP,2,s,s
  # 44  F [multi]
45,wavWaitTime,v,v
46,wavGetPlayPos,v,v
47,wavWaitEnd,v
48,wavGetWaveTime,v,v
49,menuSetCbkSelect,s
50,menuSetCbkCancel,s
51,menuClearCbkSelect
52,menuClearCbkCancel
53,wav3DSetMode,v,v
54,grCopyStretch,v,v,v,v,v,v,v,v,v
55,grFilterRect,v,v,v,v,v
56,iptClearWheelCount
57,iptGetWheelCount,v,v
58,menuGetFontSize,v
59,msgGetFontSize,v
60,strGetCharType,v,v,v
61,strGetLengthASCII,v,v
62,sysWinMsgLock
63,sysWinMsgUnlock
64,aryCmpCount,v,v,v,v
65,aryCmpTrans,v,v,v,v,v,v
66,grBlendColorRect,v,v,v,v,v,v,v,v,v
67,grDrawFillCircle,v,v,v,v
68,MHH,v,v,v
69,menuSetCbkInit,s
70,menuClearCbkInit
  # 71
72,sysOpenShell,s
73,sysAddWebMenu,s,s
74,iptSetMoveCursorTime,v
75,iptGetMoveCursorTime,v
76,grBlt,v,v,v,v,v,v
77,LXWT,v,s
78,LXWS,v,v
79,LXWE,v,v
80,LXWH,v,400,v
81,LXWHH,v,400,v
82,sysGetOSName,v
83,patchEC,v
84,mathSetClipWindow,v,v,v,v
85,mathClip,v,v,v,v,v,v
86,LXF,v,s,s
87,strInputDlg,s,v,v,v
88,strCheckASCII,v,v
89,strCheckSJIS,v,v
90,strMessageBox,s
91,strMessageBoxStr,v
92,grCopyUseAMapUseA,v,v,v,v,v,v,v
93,grSetCEParam,v,v
94,grEffectMoveView,v,v,v,v
95,cgSetCacheSize,v
  # 96,AIN_HEL0 [custom]
97,gaijiSet,v,v
98,gaijiClearAll
99,menuGetLatestSelect,v
100,lnkIsLink,v,v,v
101,lnkIsData,v,v,v
102,fncSetTable,v,s
103,fncSetTableFromStr,v,v,v
104,fncClearTable,v
105,fncCall,v
106,fncSetReturnCode,v
107,fncGetReturnCode,v
108,msgSetOutputFlag,v
109,saveDeleteFile,v,v
110,wav3DSetUseFlag,v
111,wavFadeVolume,v,v,v,v
112,patchEMEN,v
113,wmenuEnableMsgSkip,v
114,winGetFlipFlag,v
115,cdGetMaxTrack,v
116,dlgErrorOkCancel,s,v
117,menuReduce,v
118,menuGetNumof,v
119,menuGetText,v,v
120,menuGoto,v,v
121,menuReturnGoto,v,v
122,menuFreeShelterDIB
123,msgFreeShelterDIB



36,LE,0,s,v,v
36,LE,1,s,v,v

42,QE,0,s,v,v
42,QE,1,s,v,v

44,F,1,v,v
44,F,2,v,v
44,F,3,v,v
44,F,4,v,v
44,F,5,v,v
44,F,6,v,v
44,F,7,v,v
44,F,8,v,v
44,F,9,v,v
44,F,10,v,v
44,F,11,v,s






-1,B,0,v
-1,B,1,v,v,v,v,v,v
-1,B,2,v,v,v,v,v,v
-1,B,3,v,v,v,v,v,v
-1,B,4,v,v,v,v,v,v
-1,B,10,v,v
-1,B,11,v,v
-1,B,12,v
-1,B,13,v
-1,B,14,v
-1,B,21,v,v,v
-1,B,22,v,v,v
-1,B,23,v,v,v
-1,B,24,v,v,v
-1,B,31,v,v,v
-1,B,32,v,v,v
-1,B,33,v,v,v
-1,B,34,v,v,v

-1,CK,1,v,v,v,v,v,v,v,v
-1,CK,2,v,v,v,v,v,v,v,v
-1,CK,3,v,v,v,v,v,v,v,v

-1,IK,0
-1,IK,1
-1,IK,2
-1,IK,3
-1,IK,4
-1,IK,5
-1,IK,6

-1,J,0,v,v
-1,J,1,v,v
-1,J,2,v,v
-1,J,3,v,v
-1,J,4

-1,LHD,1,v
-1,LHD,2,v
-1,LHD,3,v
-1,LHG,1,v
-1,LHG,2,v
-1,LHG,3,v
-1,LHM,1,v
-1,LHM,2,v
-1,LHM,3,v
-1,LHS,1,v
-1,LHS,2,v
-1,LHS,3,v
-1,LHW,1,v
-1,LHW,2,v
-1,LHW,3,v

-1,LL,0,v,v,v

-1,MG,0,v
-1,MG,1,v
-1,MG,2,v
-1,MG,3,v
-1,MG,4,v
-1,MG,5,v
-1,MG,6,v
-1,MG,7,v
-1,MG,100,v

-1,MN,0,v,v
-1,MN,1,v,v

-1,MZ,0,v,v,v

-1,NO,0,v,v,v
-1,NO,1,v,v,v

-1,PF,0,v
-1,PF,1,v
-1,PF,2,v,v
-1,PF,3,v,v

-1,PT,0,v,v,v
-1,PT,1,v,v,v,v,v
-1,PT,2,v,v,v,v

-1,PW,0,v
-1,PW,1,v
-1,PW,2,v,v
-1,PW,3,v,v

-1,SG,0,v
-1,SG,1,v
-1,SG,2,v
-1,SG,3,v
-1,SG,4,v
-1,SG,5,v,v
-1,SG,6,v,v
-1,SG,7,v,v
-1,SG,8,v,v

-1,SI,0,v
-1,SI,1,v
-1,SI,2,v

-1,SR,0,v
-1,SR,1,v

-1,SX,0,1,v,v,v
-1,SX,1,1,v,v,v
-1,SX,2,1,v,v,v
-1,SX,3,1,v,v,v
-1,SX,0,2,v
-1,SX,1,2,v
-1,SX,2,2,v
-1,SX,3,2,v
-1,SX,0,3
-1,SX,1,3
-1,SX,2,3
-1,SX,3,3
-1,SX,0,4,v
-1,SX,1,4,v
-1,SX,2,4,v
-1,SX,3,4,v

-1,UC,0,v
-1,UC,1,v
-1,UC,2,v
-1,UC,3,v

-1,VA,0,v,v,v
-1,VA,1,v,v,v
-1,VA,2,v,v,v
-1,VA,3,v,v,v
-1,VA,4,v,v,v
-1,VA,5,v,v,v
-1,VA,6,v,v,v
-1,VA,7,v,v,v
-1,VA,8,v,v,v
-1,VA,10,v,v,v
-1,VA,11,v,v,v

-1,VZ,0,v,v
-1,VZ,1,v,v
-1,VZ,2,v,v
-1,VZ,3,v,v

-1,WZ,0,v
-1,WZ,1,v

-1,ZA,0,v
-1,ZA,1,v
-1,ZA,3,v

-1,ZD,0,v
-1,ZD,1,v
-1,ZD,2,v
-1,ZD,3,v
-1,ZD,4,v

-1,ZT,0,v
-1,ZT,1,v
-1,ZT,2,v
-1,ZT,3,v
-1,ZT,4,v
-1,ZT,5,v
-1,ZT,10,v,v,v
-1,ZT,11,v,v
-1,ZT,20,v
-1,ZT,21,v

-1,ZZ,0,v
-1,ZZ,1,v
-1,ZZ,2,v
-1,ZZ,3,v
-1,ZZ,4,v
-1,ZZ,5,v
-1,ZZ,6,v
-1,ZZ,7,v
-1,ZZ,8,v
-1,ZZ,9,v
-1,ZZ,10,v
-1,ZZ,13,v
-1,ZZ,14,v



# in doc , but not in asm
-1,G,0,v
-1,G,1,v,v
 */
