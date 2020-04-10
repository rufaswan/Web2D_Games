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
sco_cmd( 'WW' , array( 640 , 1500 , 24 ) );
sco_cmd( 'WV' , array( 0 , 0 , 640 , 480 ) );
sco_cmd( 'B_1' , array( 1 , 230 , 240 , 180 , 440 , 1 ) );
sco_cmd( 'B_2' , array( 1 , 1 , 0 , 0 , 0 , 0 ) );
sco_cmd( 'B_3' , array( 1 , &$gp_pc["var"][56] , &$gp_pc["var"][57] , ( 640 - $gp_pc["var"][56] ) , ( 480 - $gp_pc["var"][57] ) , 0 ) );
sco_cmd( 'B_4' , array( 1 , 0 , 0 , 0 , 1 , 0 ) );
sco_cmd( 'J_0' , array( 0 , 1440 ) );
sco_cmd( 'G_0' , array( 101 ) );
sco_cmd( 'ZL' , array( 12 ) );
sco_cmd( 'ZC' , array( 0 , 1 ) );
sco_cmd( 'ZE' , array( 0 ) );
sco_cmd( 'MS' , array( 1 , sco_msg( 1 ) ) );
sco_cmd( 'MS' , array( 2 , sco_msg( 2 ) ) );
sco_cmd( 'MS' , array( 3 , sco_msg( 3 ) ) );
$gp_pc["var"][40] = 9;
sco_cmd( 'MT' , array( sco_msg( 4 ) ) );
sco_cmd( 'MV' , array( 101 ) );
sco_cmd( 'ZZ_13' , array( 1 ) );
$gp_pc["var"][53] = 0;
$gp_pc["var"][50] = 96;
sco_cmd( 'PS' , array( 100 , 0 , 0 , 0 ) );
sco_cmd( 'PS' , array( 101 , 0 , 0 , 255 ) );
sco_cmd( 'PS' , array( 102 , 255 , 0 , 0 ) );
sco_cmd( 'PS' , array( 103 , 255 , 0 , 255 ) );
sco_cmd( 'PS' , array( 104 , 0 , 255 , 0 ) );
sco_cmd( 'PS' , array( 105 , 0 , 255 , 255 ) );
sco_cmd( 'PS' , array( 106 , 255 , 255 , 0 ) );
sco_cmd( 'PS' , array( 107 , 255 , 255 , 255 ) );
$gp_pc["var"][45] = 20;
sco_cmd( 'ZM' , array( &$gp_pc["var"][45] ) );
sco_cmd( 'LP' , array( 1 , &$gp_pc["var"][39] , 1 ) );
sco_cmd( 'LP' , array( 1 , &$gp_pc["var"][37] , 1 ) );
sco_cmd( 'LP' , array( 1 , &$gp_pc["var"][49] , 2 ) );
sco_cmd( 'LP' , array( 1 , &$gp_pc["var"][56] , 3 ) );
if ( ! ( $gp_pc["var"][0] == 255 ) )  $gp_pc["pc"][1] = 102;
$gp_pc["var"][39] = 1;
$gp_pc["var"][37] = 1;
sco_cmd( 'QD' , array( 1 ) );
$gp_pc["var"][44] = 10000;
sco_cmd( 'call' , array( 1 , 2393 ) );
sco_cmd( 'jump' , array( $gp_pc["var"][39] , 0 ) );
sco_cmd( 'IX' , array( &$gp_pc["var"][0] ) );
if ( ! ( $gp_pc["var"][0] == 1 ) )  $gp_pc["pc"][1] = 108;
$gp_pc["var"][0] = 0;
sco_cmd( 'call' , array( 0 , 0 ) );
sco_cmd( '~' , array( 1 , 689 ) );
sco_cmd( 'T' , array( &$gp_pc["var"][1] , &$gp_pc["var"][2] ) );
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,2393 = calllabel
sco_cmd( 'ZT_1' , array( 0 ) );
$gp_pc["var"][21] = 100;
sco_cmd( '~' , array( 1 , 964 ) );
sco_cmd( 'PF_1' , array( 0 ) );
sco_cmd( 'G_0' , array( 26 ) );
sco_cmd( 'PF_2' , array( 4 , 0 ) );
sco_cmd( 'SG_1' , array( 1 ) );
sco_cmd( 'IK_0' , array(  ) );
sco_cmd( 'sel_st' , array( 134 ) );
sco_cmd( 'MSG' , array( sco_msg( 5 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
if ( ! ( $gp_pc["var"][44] != 10000 ) )  $gp_pc["pc"][1] = 128;
sco_cmd( 'sel_st' , array( 138 ) );
sco_cmd( 'MSG' , array( sco_msg( 6 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
sco_cmd( 'sel_st' , array( 140 ) );
sco_cmd( 'MSG' , array( sco_msg( 7 ) ) );
sco_cmd( 'sel_ed' , array(  ) );
sco_cmd( ']' , array(  ) );
sco_cmd( '~' , array( 1 , 1071 ) );
$gp_pc["pc"][1] = 121;
sco_cmd( 'call' , array( 1 , 2565 ) );
$gp_pc["var"][39] = 1;
$gp_pc["var"][37] = 1;
sco_cmd( 'call' , array( 0 , 0 ) );
sco_cmd( 'call' , array( 1 , 2565 ) );
sco_cmd( 'call' , array( 0 , 0 ) );
sco_cmd( 'call' , array( 1 , 2565 ) );
$gp_pc["var"][39] = 10;
$gp_pc["var"][37] = 1;
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,964 = callfunc
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,1071 = callfunc
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,2565 = calllabel
sco_cmd( 'PF_1' , array( 4 ) );
sco_cmd( 'CF' , array( 0 , 0 , 640 , 480 , 0 ) );
sco_cmd( 'PF_0' , array( 0 ) );
sco_cmd( 'ZT_1' , array( 0 ) );
$gp_pc["var"][21] = 200;
sco_cmd( '~' , array( 1 , 964 ) );
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,689 = callfunc
sco_cmd( 'B_10' , array( &$gp_pc["var"][1] , &$gp_pc["var"][2] ) );
$gp_pc["var"][3] = 1;
$gp_pc["var"][4] = ( $gp_pc["var"][45] + 1 );
$gp_pc["var"][5] = ( $gp_pc["var"][45] + 1 );
sco_cmd( 'CC' , array( &$gp_pc["var"][1] , &$gp_pc["var"][2] , &$gp_pc["var"][4] , &$gp_pc["var"][5] , 0 , 960 ) );
sco_cmd( 'IX' , array( &$gp_pc["var"][0] ) );
if ( ! ( $gp_pc["var"][0] == 1 ) )  $gp_pc["pc"][1] = 170;
$gp_pc["var"][0] = 0;
sco_cmd( 'call' , array( 0 , 0 ) );
sco_cmd( '~' , array( 1 , 844 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 178;
if ( ! ( $gp_pc["var"][0] == 32 ) )  $gp_pc["pc"][1] = 175;
$gp_pc["var"][59] = 1;
$gp_pc["var"][0] = 0;
sco_cmd( '~' , array( 1 , 1071 ) );
sco_cmd( '~' , array( 1 , 937 ) );
sco_cmd( 'call' , array( 0 , 0 ) );
sco_cmd( '~' , array( 1 , 887 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 185;
if ( ! ( $gp_pc["var"][0] == 32 ) )  $gp_pc["pc"][1] = 183;
$gp_pc["var"][59] = 1;
$gp_pc["var"][0] = 0;
sco_cmd( '~' , array( 1 , 1071 ) );
sco_cmd( 'call' , array( 0 , 0 ) );
$gp_pc["pc"][1] = 166;

// 1,844 = callfunc
sco_cmd( 'ZT_1' , array( 0 ) );
sco_cmd( 'T' , array( &$gp_pc["var"][1] , &$gp_pc["var"][2] ) );
sco_cmd( 'X' , array( &$gp_pc["var"][3] ) );
$gp_pc["var"][21] = 50;
sco_cmd( '~' , array( 1 , 1008 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 195;
sco_cmd( 'call' , array( 0 , 0 ) );
$gp_pc["var"][0] = 0;
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,1008 = callfunc
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,937 = callfunc
sco_cmd( 'ZT_1' , array( 0 ) );
sco_cmd( 'CC' , array( 0 , 960 , &$gp_pc["var"][4] , &$gp_pc["var"][5] , &$gp_pc["var"][1] , &$gp_pc["var"][2] ) );
$gp_pc["var"][0] = 0;
sco_cmd( 'call' , array( 0 , 0 ) );

// 1,887 = callfunc
sco_cmd( 'ZT_1' , array( 0 ) );
sco_cmd( 'CC' , array( 0 , 960 , &$gp_pc["var"][4] , &$gp_pc["var"][5] , &$gp_pc["var"][1] , &$gp_pc["var"][2] ) );
$gp_pc["var"][21] = 50;
sco_cmd( '~' , array( 1 , 1008 ) );
if ( ! ( $gp_pc["var"][0] != 0 ) )  $gp_pc["pc"][1] = 214;
sco_cmd( 'call' , array( 0 , 0 ) );
$gp_pc["var"][0] = 0;
sco_cmd( 'call' , array( 0 , 0 ) );

