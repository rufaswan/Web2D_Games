// <?php exit();

// 1,0 = main

$gp_pc["var"][0] = 0;
$gp_pc["var"][1] = 0;
$gp_pc["var"][2] = 0;
$gp_pc["var"][3] = 0;
$gp_pc["var"][4] = 0;
$gp_pc["var"][5] = 0;
$gp_pc["var"][6] = 0;
$gp_pc["var"][7] = 0;
$gp_pc["var"][8] = 0;
$gp_pc["var"][9] = 0;
$gp_pc["var"][10] = 0;
sco_cmd( 'ZD_0' , array( 0 ) );
sco_cmd( '~' , array( 3 , 344 ) );
sco_cmd( '~' , array( 2 , 32 ) );
sco_cmd( 'jump' , array( 12 , 0 ) );

// 3,344 = callfunc
sco_cmd( 'WW' , array( ( 640 * 2 ) , ( 480 * 14 ) , 16 ) );
sco_cmd( 'WV' , array( 0 , 0 , 640 , 480 ) );
sco_cmd( 'PS' , array( 10 , 192 , 192 , 192 ) );
sco_cmd( 'PS' , array( 11 , 255 , 0 , 0 ) );
sco_cmd( 'PS' , array( 12 , 255 , 128 , 255 ) );
sco_cmd( 'PS' , array( 13 , 0 , 128 , 255 ) );
sco_cmd( 'PS' , array( 14 , 0 , 0 , 0 ) );
sco_cmd( 'PS' , array( 15 , 255 , 255 , 255 ) );
sco_cmd( 'B_1' , array( 1 , 230 , 190 , 192 , 192 , 1 ) );
sco_cmd( 'B_3' , array( 2 , 20 , 200 , 600 , 260 , 0 ) );
sco_cmd( 'B_2' , array( 1 , 0 , 0 , 0 , 1 , 0 ) );
sco_cmd( 'B_4' , array( 2 , 0 , 0 , 0 , 1 , 0 ) );
sco_cmd( 'MT' , array( sco_msg( 1 ) ) );
sco_cmd( 'MV' , array( 100 ) );
sco_cmd( 'ZC' , array( 1 , 14 ) );
sco_cmd( 'ZC' , array( 2 , 15 ) );
sco_cmd( 'ZC' , array( 3 , 15 ) );
sco_cmd( 'ZC' , array( 5 , 15 ) );
sco_cmd( 'ZC' , array( 7 , 15 ) );
sco_cmd( 'ZC' , array( 4 , 10 ) );
sco_cmd( 'ZC' , array( 6 , 10 ) );
sco_cmd( 'ZC' , array( 14 , 0 ) );
sco_cmd( 'ZE' , array( 0 ) );
sco_cmd( 'ZF' , array( 0 ) );
sco_cmd( 'ZS' , array( 16 ) );
sco_cmd( 'ZM' , array( 16 ) );
sco_cmd( 'ZH' , array( 2 ) );
sco_cmd( 'IY' , array( 2 ) );
sco_cmd( 'ZI' , array( 0 , 0 ) );
sco_cmd( 'ZI' , array( 1 , 0 ) );
sco_cmd( 'ZI' , array( 2 , 0 ) );
sco_cmd( 'ZI' , array( 3 , 0 ) );
sco_cmd( 'ZI' , array( 4 , 0 ) );
sco_cmd( 'ZI' , array( 5 , 0 ) );
sco_cmd( 'ZI' , array( 6 , 0 ) );
sco_cmd( 'ZI' , array( 7 , 0 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 2,32 = callfunc
$gp_pc["var"][11] = 0;
$gp_pc["var"][12] = 0;
$gp_pc["var"][13] = 0;
$gp_pc["var"][14] = 0;
$gp_pc["var"][15] = 0;
$gp_pc["var"][16] = 0;
$gp_pc["var"][17] = 0;
$gp_pc["var"][18] = 0;
$gp_pc["var"][19] = 0;
$gp_pc["var"][20] = 0;
$gp_pc["var"][21] = 0;
$gp_pc["var"][22] = 0;
$gp_pc["var"][23] = 0;
$gp_pc["var"][24] = 0;
$gp_pc["var"][25] = 0;
$gp_pc["var"][26] = 0;
$gp_pc["var"][27] = 0;
$gp_pc["var"][28] = 0;
$gp_pc["var"][29] = 0;
$gp_pc["var"][30] = 0;
$gp_pc["var"][31] = 0;
$gp_pc["var"][32] = 0;
$gp_pc["var"][33] = 0;
$gp_pc["var"][34] = 0;
$gp_pc["var"][35] = 0;
$gp_pc["var"][36] = 0;
$gp_pc["var"][37] = 0;
$gp_pc["var"][38] = 0;
$gp_pc["var"][39] = 0;
$gp_pc["var"][40] = 0;
$gp_pc["var"][41] = 0;
$gp_pc["var"][42] = 0;
$gp_pc["var"][43] = 0;
$gp_pc["var"][44] = 0;
$gp_pc["var"][45] = 0;
$gp_pc["var"][46] = 0;
$gp_pc["var"][47] = 0;
$gp_pc["var"][48] = 0;
$gp_pc["var"][49] = 0;
$gp_pc["var"][50] = 0;
$gp_pc["var"][51] = 0;
$gp_pc["var"][52] = 0;
$gp_pc["var"][53] = 0;
$gp_pc["var"][54] = 0;
$gp_pc["var"][55] = 0;
$gp_pc["var"][56] = 0;
$gp_pc["var"][57] = 0;
$gp_pc["var"][58] = 0;
$gp_pc["var"][59] = 0;
$gp_pc["var"][60] = 0;
$gp_pc["var"][61] = 0;
$gp_pc["var"][62] = 0;
$gp_pc["var"][63] = 0;
$gp_pc["var"][64] = 0;
$gp_pc["var"][65] = 0;
$gp_pc["var"][66] = 0;
$gp_pc["var"][67] = 0;
$gp_pc["var"][68] = 0;
$gp_pc["var"][69] = 0;
$gp_pc["var"][70] = 0;
$gp_pc["var"][71] = 0;
$gp_pc["var"][72] = 0;
$gp_pc["var"][73] = 0;
$gp_pc["var"][74] = 0;
$gp_pc["var"][75] = 0;
$gp_pc["var"][76] = 0;
$gp_pc["var"][77] = 0;
$gp_pc["var"][78] = 0;
$gp_pc["var"][79] = 0;
$gp_pc["var"][80] = 0;
$gp_pc["var"][81] = 0;
$gp_pc["var"][82] = 0;
$gp_pc["var"][83] = 0;
$gp_pc["var"][84] = 0;
$gp_pc["var"][85] = 0;
$gp_pc["var"][86] = 0;
$gp_pc["var"][87] = 0;
$gp_pc["var"][88] = 0;
$gp_pc["var"][89] = 0;
$gp_pc["var"][90] = 0;
$gp_pc["var"][91] = 0;
$gp_pc["var"][92] = 0;
$gp_pc["var"][93] = 0;
$gp_pc["var"][94] = 0;
$gp_pc["var"][95] = 0;
$gp_pc["var"][96] = 0;
$gp_pc["var"][97] = 0;
$gp_pc["var"][98] = 0;
$gp_pc["var"][99] = 0;
$gp_pc["var"][100] = 0;
$gp_pc["var"][101] = 0;
$gp_pc["var"][102] = 0;
$gp_pc["var"][103] = 0;
$gp_pc["var"][104] = 0;
$gp_pc["var"][105] = 0;
$gp_pc["var"][106] = 0;
$gp_pc["var"][107] = 0;
$gp_pc["var"][108] = 0;
$gp_pc["var"][109] = 0;
$gp_pc["var"][110] = 0;
$gp_pc["var"][111] = 0;
$gp_pc["var"][112] = 0;
$gp_pc["var"][113] = 0;
$gp_pc["var"][114] = 0;
$gp_pc["var"][115] = 0;
$gp_pc["var"][116] = 0;
$gp_pc["var"][117] = 0;
$gp_pc["var"][118] = 0;
$gp_pc["var"][119] = 0;
$gp_pc["var"][120] = 0;
$gp_pc["var"][121] = 0;
$gp_pc["var"][122] = 0;
$gp_pc["var"][123] = 0;
$gp_pc["var"][124] = 0;
$gp_pc["var"][125] = 0;
$gp_pc["var"][126] = 0;
$gp_pc["var"][127] = 0;
$gp_pc["var"][128] = 0;
$gp_pc["var"][129] = 0;
$gp_pc["var"][130] = 0;
$gp_pc["var"][131] = 0;
$gp_pc["var"][132] = 0;
$gp_pc["var"][133] = 0;
$gp_pc["var"][134] = 0;
$gp_pc["var"][135] = 0;
$gp_pc["var"][136] = 0;
$gp_pc["var"][137] = 0;
$gp_pc["var"][138] = 0;
$gp_pc["var"][139] = 0;
$gp_pc["var"][140] = 0;
$gp_pc["var"][141] = 0;
$gp_pc["var"][142] = 0;
$gp_pc["var"][143] = 0;
$gp_pc["var"][144] = 0;
$gp_pc["var"][145] = 0;
$gp_pc["var"][146] = 0;
$gp_pc["var"][147] = 0;
$gp_pc["var"][148] = 0;
$gp_pc["var"][149] = 0;
$gp_pc["var"][150] = 0;
$gp_pc["var"][151] = 0;
$gp_pc["var"][152] = 0;
$gp_pc["var"][153] = 0;
$gp_pc["var"][154] = 0;
$gp_pc["var"][155] = 0;
$gp_pc["var"][156] = 0;
$gp_pc["var"][157] = 0;
$gp_pc["var"][158] = 0;
$gp_pc["var"][159] = 0;
$gp_pc["var"][160] = 0;
$gp_pc["var"][161] = 0;
$gp_pc["var"][162] = 0;
$gp_pc["var"][163] = 0;
$gp_pc["var"][164] = 0;
$gp_pc["var"][165] = 0;
$gp_pc["var"][166] = 0;
$gp_pc["var"][167] = 0;
$gp_pc["var"][168] = 0;
$gp_pc["var"][169] = 0;
$gp_pc["var"][170] = 10000;
sco_cmd( 'DC' , array( 1 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 2 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 3 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 4 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 5 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 6 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 7 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 8 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 9 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DC' , array( 10 , &$gp_pc["var"][170] , 1 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][160] , 0 , 1 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][161] , 0 , 2 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][162] , 0 , 3 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][163] , 0 , 4 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][164] , 0 , 5 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][165] , 0 , 6 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][168] , 0 , 7 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][169] , 0 , 8 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][166] , 0 , 9 ) );
sco_cmd( 'DS' , array( &$gp_pc["var"][157] , &$gp_pc["var"][167] , 0 , 10 ) );
$gp_pc["var"][171] = 0;
$gp_pc["var"][172] = 0;
$gp_pc["var"][173] = 0;
$gp_pc["var"][174] = 0;
sco_cmd( '~' , array( 0 , 0 ) );

// 12,0 = jumppage

sco_cmd( 'ZD_0' , array( 0 ) );
sco_cmd( '~' , array( 3 , 48 ) );
sco_cmd( '~' , array( 9 , 48 ) );
sco_cmd( '~' , array( 7 , 1582 ) );
sco_cmd( '~' , array( 11 , 48 ) );
sco_cmd( '~' , array( 11 , 1205 ) );
$gp_pc["var"][11] = 0;
sco_cmd( '~' , array( 7 , 1856 ) );
$gp_pc["var"][11] = 5000;
sco_cmd( '~' , array( 7 , 1813 ) );
$gp_pc["var"][155] = 1;
sco_cmd( '~' , array( 7 , 1536 ) );
sco_cmd( '~' , array( 11 , 1219 ) );
sco_cmd( '~' , array( 5 , 32 ) );
sco_cmd( 'sel_st' , array( 282 ) );
sco_cmd( 'msg' , array( sco_msg( 4 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
sco_cmd( 'sel_st' , array( 287 ) );
sco_cmd( 'msg' , array( sco_msg( 5 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
if ( ! ( ( $gp_pc["var"][172] != 0 ) + ( $gp_pc["var"][173] != 0 ) ) )  $gp_pc["pc"][1] = 273;
sco_cmd( 'sel_st' , array( 299 ) );
sco_cmd( 'msg' , array( sco_msg( 6 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
$gp_pc["pc"][1] = 273;
sco_cmd( '~' , array( 7 , 48 ) );
sco_cmd( 'AIN_71' , array(  ) );
sco_cmd( ']' , array(  ) );
$gp_pc["var"][11] = 3;
sco_cmd( '~' , array( 7 , 1651 ) );
sco_cmd( '~' , array( 7 , 517 ) );
sco_cmd( '~' , array( 5 , 32 ) );
sco_cmd( '~' , array( 7 , 902 ) );
$gp_pc["pc"][1] = 261;
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
sco_cmd( '~' , array( 7 , 517 ) );
$gp_pc["var"][171] = 0;
sco_cmd( 'jump' , array( 13 , 0 ) );
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["var"][11] = 2000;
sco_cmd( '~' , array( 7 , 1898 ) );
sco_cmd( '~' , array( 7 , 517 ) );
$gp_pc["var"][171] = 0;
sco_cmd( '~' , array( 10 , 242 ) );
$gp_pc["var"][155] = 1;
sco_cmd( '~' , array( 7 , 1536 ) );
$gp_pc["var"][11] = 1000;
sco_cmd( '~' , array( 7 , 1813 ) );
$gp_pc["pc"][1] = 261;
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["var"][11] = 2000;
sco_cmd( '~' , array( 7 , 1856 ) );
sco_cmd( '~' , array( 7 , 517 ) );
$gp_pc["var"][171] = 1;
sco_cmd( 'jump' , array( 13 , 0 ) );

// 3,48 = callfunc
$gp_pc["var"][31] = 1;
$gp_pc["var"][32] = 0;
$gp_pc["var"][59] = 0;
$gp_pc["var"][60] = 0;
$gp_pc["var"][61] = 1;
$gp_pc["var"][75] = 640;
$gp_pc["var"][76] = 0;
$gp_pc["var"][77] = 0;
$gp_pc["var"][78] = 480;
$gp_pc["var"][79] = 0;
$gp_pc["var"][80] = 0;
$gp_pc["var"][81] = 640;
$gp_pc["var"][82] = 480;
$gp_pc["var"][91] = $gp_pc["var"][77];
$gp_pc["var"][92] = ( $gp_pc["var"][78] + 480 );
$gp_pc["var"][95] = $gp_pc["var"][91];
$gp_pc["var"][96] = ( $gp_pc["var"][92] + ( 480 * 1 ) );
$gp_pc["var"][99] = $gp_pc["var"][91];
$gp_pc["var"][100] = ( $gp_pc["var"][92] + ( 480 * 2 ) );
$gp_pc["var"][83] = ( $gp_pc["var"][91] + 640 );
$gp_pc["var"][84] = ( $gp_pc["var"][92] + 480 );
$gp_pc["var"][87] = $gp_pc["var"][83];
$gp_pc["var"][88] = ( $gp_pc["var"][84] + 480 );
$gp_pc["var"][175] = $gp_pc["var"][91];
$gp_pc["var"][176] = $gp_pc["var"][92];
$gp_pc["var"][177] = $gp_pc["var"][95];
$gp_pc["var"][178] = $gp_pc["var"][96];
$gp_pc["var"][179] = $gp_pc["var"][99];
$gp_pc["var"][180] = $gp_pc["var"][100];
$gp_pc["var"][181] = $gp_pc["var"][83];
$gp_pc["var"][182] = $gp_pc["var"][84];
$gp_pc["var"][183] = $gp_pc["var"][87];
$gp_pc["var"][184] = $gp_pc["var"][88];
$gp_pc["var"][115] = 0;
$gp_pc["var"][116] = 0;
$gp_pc["var"][117] = 0;
$gp_pc["var"][118] = 0;
$gp_pc["var"][119] = 0;
$gp_pc["var"][120] = 9999;
$gp_pc["var"][121] = 9999;
$gp_pc["var"][122] = 9999;
$gp_pc["var"][123] = 9999;
$gp_pc["var"][124] = 9999;
$gp_pc["var"][130] = 1;
$gp_pc["var"][154] = 10;
$gp_pc["var"][156] = 1;
$gp_pc["var"][153] = ( 16383 + ( 16383 + ( 16383 + 10861 ) ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 9,48 = callfunc
sco_cmd( 'ZZ_13' , array( &$gp_pc["var"][61] ) );
$gp_pc["var"][54] = 40;
$gp_pc["var"][149] = 1;
if ( ! ( $gp_pc["var"][149] == 0 ) )  $gp_pc["pc"][1] = 365;
$gp_pc["var"][11] = 0;
sco_cmd( '~' , array( 9 , 1929 ) );
$gp_pc["pc"][1] = 365;
sco_cmd( '~' , array( 8 , 48 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 9,1929 = callfunc
sco_cmd( 'ZT_10' , array( 100 , 10 , &$gp_pc["var"][11] ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 8,48 = callfunc
$gp_pc["var"][55] = $gp_pc["var"][54];
sco_cmd( 'MG_100' , array( 0 ) );
sco_cmd( 'MG_1' , array( 0 ) );
sco_cmd( 'MG_0' , array( 1 ) );
sco_cmd( 'MG_4' , array( &$gp_pc["var"][55] ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,1582 = callfunc
$gp_pc["var"][45] = 0;

if ( $gp_pc["var"][45] > 2 )  $gp_pc["pc"][1] = 386;
sco_cmd( 'wavLoad' , array( &$gp_pc["var"][45] , ( $gp_pc["var"][45] + 1 ) ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 383;
$gp_pc["var"][45] = 10;

if ( $gp_pc["var"][45] > 19 )  $gp_pc["pc"][1] = 391;
sco_cmd( 'wavLoad' , array( &$gp_pc["var"][45] , 1 ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 388;
sco_cmd( '~' , array( 0 , 0 ) );

// 11,48 = callfunc
$gp_pc["var"][14] = 17;
sco_cmd( '~' , array( 11 , 276 ) );
$gp_pc["var"][83] = 90;
$gp_pc["var"][84] = 110;
sco_cmd( '~' , array( 8 , 1597 ) );
$gp_pc["var"][91] = 0;
$gp_pc["var"][92] = 0;
sco_cmd( '~' , array( 8 , 1626 ) );
$gp_pc["var"][95] = 0;
$gp_pc["var"][96] = 0;
sco_cmd( '~' , array( 8 , 1655 ) );
$gp_pc["var"][99] = 0;
$gp_pc["var"][100] = 0;
sco_cmd( '~' , array( 8 , 1684 ) );
sco_cmd( 'MT' , array( sco_msg( 2 ) ) );
sco_cmd( 'MV' , array( 100 ) );
$gp_pc["var"][57] = 55;
$gp_pc["var"][58] = 30;
sco_cmd( '~' , array( 8 , 712 ) );
$gp_pc["var"][62] = 20;
sco_cmd( '~' , array( 8 , 119 ) );
$gp_pc["var"][56] = 33;
sco_cmd( '~' , array( 8 , 707 ) );
$gp_pc["var"][11] = 22;
sco_cmd( '~' , array( 9 , 1879 ) );
$gp_pc["var"][150] = 8;
sco_cmd( '~' , array( 7 , 820 ) );
$gp_pc["var"][151] = 10;
$gp_pc["var"][152] = 0;
sco_cmd( '~' , array( 7 , 850 ) );
$gp_pc["var"][145] = 80;
sco_cmd( '~' , array( 8 , 137 ) );
$gp_pc["var"][208] = 10;
$gp_pc["var"][209] = 5;
$gp_pc["var"][210] = 4;
$gp_pc["var"][211] = 121;
$gp_pc["var"][140] = 122;
$gp_pc["var"][141] = 123;
$gp_pc["var"][142] = 124;
sco_cmd( '~' , array( 0 , 0 ) );

// 11,276 = callfunc
sco_cmd( '#' , array( 11 , 2134900736 ) );
$gp_pc["var"][45] = 128;

if ( $gp_pc["var"][45] > ( 128 + $gp_pc["var"][14] ) )  $gp_pc["pc"][1] = 445;
sco_cmd( 'F_2' , array( &$gp_pc["var"][11] , 0 ) );
sco_cmd( 'F_2' , array( &$gp_pc["var"][12] , 0 ) );
sco_cmd( 'F_2' , array( &$gp_pc["var"][13] , 0 ) );
sco_cmd( 'PS' , array( &$gp_pc["var"][45] , &$gp_pc["var"][11] , &$gp_pc["var"][12] , &$gp_pc["var"][13] ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 439;
sco_cmd( '~' , array( 0 , 0 ) );

// 8,1597 = callfunc
$gp_pc["var"][103] = $gp_pc["var"][83];
$gp_pc["var"][104] = $gp_pc["var"][84];

// 8,1626 = callfunc
$gp_pc["var"][107] = $gp_pc["var"][91];
$gp_pc["var"][108] = $gp_pc["var"][92];

// 8,1655 = callfunc
$gp_pc["var"][109] = $gp_pc["var"][95];
$gp_pc["var"][110] = $gp_pc["var"][96];

// 8,1684 = callfunc
$gp_pc["var"][111] = $gp_pc["var"][99];
$gp_pc["var"][112] = $gp_pc["var"][100];

// 8,712 = callfunc
sco_cmd( '~' , array( 0 , 0 ) );

// 8,119 = callfunc
sco_cmd( '~' , array( 0 , 0 ) );

// 8,707 = callfunc
sco_cmd( '~' , array( 0 , 0 ) );

// 9,1879 = callfunc
$gp_pc["var"][12] = $gp_pc["var"][65];
$gp_pc["var"][65] = $gp_pc["var"][11];
sco_cmd( '~' , array( 0 , &$gp_pc["var"][12] ) );

// 7,820 = callfunc
sco_cmd( 'ZC' , array( 1 , ( 128 + $gp_pc["var"][150] ) ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,850 = callfunc
sco_cmd( '~' , array( 7 , 862 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,862 = callfunc
sco_cmd( 'ZA_0' , array( &$gp_pc["var"][151] ) );
sco_cmd( 'ZA_1' , array( ( 128 + $gp_pc["var"][152] ) ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 8,137 = callfunc
sco_cmd( '~' , array( 0 , 0 ) );

// 11,1205 = callfunc
sco_cmd( 'LP' , array( 26 , &$gp_pc["var"][172] , 2 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,1856 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 501;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 501;
sco_cmd( '~' , array( 7 , 2033 ) );
sco_cmd( 'SX_2_1' , array( &$gp_pc["var"][11] , 0 , 0 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,2033 = callfunc
sco_cmd( 'SX_2_2' , array( &$gp_pc["var"][15] ) );
if ( ! ( $gp_pc["var"][15] == 0 ) )  $gp_pc["pc"][1] = 510;
sco_cmd( 'SX_2_3' , array(  ) );
$gp_pc["pc"][1] = 510;
sco_cmd( '~' , array( 0 , 0 ) );

// 7,1813 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 516;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 516;
sco_cmd( '~' , array( 7 , 2033 ) );
sco_cmd( 'SX_2_1' , array( &$gp_pc["var"][11] , 100 , 0 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,1536 = callfunc
if ( ! ( $gp_pc["var"][155] == 0 ) )  $gp_pc["pc"][1] = 524;
sco_cmd( 'SG_0' , array( 0 ) );
$gp_pc["pc"][1] = 524;
if ( ! ( $gp_pc["var"][155] != 0 ) )  $gp_pc["pc"][1] = 527;
sco_cmd( 'SG_1' , array( &$gp_pc["var"][155] ) );
$gp_pc["pc"][1] = 527;
sco_cmd( '~' , array( 0 , 0 ) );

// 11,1219 = callfunc
$gp_pc["var"][116] = $gp_pc["var"][209];
sco_cmd( '~' , array( 8 , 717 ) );
$gp_pc["var"][11] = 27;
$gp_pc["var"][12] = 100;
$gp_pc["var"][13] = 100;
sco_cmd( '~' , array( 9 , 187 ) );
if ( ! ( $gp_pc["var"][0] == 0 ) )  $gp_pc["pc"][1] = 541;
sco_cmd( '~' , array( 7 , 902 ) );
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 541;
sco_cmd( '~' , array( 0 , 0 ) );

// 8,717 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 547;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 547;
if ( ! ( $gp_pc["var"][121] != $gp_pc["var"][116] ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][121] = $gp_pc["var"][116];
if ( ! ( $gp_pc["var"][116] == 0 ) )  $gp_pc["pc"][1] = 0;
sco_cmd( 'trace' , array( sco_msg( 3 ) ) );
$gp_pc["var"][89] = $gp_pc["var"][81];
$gp_pc["var"][90] = $gp_pc["var"][82];

// 9,187 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 558;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 558;
sco_cmd( 'US' , array( &$gp_pc["var"][11] , 10 ) );
$gp_pc["var"][11] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
sco_cmd( '~' , array( 8 , 142 ) );
$gp_pc["var"][11] = $gp_pc["var"][13];
sco_cmd( '~' , array( 8 , 400 ) );
sco_cmd( 'UG' , array( &$gp_pc["var"][11] , 10 ) );
$gp_pc["var"][11] = $gp_pc["var"][11];
$gp_pc["var"][12] = $gp_pc["var"][12];
sco_cmd( '~' , array( 8 , 583 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][0] ) );
sco_cmd( '~' , array( 0 , &$gp_pc["var"][0] ) );

// 8,142 = callfunc
sco_cmd( 'CF' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 ) );
if ( ! ( ( $gp_pc["var"][11] & 1 ) * ( $gp_pc["var"][116] != 0 ) ) )  $gp_pc["pc"][1] = 575;
sco_cmd( 'CC' , array( &$gp_pc["var"][87] , &$gp_pc["var"][88] , &$gp_pc["var"][89] , &$gp_pc["var"][90] , ( $gp_pc["var"][77] + $gp_pc["var"][105] ) , ( $gp_pc["var"][78] + $gp_pc["var"][106] ) ) );
$gp_pc["pc"][1] = 575;
if ( ! ( ( $gp_pc["var"][11] & 2 ) * ( $gp_pc["var"][115] != 0 ) ) )  $gp_pc["pc"][1] = 578;
sco_cmd( 'CC' , array( &$gp_pc["var"][83] , &$gp_pc["var"][84] , &$gp_pc["var"][85] , &$gp_pc["var"][86] , ( $gp_pc["var"][77] + $gp_pc["var"][103] ) , ( $gp_pc["var"][78] + $gp_pc["var"][104] ) ) );
$gp_pc["pc"][1] = 578;
if ( ! ( ( $gp_pc["var"][11] & 4 ) * ( $gp_pc["var"][117] != 0 ) ) )  $gp_pc["pc"][1] = 581;
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][91] , &$gp_pc["var"][92] , &$gp_pc["var"][93] , &$gp_pc["var"][94] , ( $gp_pc["var"][77] + $gp_pc["var"][107] ) , ( $gp_pc["var"][78] + $gp_pc["var"][108] ) , 0 ) );
$gp_pc["pc"][1] = 581;
if ( ! ( ( $gp_pc["var"][11] & 8 ) * ( $gp_pc["var"][118] != 0 ) ) )  $gp_pc["pc"][1] = 584;
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][95] , &$gp_pc["var"][96] , &$gp_pc["var"][97] , &$gp_pc["var"][98] , ( $gp_pc["var"][77] + $gp_pc["var"][109] ) , ( $gp_pc["var"][78] + $gp_pc["var"][110] ) , 0 ) );
$gp_pc["pc"][1] = 584;
if ( ! ( ( $gp_pc["var"][11] & 16 ) * ( $gp_pc["var"][119] != 0 ) ) )  $gp_pc["pc"][1] = 587;
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][99] , &$gp_pc["var"][100] , &$gp_pc["var"][101] , &$gp_pc["var"][102] , ( $gp_pc["var"][77] + $gp_pc["var"][111] ) , ( $gp_pc["var"][78] + $gp_pc["var"][112] ) , 0 ) );
$gp_pc["pc"][1] = 587;
sco_cmd( '~' , array( 0 , 0 ) );

// 8,400 = callfunc
if ( ! ( $gp_pc["var"][11] < 100 ) )  $gp_pc["pc"][1] = 593;
sco_cmd( 'CK_1' , array( 0 , 480 , 640 , 480 , 0 , ( 255 - ( ( 256 * $gp_pc["var"][11] ) / 100 ) ) , 0 , 0 ) );
$gp_pc["pc"][1] = 593;
sco_cmd( '~' , array( 0 , 0 ) );

// 8,583 = callfunc
$gp_pc["var"][0] = 0;
if ( ! ( 100 + $gp_pc["var"][-4] ) )  $gp_pc["pc"][1] = 600;
sco_cmd( 'CC' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 , 0 ) );
$gp_pc["pc"][1] = 600;
if ( ! ( 99 * $gp_pc["var"][-3] ) )  $gp_pc["pc"][1] = 606;
sco_cmd( 'CE' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 , 0 , &$gp_pc["var"][11] , &$gp_pc["var"][12] , 1 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 605;
sco_cmd( 'CC' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 , 0 ) );
$gp_pc["pc"][1] = 605;
$gp_pc["pc"][1] = 606;
sco_cmd( '~' , array( 0 , &$gp_pc["var"][0] ) );

// 7,902 = callfunc
$gp_pc["var"][37] = 430;
$gp_pc["var"][38] = 430;
$gp_pc["var"][35] = 180;
$gp_pc["var"][36] = 30;
sco_cmd( 'CC' , array( 0 , 0 , 640 , 480 , &$gp_pc["var"][75] , &$gp_pc["var"][76] ) );
sco_cmd( 'CC' , array( &$gp_pc["var"][75] , &$gp_pc["var"][76] , 640 , 480 , &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 480 ) ) );
sco_cmd( 'CF' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , 640 , 480 , 0 ) );
sco_cmd( 'J_0' , array( &$gp_pc["var"][77] , ( $gp_pc["var"][78] + $gp_pc["var"][36] ) ) );
sco_cmd( 'G_0' , array( &$gp_pc["var"][210] ) );
sco_cmd( 'CC' , array( ( $gp_pc["var"][75] + $gp_pc["var"][37] ) , ( ( $gp_pc["var"][76] + $gp_pc["var"][38] ) + 480 ) , &$gp_pc["var"][35] , &$gp_pc["var"][36] , &$gp_pc["var"][77] , &$gp_pc["var"][78] ) );
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][77] , ( $gp_pc["var"][78] + $gp_pc["var"][36] ) , &$gp_pc["var"][35] , &$gp_pc["var"][36] , &$gp_pc["var"][77] , &$gp_pc["var"][78] , 0 ) );
$gp_pc["var"][53] = 1;
if ( ! $gp_pc["var"][-4] )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][45] = 0;
if ( ! ( $gp_pc["var"][53] == 1 ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][0] = 0;
sco_cmd( 'ZT_21' , array( 10 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][53] = 0;
$gp_pc["pc"][1] = 0;

// 7,1651 = callfunc
if ( ! ( $gp_pc["var"][11] == 1 ) )  $gp_pc["pc"][1] = 638;
sco_cmd( 'wavPlay' , array( &$gp_pc["var"][154] , 1 ) );
sco_cmd( 'inc' , array( &$gp_pc["var"][154] ) );
if ( ! ( $gp_pc["var"][154] > 19 ) )  $gp_pc["pc"][1] = 637;
$gp_pc["var"][154] = 10;
$gp_pc["pc"][1] = 637;
$gp_pc["pc"][1] = 639;
sco_cmd( 'wavPlay' , array( ( $gp_pc["var"][11] - 1 ) , 1 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 5,32 = callfunc
sco_cmd( 'US' , array( &$gp_pc["var"][0] , 1 ) );
sco_cmd( 'IK_6' , array(  ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 647;
sco_cmd( 'IK_6' , array(  ) );
$gp_pc["pc"][1] = 644;
sco_cmd( '~' , array( 0 , 0 ) );

// 7,48 = callfunc
$gp_pc["var"][199] = 335;
$gp_pc["var"][200] = 20;
$gp_pc["var"][201] = 22;
$gp_pc["var"][202] = 15;
$gp_pc["var"][203] = 15;
$gp_pc["var"][204] = 12;
$gp_pc["var"][205] = 4;
$gp_pc["var"][33] = $gp_pc["var"][202];
$gp_pc["var"][34] = $gp_pc["var"][203];
$gp_pc["var"][11] = $gp_pc["var"][204];
$gp_pc["var"][12] = $gp_pc["var"][205];
$gp_pc["var"][13] = $gp_pc["var"][200];
$gp_pc["var"][14] = $gp_pc["var"][201];
$gp_pc["var"][19] = $gp_pc["var"][199];
sco_cmd( 'B_12' , array( &$gp_pc["var"][59] ) );
sco_cmd( 'B_14' , array( &$gp_pc["var"][60] ) );
$gp_pc["var"][35] = ( ( $gp_pc["var"][60] * $gp_pc["var"][200] ) / 2 );
$gp_pc["var"][36] = ( ( $gp_pc["var"][59] * $gp_pc["var"][201] ) + ( $gp_pc["var"][205] * 2 ) );
sco_cmd( 'B_21' , array( 1 , &$gp_pc["var"][37] , &$gp_pc["var"][38] ) );
$gp_pc["var"][37] = ( ( 640 - $gp_pc["var"][35] ) / 2 );
$gp_pc["var"][38] = ( ( 480 - $gp_pc["var"][36] ) / 2 );
sco_cmd( 'CC' , array( ( ( $gp_pc["var"][37] - $gp_pc["var"][33] ) - $gp_pc["var"][11] ) , ( ( $gp_pc["var"][38] - $gp_pc["var"][34] ) - $gp_pc["var"][12] ) , ( ( $gp_pc["var"][35] + ( $gp_pc["var"][33] * 2 ) ) + ( $gp_pc["var"][11] * 2 ) ) , ( ( $gp_pc["var"][36] + ( $gp_pc["var"][34] * 2 ) ) + ( $gp_pc["var"][12] * 2 ) ) , &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 100 ) ) );
sco_cmd( 'J_0' , array( &$gp_pc["var"][75] , &$gp_pc["var"][76] ) );
sco_cmd( 'G_0' , array( &$gp_pc["var"][208] ) );
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][75] , &$gp_pc["var"][76] , ( ( $gp_pc["var"][33] + $gp_pc["var"][35] ) + ( $gp_pc["var"][11] * 2 ) ) , ( $gp_pc["var"][34] + $gp_pc["var"][12] ) , ( ( $gp_pc["var"][37] - $gp_pc["var"][34] ) - $gp_pc["var"][11] ) , ( ( $gp_pc["var"][38] - $gp_pc["var"][34] ) - $gp_pc["var"][12] ) , 0 ) );
sco_cmd( 'CX' , array( 0 , ( $gp_pc["var"][75] + $gp_pc["var"][19] ) , &$gp_pc["var"][76] , &$gp_pc["var"][33] , ( $gp_pc["var"][34] + $gp_pc["var"][12] ) , ( ( $gp_pc["var"][37] + $gp_pc["var"][35] ) + $gp_pc["var"][11] ) , ( ( $gp_pc["var"][38] - $gp_pc["var"][34] ) - $gp_pc["var"][12] ) , 0 ) );
$gp_pc["var"][45] = 1;

if ( $gp_pc["var"][45] > $gp_pc["var"][59] )  $gp_pc["pc"][1] = 682;
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][75] , ( $gp_pc["var"][76] + $gp_pc["var"][33] ) , ( ( $gp_pc["var"][33] + $gp_pc["var"][35] ) + ( $gp_pc["var"][11] * 2 ) ) , &$gp_pc["var"][14] , ( ( $gp_pc["var"][37] - $gp_pc["var"][33] ) - $gp_pc["var"][11] ) , ( $gp_pc["var"][38] + ( $gp_pc["var"][14] * ( $gp_pc["var"][45] - 1 ) ) ) , 0 ) );
sco_cmd( 'CX' , array( 0 , ( $gp_pc["var"][75] + $gp_pc["var"][19] ) , ( $gp_pc["var"][76] + $gp_pc["var"][34] ) , &$gp_pc["var"][33] , &$gp_pc["var"][14] , ( ( $gp_pc["var"][37] + $gp_pc["var"][35] ) + $gp_pc["var"][11] ) , ( $gp_pc["var"][38] + ( $gp_pc["var"][14] * ( $gp_pc["var"][45] - 1 ) ) ) , 0 ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 678;
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][75] , ( ( ( $gp_pc["var"][76] + $gp_pc["var"][34] ) + $gp_pc["var"][14] ) - $gp_pc["var"][12] ) , ( ( $gp_pc["var"][33] + $gp_pc["var"][35] ) + ( $gp_pc["var"][11] * 2 ) ) , ( $gp_pc["var"][34] + $gp_pc["var"][12] ) , ( ( $gp_pc["var"][37] - $gp_pc["var"][33] ) - $gp_pc["var"][11] ) , ( $gp_pc["var"][38] + ( $gp_pc["var"][14] * ( $gp_pc["var"][45] - 1 ) ) ) , 0 ) );
sco_cmd( 'CX' , array( 0 , ( $gp_pc["var"][75] + $gp_pc["var"][19] ) , ( ( ( $gp_pc["var"][76] + $gp_pc["var"][34] ) + $gp_pc["var"][14] ) - $gp_pc["var"][12] ) , &$gp_pc["var"][33] , ( $gp_pc["var"][34] + $gp_pc["var"][12] ) , ( ( $gp_pc["var"][37] + $gp_pc["var"][35] ) + $gp_pc["var"][11] ) , ( $gp_pc["var"][38] + ( $gp_pc["var"][14] * ( $gp_pc["var"][45] - 1 ) ) ) , 0 ) );
sco_cmd( 'B_1' , array( 1 , &$gp_pc["var"][37] , &$gp_pc["var"][38] , &$gp_pc["var"][35] , &$gp_pc["var"][36] , 1 ) );
sco_cmd( 'B_2' , array( 1 , 0 , 0 , 0 , 1 , 0 ) );
sco_cmd( 'ZS' , array( &$gp_pc["var"][200] ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 7,517 = callfunc
$gp_pc["var"][33] = $gp_pc["var"][202];
$gp_pc["var"][34] = $gp_pc["var"][203];
$gp_pc["var"][11] = $gp_pc["var"][204];
$gp_pc["var"][12] = $gp_pc["var"][205];
$gp_pc["var"][13] = $gp_pc["var"][200];
$gp_pc["var"][14] = $gp_pc["var"][201];
$gp_pc["var"][35] = ( ( $gp_pc["var"][60] * $gp_pc["var"][200] ) / 2 );
$gp_pc["var"][36] = ( ( $gp_pc["var"][59] * $gp_pc["var"][201] ) + ( $gp_pc["var"][205] * 2 ) );
sco_cmd( 'B_21' , array( 1 , &$gp_pc["var"][37] , &$gp_pc["var"][38] ) );
sco_cmd( 'CC' , array( &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 100 ) , ( ( $gp_pc["var"][35] + ( $gp_pc["var"][33] * 2 ) ) + ( $gp_pc["var"][11] * 2 ) ) , ( ( $gp_pc["var"][36] + ( $gp_pc["var"][34] * 2 ) ) + ( $gp_pc["var"][12] * 2 ) ) , ( ( $gp_pc["var"][37] - $gp_pc["var"][33] ) - $gp_pc["var"][11] ) , ( ( $gp_pc["var"][38] - $gp_pc["var"][34] ) - $gp_pc["var"][12] ) ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 13,0 = jumppage

sco_cmd( 'ZD_0' , array( 1 ) );
sco_cmd( '~' , array( 11 , 1376 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][138] ) );
$gp_pc["var"][138] = $gp_pc["var"][138];
sco_cmd( '~' , array( 11 , 1290 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][125] ) );
if ( ! ( $gp_pc["var"][125] != 0 ) )  $gp_pc["pc"][1] = 713;
$gp_pc["pc"][1] = 714;
$gp_pc["pc"][1] = 713;
$gp_pc["pc"][1] = 705;
$gp_pc["var"][53] = 1;
if ( ! ( $gp_pc["var"][53] == 1 ) )  $gp_pc["pc"][1] = 769;
sco_cmd( 'US' , array( &$gp_pc["var"][53] , 1 ) );
$gp_pc["var"][147] = 1;
$gp_pc["var"][146] = 0;
sco_cmd( 'call' , array( 1 , 0 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][129] ) );
if ( ! ( $gp_pc["var"][129] == 0 ) )  $gp_pc["pc"][1] = 731;
$gp_pc["var"][158] = $gp_pc["var"][158];
sco_cmd( '~' , array( 13 , 561 ) );
sco_cmd( 'inc' , array( &$gp_pc["var"][126] ) );
sco_cmd( 'inc' , array( &$gp_pc["var"][158] ) );
if ( ! ( $gp_pc["var"][128] != 0 ) )  $gp_pc["pc"][1] = 730;
$gp_pc["var"][126] = $gp_pc["var"][128];
$gp_pc["var"][128] = 0;
$gp_pc["pc"][1] = 730;
$gp_pc["pc"][1] = 731;
if ( ! ( ( $gp_pc["var"][129] > 0 ) * ( $gp_pc["var"][129] < ( 16383 + ( 16383 + ( 16383 + 10851 ) ) ) ) ) )  $gp_pc["pc"][1] = 735;
$gp_pc["var"][125] = $gp_pc["var"][129];
$gp_pc["var"][126] = 1;
$gp_pc["pc"][1] = 735;
if ( ! ( $gp_pc["var"][129] == ( 16383 + ( 16383 + ( 16383 + 10852 ) ) ) ) )  $gp_pc["pc"][1] = 747;
if ( ! ( $gp_pc["var"][158] > 0 ) )  $gp_pc["pc"][1] = 746;
$gp_pc["var"][127] = $gp_pc["var"][126];
$gp_pc["var"][159] = $gp_pc["var"][158];
sco_cmd( 'dec' , array( &$gp_pc["var"][126] ) );
sco_cmd( 'dec' , array( &$gp_pc["var"][158] ) );
$gp_pc["var"][158] = $gp_pc["var"][158];
sco_cmd( '~' , array( 13 , 676 ) );
sco_cmd( '~' , array( 8 , 105 ) );
$gp_pc["var"][206] = 1;
$gp_pc["pc"][1] = 746;
$gp_pc["pc"][1] = 747;
if ( ! ( $gp_pc["var"][129] == ( 16383 + ( 16383 + ( 16383 + 10853 ) ) ) ) )  $gp_pc["pc"][1] = 749;
$gp_pc["pc"][1] = 749;
if ( ! ( $gp_pc["var"][129] == ( 16383 + ( 16383 + ( 16383 + 10854 ) ) ) ) )  $gp_pc["pc"][1] = 760;
$gp_pc["var"][115] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][116] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][117] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][118] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][119] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][158] = $gp_pc["var"][158];
sco_cmd( '~' , array( 13 , 676 ) );
sco_cmd( '~' , array( 8 , 105 ) );
$gp_pc["var"][206] = 1;
$gp_pc["pc"][1] = 760;
if ( ! ( $gp_pc["var"][129] == $gp_pc["var"][153] ) )  $gp_pc["pc"][1] = 765;
sco_cmd( 'IY' , array( 0 ) );
sco_cmd( '~' , array( 10 , 1356 ) );
sco_cmd( 'jump' , array( 12 , 0 ) );
$gp_pc["pc"][1] = 765;
sco_cmd( '~' , array( 8 , 96 ) );
sco_cmd( '~' , array( 9 , 1244 ) );
sco_cmd( 'UG' , array( &$gp_pc["var"][53] , 1 ) );
$gp_pc["pc"][1] = 715;
$gp_pc["pc"][1] = 705;

// 11,1376 = callfunc
if ( ! ( $gp_pc["var"][171] == 1 ) )  $gp_pc["pc"][1] = 775;
sco_cmd( '~' , array( 0 , 11 ) );
$gp_pc["pc"][1] = 775;
$gp_pc["var"][206] = 0;
$gp_pc["var"][116] = 6;
sco_cmd( '~' , array( 8 , 717 ) );
$gp_pc["var"][11] = 31;
$gp_pc["var"][12] = 0;
$gp_pc["var"][13] = 100;
sco_cmd( '~' , array( 9 , 187 ) );
$gp_pc["var"][53] = 1;
$gp_pc["var"][138] = 0;
$gp_pc["var"][139] = 99;
if ( ! ( $gp_pc["var"][53] == 1 ) )  $gp_pc["pc"][1] = 807;
sco_cmd( 'IM' , array( &$gp_pc["var"][33] , &$gp_pc["var"][34] ) );
$gp_pc["var"][50] = $gp_pc["var"][0];
$gp_pc["var"][33] = $gp_pc["var"][33];
$gp_pc["var"][34] = $gp_pc["var"][34];
sco_cmd( '~' , array( 11 , 1800 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][138] ) );
if ( ! ( $gp_pc["var"][138] != $gp_pc["var"][139] ) )  $gp_pc["pc"][1] = 796;
$gp_pc["var"][11] = 1;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 796;
$gp_pc["var"][138] = $gp_pc["var"][138];
$gp_pc["var"][11] = 1;
sco_cmd( '~' , array( 11 , 1889 ) );
if ( ! ( $gp_pc["var"][50] == 16 ) )  $gp_pc["pc"][1] = 804;
if ( ! ( $gp_pc["var"][138] != 0 ) )  $gp_pc["pc"][1] = 803;
$gp_pc["var"][53] = 0;
$gp_pc["pc"][1] = 803;
$gp_pc["pc"][1] = 804;
if ( ! ( $gp_pc["var"][50] == 32 ) )  $gp_pc["pc"][1] = 806;
$gp_pc["pc"][1] = 806;
$gp_pc["pc"][1] = 785;
if ( ! ( $gp_pc["var"][138] != 0 ) )  $gp_pc["pc"][1] = 811;
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 811;
if ( ! ( $gp_pc["var"][138] == 0 ) )  $gp_pc["pc"][1] = 815;
$gp_pc["var"][11] = 3;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 815;
$gp_pc["var"][45] = 1;

if ( $gp_pc["var"][45] > 3 )  $gp_pc["pc"][1] = 831;
sco_cmd( 'US' , array( &$gp_pc["var"][138] , 1 ) );
$gp_pc["var"][138] = 0;
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 11 , 1889 ) );
$gp_pc["var"][188] = 7;
sco_cmd( '~' , array( 5 , 221 ) );
sco_cmd( 'UG' , array( &$gp_pc["var"][138] , 1 ) );
$gp_pc["var"][138] = $gp_pc["var"][138];
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 11 , 1889 ) );
$gp_pc["var"][188] = 7;
sco_cmd( '~' , array( 5 , 221 ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 817;
$gp_pc["var"][11] = 2000;
sco_cmd( '~' , array( 7 , 1898 ) );
$gp_pc["var"][116] = 0;
sco_cmd( '~' , array( 8 , 717 ) );
$gp_pc["var"][11] = 31;
$gp_pc["var"][12] = 3;
$gp_pc["var"][144] = 0;
sco_cmd( '~' , array( 9 , 100 ) );
sco_cmd( '~' , array( 7 , 2091 ) );
$gp_pc["var"][11] = 0;
sco_cmd( '~' , array( 7 , 1813 ) );
sco_cmd( '~' , array( 0 , &$gp_pc["var"][138] ) );

// 11,1800 = callfunc
$gp_pc["var"][138] = 0;
if ( ! ( 176 * $gp_pc["var"][-3] ) )  $gp_pc["pc"][1] = 849;
$gp_pc["var"][138] = 1;
$gp_pc["pc"][1] = 849;
if ( ! ( 412 * $gp_pc["var"][-3] ) )  $gp_pc["pc"][1] = 852;
$gp_pc["var"][138] = 2;
$gp_pc["pc"][1] = 852;
sco_cmd( '~' , array( 0 , &$gp_pc["var"][138] ) );

// 11,1889 = callfunc
if ( ! ( ( $gp_pc["var"][139] != $gp_pc["var"][138] ) * ( $gp_pc["var"][139] != 99 ) ) )  $gp_pc["pc"][1] = 875;
if ( ! ( $gp_pc["var"][139] == 0 ) )  $gp_pc["pc"][1] = 861;
if ( ! $gp_pc["var"][-3] )  $gp_pc["pc"][1] = 860;
sco_cmd( 'CC' , array( ( $gp_pc["var"][77] + 42 ) , ( $gp_pc["var"][78] + 148 ) , 224 , 288 , 42 , 148 ) );
$gp_pc["pc"][1] = 860;
$gp_pc["pc"][1] = 861;
if ( ! ( $gp_pc["var"][139] == 1 ) )  $gp_pc["pc"][1] = 867;
sco_cmd( 'CC' , array( ( $gp_pc["var"][77] + 284 ) , ( $gp_pc["var"][78] + 0 ) , 356 , 240 , 284 , 0 ) );
if ( ! $gp_pc["var"][-3] )  $gp_pc["pc"][1] = 866;
sco_cmd( 'CC' , array( ( $gp_pc["var"][77] + 42 ) , ( $gp_pc["var"][78] + 148 ) , 224 , 288 , 42 , 148 ) );
$gp_pc["pc"][1] = 866;
$gp_pc["pc"][1] = 867;
if ( ! ( $gp_pc["var"][139] == 2 ) )  $gp_pc["pc"][1] = 873;
sco_cmd( 'CC' , array( ( $gp_pc["var"][77] + 284 ) , ( $gp_pc["var"][78] + 240 ) , 356 , 240 , 284 , 240 ) );
if ( ! $gp_pc["var"][-3] )  $gp_pc["pc"][1] = 872;
sco_cmd( 'CC' , array( ( $gp_pc["var"][77] + 42 ) , ( $gp_pc["var"][78] + 148 ) , 224 , 288 , 42 , 148 ) );
$gp_pc["pc"][1] = 872;
$gp_pc["pc"][1] = 873;
$gp_pc["var"][139] = 99;
$gp_pc["pc"][1] = 875;
if ( ! ( $gp_pc["var"][139] != $gp_pc["var"][138] ) )  $gp_pc["pc"][1] = 978;
if ( ! ( $gp_pc["var"][138] == 0 ) )  $gp_pc["pc"][1] = 908;
if ( ! ( $gp_pc["var"][11] == 1 ) )  $gp_pc["pc"][1] = 906;
sco_cmd( '~' , array( 8 , 77 ) );
sco_cmd( 'WZ_0' , array( 0 ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 0 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 7 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 1 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 8 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 2 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 9 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 3 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 10 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 4 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 11 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 5 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 12 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 6 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 13 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 7 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 14 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 8 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 15 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 9 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 16 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 10 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 17 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 11 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 0 ) ) );
sco_cmd( 'WX' , array( 42 , 148 , 224 , 288 ) );
$gp_pc["pc"][1] = 906;
sco_cmd( '~' , array( 8 , 48 ) );
$gp_pc["pc"][1] = 908;
if ( ! ( $gp_pc["var"][138] == 1 ) )  $gp_pc["pc"][1] = 942;
if ( ! ( $gp_pc["var"][11] != 0 ) )  $gp_pc["pc"][1] = 938;
sco_cmd( '~' , array( 8 , 77 ) );
sco_cmd( 'WZ_0' , array( 0 ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 0 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 18 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 1 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 8 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 2 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 19 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 3 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 20 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 4 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 21 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 5 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 22 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 6 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 23 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 7 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 24 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 8 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 25 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 9 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 26 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 10 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 27 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 11 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 28 ) ) );
sco_cmd( 'WX' , array( 42 , 148 , 224 , 288 ) );
$gp_pc["pc"][1] = 938;
sco_cmd( 'J_0' , array( 284 , 0 ) );
sco_cmd( 'G_0' , array( 7 ) );
sco_cmd( '~' , array( 8 , 48 ) );
$gp_pc["pc"][1] = 942;
if ( ! ( $gp_pc["var"][138] == 2 ) )  $gp_pc["pc"][1] = 976;
if ( ! ( $gp_pc["var"][11] != 0 ) )  $gp_pc["pc"][1] = 972;
sco_cmd( '~' , array( 8 , 77 ) );
sco_cmd( 'WZ_0' , array( 0 ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 0 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 29 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 1 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 8 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 2 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 30 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 3 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 31 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 4 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 32 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 5 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 33 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 6 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 34 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 7 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 35 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 8 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 36 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 9 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 37 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 10 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 38 ) ) );
sco_cmd( 'T' , array( 42 , ( 148 + ( 24 * 11 ) ) ) );
sco_cmd( 'msg' , array( sco_msg( 39 ) ) );
sco_cmd( 'WX' , array( 42 , 148 , 224 , 288 ) );
$gp_pc["pc"][1] = 972;
sco_cmd( 'J_0' , array( 284 , 240 ) );
sco_cmd( 'G_0' , array( 8 ) );
sco_cmd( '~' , array( 8 , 48 ) );
$gp_pc["pc"][1] = 976;
$gp_pc["var"][139] = $gp_pc["var"][138];
$gp_pc["pc"][1] = 978;
sco_cmd( '~' , array( 0 , 0 ) );

// 8,77 = callfunc
sco_cmd( 'MG_100' , array( 1 ) );
sco_cmd( 'MG_0' , array( 0 ) );
sco_cmd( 'ZM' , array( &$gp_pc["var"][62] ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 5,221 = callfunc
sco_cmd( 'ZT_10' , array( 100 , 10 , 0 ) );
sco_cmd( 'ZT_11' , array( 100 , &$gp_pc["var"][0] ) );
if ( ! ( $gp_pc["var"][0] < $gp_pc["var"][188] ) )  $gp_pc["pc"][1] = 992;
sco_cmd( 'ZT_11' , array( 100 , &$gp_pc["var"][0] ) );
$gp_pc["pc"][1] = 989;
sco_cmd( '~' , array( 0 , 0 ) );

// 7,1898 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 998;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 998;
sco_cmd( '~' , array( 7 , 2033 ) );
sco_cmd( 'SX_2_1' , array( &$gp_pc["var"][11] , 0 , 1 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 9,100 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1006;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1006;
sco_cmd( 'US' , array( &$gp_pc["var"][11] , 10 ) );
$gp_pc["var"][11] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
sco_cmd( '~' , array( 8 , 142 ) );
$gp_pc["var"][11] = $gp_pc["var"][144];
sco_cmd( '~' , array( 8 , 400 ) );
sco_cmd( 'UG' , array( &$gp_pc["var"][11] , 10 ) );
$gp_pc["var"][11] = $gp_pc["var"][11];
$gp_pc["var"][12] = $gp_pc["var"][12];
sco_cmd( '~' , array( 8 , 495 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 8,495 = callfunc
if ( ! ( 100 + $gp_pc["var"][-4] ) )  $gp_pc["pc"][1] = 1021;
sco_cmd( 'CC' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 , 0 ) );
$gp_pc["pc"][1] = 1021;
if ( ! ( 99 * $gp_pc["var"][-3] ) )  $gp_pc["pc"][1] = 1024;
sco_cmd( 'CE' , array( &$gp_pc["var"][77] , &$gp_pc["var"][78] , &$gp_pc["var"][81] , &$gp_pc["var"][82] , 0 , 0 , &$gp_pc["var"][11] , &$gp_pc["var"][12] , 0 ) );
$gp_pc["pc"][1] = 1024;
sco_cmd( '~' , array( 0 , 0 ) );

// 7,2091 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1030;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1030;
if ( ! 1 )  $gp_pc["pc"][1] = 1042;
sco_cmd( 'ZT_1' , array( 0 ) );
sco_cmd( 'SX_2_2' , array( &$gp_pc["var"][15] ) );
if ( ! ( $gp_pc["var"][15] == 1 ) )  $gp_pc["pc"][1] = 1036;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1036;
sco_cmd( 'ZT_20' , array( 2 ) );
sco_cmd( 'ZT_5' , array( &$gp_pc["var"][16] ) );
if ( ! ( $gp_pc["var"][16] > 7000 ) )  $gp_pc["pc"][1] = 1041;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1041;
$gp_pc["pc"][1] = 1030;
sco_cmd( '~' , array( 0 , 0 ) );

// 11,1290 = callfunc
$gp_pc["var"][125] = 0;
if ( ! ( $gp_pc["var"][138] == 1 ) )  $gp_pc["pc"][1] = 1050;
$gp_pc["var"][125] = 13;
$gp_pc["var"][126] = 1;
$gp_pc["pc"][1] = 1050;
if ( ! ( $gp_pc["var"][138] == 2 ) )  $gp_pc["pc"][1] = 1054;
$gp_pc["var"][125] = 27;
$gp_pc["var"][126] = 1;
$gp_pc["pc"][1] = 1054;
if ( ! ( $gp_pc["var"][138] == 11 ) )  $gp_pc["pc"][1] = 1058;
$gp_pc["var"][125] = 38;
$gp_pc["var"][126] = 1;
$gp_pc["pc"][1] = 1058;
sco_cmd( '~' , array( 0 , &$gp_pc["var"][125] ) );

// 13,561 = callfunc
$gp_pc["var"][160][$gp_pc["var"][158]] = $gp_pc["var"][115];
$gp_pc["var"][161][$gp_pc["var"][158]] = $gp_pc["var"][116];
$gp_pc["var"][162][$gp_pc["var"][158]] = $gp_pc["var"][117];
$gp_pc["var"][163][$gp_pc["var"][158]] = $gp_pc["var"][118];
$gp_pc["var"][164][$gp_pc["var"][158]] = $gp_pc["var"][119];
$gp_pc["var"][165][$gp_pc["var"][158]] = $gp_pc["var"][146];
$gp_pc["var"][166][$gp_pc["var"][158]] = $gp_pc["var"][150];
$gp_pc["var"][167][$gp_pc["var"][158]] = $gp_pc["var"][155];
$gp_pc["var"][168][$gp_pc["var"][158]] = $gp_pc["var"][125];
$gp_pc["var"][169][$gp_pc["var"][158]] = $gp_pc["var"][126];
sco_cmd( '~' , array( 0 , 0 ) );

// 13,676 = callfunc
$gp_pc["var"][212] = 0;
$gp_pc["var"][206] = 0;
if ( ! ( $gp_pc["var"][115] != $gp_pc["var"][160][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1082;
$gp_pc["var"][120] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][115] = $gp_pc["var"][160][$gp_pc["var"][158]];
sco_cmd( '~' , array( 8 , 871 ) );
$gp_pc["var"][212] = 1;
$gp_pc["pc"][1] = 1082;
if ( ! ( $gp_pc["var"][116] != $gp_pc["var"][161][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1088;
$gp_pc["var"][121] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][116] = $gp_pc["var"][161][$gp_pc["var"][158]];
sco_cmd( '~' , array( 8 , 717 ) );
$gp_pc["var"][212] = 1;
$gp_pc["pc"][1] = 1088;
if ( ! ( $gp_pc["var"][117] != $gp_pc["var"][162][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1094;
$gp_pc["var"][122] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][117] = $gp_pc["var"][162][$gp_pc["var"][158]];
sco_cmd( '~' , array( 8 , 1025 ) );
$gp_pc["var"][212] = 1;
$gp_pc["pc"][1] = 1094;
if ( ! ( $gp_pc["var"][118] != $gp_pc["var"][163][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1100;
$gp_pc["var"][123] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][118] = $gp_pc["var"][163][$gp_pc["var"][158]];
sco_cmd( '~' , array( 8 , 1233 ) );
$gp_pc["var"][212] = 1;
$gp_pc["pc"][1] = 1100;
if ( ! ( $gp_pc["var"][119] != $gp_pc["var"][164][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1106;
$gp_pc["var"][124] = ( 16383 + ( 16383 + ( 16383 + ( 16383 + 3 ) ) ) );
$gp_pc["var"][119] = $gp_pc["var"][164][$gp_pc["var"][158]];
sco_cmd( '~' , array( 8 , 1415 ) );
$gp_pc["var"][212] = 1;
$gp_pc["pc"][1] = 1106;
if ( ! ( $gp_pc["var"][212] == 1 ) )  $gp_pc["pc"][1] = 1112;
$gp_pc["var"][11] = 31;
$gp_pc["var"][12] = 3;
$gp_pc["var"][144] = $gp_pc["var"][165][$gp_pc["var"][158]];
sco_cmd( '~' , array( 9 , 100 ) );
$gp_pc["pc"][1] = 1112;
if ( ! ( $gp_pc["var"][155] != $gp_pc["var"][167][$gp_pc["var"][158]] ) )  $gp_pc["pc"][1] = 1116;
$gp_pc["var"][155] = $gp_pc["var"][167][$gp_pc["var"][158]];
sco_cmd( '~' , array( 7 , 1536 ) );
$gp_pc["pc"][1] = 1116;
$gp_pc["var"][150] = $gp_pc["var"][166][$gp_pc["var"][158]];
$gp_pc["var"][125] = $gp_pc["var"][168][$gp_pc["var"][158]];
$gp_pc["var"][126] = $gp_pc["var"][169][$gp_pc["var"][158]];
sco_cmd( '~' , array( 0 , 0 ) );

// 8,871 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1125;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1125;
if ( ! ( $gp_pc["var"][120] != $gp_pc["var"][115] ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][120] = $gp_pc["var"][115];
if ( ! ( $gp_pc["var"][115] == 0 ) )  $gp_pc["pc"][1] = 0;
sco_cmd( 'trace' , array( sco_msg( 40 ) ) );
$gp_pc["var"][85] = $gp_pc["var"][81];
$gp_pc["var"][86] = $gp_pc["var"][82];

// 8,1025 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1136;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1136;
if ( ! ( $gp_pc["var"][122] != $gp_pc["var"][117] ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][122] = $gp_pc["var"][117];
if ( ! ( $gp_pc["var"][117] == 0 ) )  $gp_pc["pc"][1] = 0;
sco_cmd( 'trace' , array( sco_msg( 41 ) ) );
$gp_pc["var"][93] = $gp_pc["var"][81];
$gp_pc["var"][94] = $gp_pc["var"][82];

// 8,1233 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1147;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1147;
if ( ! ( $gp_pc["var"][123] != $gp_pc["var"][118] ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][123] = $gp_pc["var"][118];
if ( ! ( $gp_pc["var"][118] == 0 ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][97] = $gp_pc["var"][81];
$gp_pc["var"][98] = $gp_pc["var"][82];

// 8,1415 = callfunc
if ( ! ( $gp_pc["var"][206] == 1 ) )  $gp_pc["pc"][1] = 1157;
sco_cmd( '~' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 1157;
if ( ! ( $gp_pc["var"][124] != $gp_pc["var"][119] ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][124] = $gp_pc["var"][119];
if ( ! ( $gp_pc["var"][119] == 0 ) )  $gp_pc["pc"][1] = 0;
$gp_pc["var"][101] = $gp_pc["var"][81];
$gp_pc["var"][102] = $gp_pc["var"][82];

// 8,105 = callfunc
$gp_pc["var"][64] = 0;
sco_cmd( 'inc' , array( &$gp_pc["var"][63] ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 10,1356 = callfunc
sco_cmd( 'QD' , array( 26 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 8,96 = callfunc
$gp_pc["var"][63] = 0;
sco_cmd( '~' , array( 0 , 0 ) );

// 9,1244 = callfunc
$gp_pc["var"][11] = 0;
$gp_pc["var"][12] = 0;
sco_cmd( '~' , array( 8 , 495 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 10,242 = callfunc
sco_cmd( 'US' , array( &$gp_pc["var"][150] , 1 ) );
$gp_pc["var"][150] = 15;
sco_cmd( '~' , array( 7 , 820 ) );
sco_cmd( 'CC' , array( 0 , 120 , 640 , 240 , &$gp_pc["var"][75] , ( ( $gp_pc["var"][76] + ( 480 * 2 ) ) + 120 ) ) );
sco_cmd( '~' , array( 10 , 582 ) );
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 10 , 597 ) );
sco_cmd( '~' , array( 10 , 396 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][136] ) );
if ( ! ( $gp_pc["var"][136] != 0 ) )  $gp_pc["pc"][1] = 1197;
$gp_pc["var"][11] = 0;
sco_cmd( '~' , array( 7 , 1813 ) );
sco_cmd( 'LD' , array( &$gp_pc["var"][136] ) );
$gp_pc["pc"][1] = 1197;
sco_cmd( 'CC' , array( &$gp_pc["var"][75] , ( ( $gp_pc["var"][76] + ( 480 * 2 ) ) + 120 ) , 640 , 240 , 0 , 120 ) );
sco_cmd( 'UG' , array( &$gp_pc["var"][150] , 1 ) );
sco_cmd( '~' , array( 7 , 835 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 10,582 = callfunc
$gp_pc["var"][136] = 0;
$gp_pc["var"][137] = 0;
sco_cmd( '~' , array( 0 , 0 ) );

// 10,597 = callfunc
if ( ! ( $gp_pc["var"][11] == 1 ) )  $gp_pc["pc"][1] = 1212;
sco_cmd( 'J_0' , array( &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 480 ) ) );
sco_cmd( 'G_0' , array( 125 ) );
$gp_pc["pc"][1] = 1212;
if ( ! ( $gp_pc["var"][11] == 2 ) )  $gp_pc["pc"][1] = 1216;
sco_cmd( 'J_0' , array( &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 480 ) ) );
sco_cmd( 'G_0' , array( 126 ) );
$gp_pc["pc"][1] = 1216;
sco_cmd( 'J_0' , array( &$gp_pc["var"][75] , ( ( $gp_pc["var"][76] + 480 ) + 240 ) ) );
sco_cmd( 'G_0' , array( 127 ) );
sco_cmd( '~' , array( 8 , 77 ) );
sco_cmd( 'CC' , array( &$gp_pc["var"][75] , ( $gp_pc["var"][76] + 480 ) , 512 , 240 , &$gp_pc["var"][75] , &$gp_pc["var"][76] ) );
sco_cmd( 'US' , array( &$gp_pc["var"][133] , 1 ) );
$gp_pc["var"][45] = 1;

if ( $gp_pc["var"][45] > 5 )  $gp_pc["pc"][1] = 1233;
sco_cmd( 'T' , array( ( ( $gp_pc["var"][75] + 50 ) + 44 ) , ( ( ( $gp_pc["var"][76] + 62 ) + ( ( $gp_pc["var"][45] - 1 ) * 30 ) ) + 4 ) ) );
$gp_pc["var"][133] = 0;
sco_cmd( 'LP' , array( &$gp_pc["var"][45] , &$gp_pc["var"][133] , 1 ) );
$gp_pc["var"][11] = $gp_pc["var"][133];
sco_cmd( '~' , array( 11 , 456 ) );
sco_cmd( 'msg' , array( sco_msg( 66 ) ) );
$gp_pc["var"][11] = $gp_pc["var"][45];
sco_cmd( '~' , array( 10 , 841 ) );
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 1223;
sco_cmd( 'UG' , array( &$gp_pc["var"][133] , 1 ) );
sco_cmd( 'CC' , array( &$gp_pc["var"][75] , &$gp_pc["var"][76] , 512 , 240 , 64 , 120 ) );
sco_cmd( '~' , array( 8 , 48 ) );
sco_cmd( '~' , array( 0 , 0 ) );

// 11,456 = callfunc
if ( ! ( $gp_pc["var"][11] == 0 ) )  $gp_pc["pc"][1] = 1242;
sco_cmd( 'msg' , array( sco_msg( 42 ) ) );
$gp_pc["pc"][1] = 1242;
if ( ! ( $gp_pc["var"][11] == 1 ) )  $gp_pc["pc"][1] = 1245;
sco_cmd( 'msg' , array( sco_msg( 43 ) ) );
$gp_pc["pc"][1] = 1245;
if ( ! ( $gp_pc["var"][11] == 2 ) )  $gp_pc["pc"][1] = 1248;
sco_cmd( 'msg' , array( sco_msg( 44 ) ) );
$gp_pc["pc"][1] = 1248;
if ( ! ( $gp_pc["var"][11] == 3 ) )  $gp_pc["pc"][1] = 1251;
sco_cmd( 'msg' , array( sco_msg( 45 ) ) );
$gp_pc["pc"][1] = 1251;
if ( ! ( $gp_pc["var"][11] == 4 ) )  $gp_pc["pc"][1] = 1254;
sco_cmd( 'msg' , array( sco_msg( 46 ) ) );
$gp_pc["pc"][1] = 1254;
if ( ! ( $gp_pc["var"][11] == 5 ) )  $gp_pc["pc"][1] = 1257;
sco_cmd( 'msg' , array( sco_msg( 47 ) ) );
$gp_pc["pc"][1] = 1257;
if ( ! ( $gp_pc["var"][11] == 6 ) )  $gp_pc["pc"][1] = 1260;
sco_cmd( 'msg' , array( sco_msg( 48 ) ) );
$gp_pc["pc"][1] = 1260;
if ( ! ( $gp_pc["var"][11] == 7 ) )  $gp_pc["pc"][1] = 1263;
sco_cmd( 'msg' , array( sco_msg( 49 ) ) );
$gp_pc["pc"][1] = 1263;
if ( ! ( $gp_pc["var"][11] == 8 ) )  $gp_pc["pc"][1] = 1266;
sco_cmd( 'msg' , array( sco_msg( 50 ) ) );
$gp_pc["pc"][1] = 1266;
if ( ! ( $gp_pc["var"][11] == 9 ) )  $gp_pc["pc"][1] = 1269;
sco_cmd( 'msg' , array( sco_msg( 51 ) ) );
$gp_pc["pc"][1] = 1269;
if ( ! ( $gp_pc["var"][11] == 10 ) )  $gp_pc["pc"][1] = 1272;
sco_cmd( 'msg' , array( sco_msg( 52 ) ) );
$gp_pc["pc"][1] = 1272;
if ( ! ( $gp_pc["var"][11] == 11 ) )  $gp_pc["pc"][1] = 1275;
sco_cmd( 'msg' , array( sco_msg( 53 ) ) );
$gp_pc["pc"][1] = 1275;
if ( ! ( $gp_pc["var"][11] == 12 ) )  $gp_pc["pc"][1] = 1278;
sco_cmd( 'msg' , array( sco_msg( 54 ) ) );
$gp_pc["pc"][1] = 1278;
if ( ! ( $gp_pc["var"][11] == 13 ) )  $gp_pc["pc"][1] = 1281;
sco_cmd( 'msg' , array( sco_msg( 55 ) ) );
$gp_pc["pc"][1] = 1281;
if ( ! ( $gp_pc["var"][11] == 14 ) )  $gp_pc["pc"][1] = 1284;
sco_cmd( 'msg' , array( sco_msg( 56 ) ) );
$gp_pc["pc"][1] = 1284;
if ( ! ( $gp_pc["var"][11] == 15 ) )  $gp_pc["pc"][1] = 1287;
sco_cmd( 'msg' , array( sco_msg( 57 ) ) );
$gp_pc["pc"][1] = 1287;
if ( ! ( $gp_pc["var"][11] == 16 ) )  $gp_pc["pc"][1] = 1290;
sco_cmd( 'msg' , array( sco_msg( 58 ) ) );
$gp_pc["pc"][1] = 1290;
if ( ! ( $gp_pc["var"][11] == 17 ) )  $gp_pc["pc"][1] = 1293;
sco_cmd( 'msg' , array( sco_msg( 59 ) ) );
$gp_pc["pc"][1] = 1293;
if ( ! ( $gp_pc["var"][11] == 18 ) )  $gp_pc["pc"][1] = 1296;
sco_cmd( 'msg' , array( sco_msg( 60 ) ) );
$gp_pc["pc"][1] = 1296;
if ( ! ( $gp_pc["var"][11] == 19 ) )  $gp_pc["pc"][1] = 1299;
sco_cmd( 'msg' , array( sco_msg( 61 ) ) );
$gp_pc["pc"][1] = 1299;
if ( ! ( $gp_pc["var"][11] == 20 ) )  $gp_pc["pc"][1] = 1302;
sco_cmd( 'msg' , array( sco_msg( 62 ) ) );
$gp_pc["pc"][1] = 1302;
if ( ! ( $gp_pc["var"][11] == 21 ) )  $gp_pc["pc"][1] = 1305;
sco_cmd( 'msg' , array( sco_msg( 63 ) ) );
$gp_pc["pc"][1] = 1305;
if ( ! ( $gp_pc["var"][11] == 22 ) )  $gp_pc["pc"][1] = 1308;
sco_cmd( 'msg' , array( sco_msg( 64 ) ) );
$gp_pc["pc"][1] = 1308;
if ( ! ( $gp_pc["var"][11] == 23 ) )  $gp_pc["pc"][1] = 1311;
sco_cmd( 'msg' , array( sco_msg( 65 ) ) );
$gp_pc["pc"][1] = 1311;
sco_cmd( '~' , array( 0 , 0 ) );

// 10,841 = callfunc
sco_cmd( 'US' , array( &$gp_pc["var"][1] , 10 ) );
sco_cmd( 'LT' , array( |11|0| , 0] , 1] , 2] , 3] , 4] , 5] ) );
if ( ! ( $gp_pc["var"][0] == 0 ) )  $gp_pc["pc"][1] = 0;
sco_cmd( 'HH' , array( 4 , &$gp_pc["var"][1] ) );
sco_cmd( 'msg' , array( sco_msg( 67 ) ) );
sco_cmd( 'HH' , array( 2 , &$gp_pc["var"][2] ) );
sco_cmd( 'msg' , array( sco_msg( 67 ) ) );
sco_cmd( 'HH' , array( 2 , &$gp_pc["var"][3] ) );
sco_cmd( 'msg' , array( sco_msg( 68 ) ) );
sco_cmd( 'HH' , array( 2 , &$gp_pc["var"][4] ) );
sco_cmd( 'msg' , array( sco_msg( 0 ) ) );

// 10,396 = callfunc
$gp_pc["var"][53] = 1;
if ( ! ( $gp_pc["var"][53] == 1 ) )  $gp_pc["pc"][1] = 1345;
sco_cmd( 'IM' , array( &$gp_pc["var"][33] , &$gp_pc["var"][34] ) );
$gp_pc["var"][50] = $gp_pc["var"][0];
$gp_pc["var"][33] = $gp_pc["var"][33];
$gp_pc["var"][34] = $gp_pc["var"][34];
sco_cmd( '~' , array( 10 , 1244 ) );
sco_cmd( '~' , array( -1 , &$gp_pc["var"][136] ) );
$gp_pc["var"][136] = $gp_pc["var"][136];
sco_cmd( '~' , array( 10 , 959 ) );
if ( ! ( ( $gp_pc["var"][50] == 16 ) * ( $gp_pc["var"][136] != 0 ) ) )  $gp_pc["pc"][1] = 1340;
$gp_pc["var"][53] = 0;
$gp_pc["pc"][1] = 1340;
if ( ! ( $gp_pc["var"][50] == 32 ) )  $gp_pc["pc"][1] = 1344;
$gp_pc["var"][53] = 0;
$gp_pc["var"][136] = 0;
$gp_pc["pc"][1] = 1344;
$gp_pc["pc"][1] = 1328;
if ( ! ( $gp_pc["var"][136] != 0 ) )  $gp_pc["pc"][1] = 1349;
$gp_pc["var"][11] = 2;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 1349;
if ( ! ( $gp_pc["var"][136] == 0 ) )  $gp_pc["pc"][1] = 1353;
$gp_pc["var"][11] = 3;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["pc"][1] = 1353;
$gp_pc["var"][136] = $gp_pc["var"][136];
sco_cmd( '~' , array( 10 , 1120 ) );
sco_cmd( '~' , array( 5 , 32 ) );
sco_cmd( '~' , array( 0 , &$gp_pc["var"][136] ) );

// 10,1244 = callfunc
$gp_pc["var"][136] = 0;
$gp_pc["var"][45] = 1;

if ( $gp_pc["var"][45] > 5 )  $gp_pc["pc"][1] = 1369;
if ( ! ( ( ( 120 + 62 ) + ( ( $gp_pc["var"][45] - 1 ) * 30 ) ) * $gp_pc["var"][-4] ) )  $gp_pc["pc"][1] = 1368;
if ( ! ( ( ( ( 120 + 62 ) + ( ( $gp_pc["var"][45] - 1 ) * 30 ) ) + 27 ) * $gp_pc["var"][-3] ) )  $gp_pc["pc"][1] = 1367;
$gp_pc["var"][136] = $gp_pc["var"][45];
$gp_pc["pc"][1] = 1367;
$gp_pc["pc"][1] = 1368;
$gp_pc["var"][45] += 1; $gp_pc["pc"][1] = 1362;
sco_cmd( '~' , array( 0 , &$gp_pc["var"][136] ) );

// 10,959 = callfunc
if ( ! ( ( $gp_pc["var"][136] != $gp_pc["var"][137] ) * ( $gp_pc["var"][137] != 0 ) ) )  $gp_pc["pc"][1] = 1378;
$gp_pc["var"][37] = 50;
$gp_pc["var"][38] = ( 62 + ( ( $gp_pc["var"][137] - 1 ) * 30 ) );
sco_cmd( 'CC' , array( ( $gp_pc["var"][75] + $gp_pc["var"][37] ) , ( $gp_pc["var"][76] + $gp_pc["var"][38] ) , 408 , 27 , ( $gp_pc["var"][37] + 64 ) , ( $gp_pc["var"][38] + 120 ) ) );
$gp_pc["var"][137] = 0;
$gp_pc["pc"][1] = 1378;
if ( ! ( ( $gp_pc["var"][136] != $gp_pc["var"][137] ) * ( $gp_pc["var"][136] != 0 ) ) )  $gp_pc["pc"][1] = 1386;
$gp_pc["var"][11] = 1;
sco_cmd( '~' , array( 7 , 1651 ) );
$gp_pc["var"][37] = 50;
$gp_pc["var"][38] = ( 62 + ( ( $gp_pc["var"][136] - 1 ) * 30 ) );
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][75] , ( ( $gp_pc["var"][76] + 480 ) + 240 ) , 408 , 27 , ( $gp_pc["var"][37] + 64 ) , ( $gp_pc["var"][38] + 120 ) , 0 ) );
$gp_pc["var"][137] = $gp_pc["var"][136];
$gp_pc["pc"][1] = 1386;
sco_cmd( '~' , array( 0 , 0 ) );

// 10,1120 = callfunc
if ( ! ( $gp_pc["var"][136] == 0 ) )  $gp_pc["pc"][1] = 1394;
$gp_pc["var"][37] = 50;
$gp_pc["var"][38] = 62;
sco_cmd( 'CC' , array( ( $gp_pc["var"][75] + $gp_pc["var"][37] ) , ( $gp_pc["var"][76] + $gp_pc["var"][38] ) , 408 , ( 30 * 5 ) , ( $gp_pc["var"][37] + 64 ) , ( $gp_pc["var"][38] + 120 ) ) );
$gp_pc["pc"][1] = 1394;
if ( ! ( $gp_pc["var"][136] != 0 ) )  $gp_pc["pc"][1] = 1399;
$gp_pc["var"][37] = 50;
$gp_pc["var"][38] = ( 62 + ( ( $gp_pc["var"][136] - 1 ) * 30 ) );
sco_cmd( 'CX' , array( 0 , &$gp_pc["var"][75] , ( ( ( $gp_pc["var"][76] + 480 ) + 240 ) + 27 ) , 408 , 27 , ( $gp_pc["var"][37] + 64 ) , ( $gp_pc["var"][38] + 120 ) , 0 ) );
$gp_pc["pc"][1] = 1399;
sco_cmd( '~' , array( 0 , 0 ) );

// 7,835 = callfunc
sco_cmd( 'ZC' , array( 1 , ( 128 + $gp_pc["var"][150] ) ) );
sco_cmd( '~' , array( 0 , 0 ) );

