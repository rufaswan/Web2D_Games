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
// <@  cali:成立中実行コマンド  <@ Whileループ開始
// >  Whileループ終了

function B_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	$type = ord( $file[$st+1] );
	switch( $type )
	{
		// B1,num,X1,Y1,X2,Y2,V:  選択肢ウィンドウの座標を設定する
		case 1:
			$st += 2;
			$num = sco35_calli($file, $st);
			$x1 = sco35_calli($file, $st);
			$y1 = sco35_calli($file, $st);
			$x2 = sco35_calli($file, $st);
			$y2 = sco35_calli($file, $st);
			$v = sco35_calli($file, $st);
			$w = var_size( $x2-$x1 );
			$h = var_size( $y2-$y1 );
			trace("select B1 $num , $x1 , $y1 , $w , $h , $v");
			$gp_pc["B1"][$num] = array($x1 , $y1 , $w , $h , $v);
			return;
		// B2,num,W,C1,C2,C3,dot:  選択肢ウィンドウを切り替える
		case 2:
			$st += 2;
			$num = sco35_calli($file, $st);
			$w  = sco35_calli($file, $st);
			$c1 = sco35_calli($file, $st);
			$c2 = sco35_calli($file, $st);
			$c3 = sco35_calli($file, $st);
			$dot = sco35_calli($file, $st);
			trace("select B2 $num , $w , $c1 , $c2 , $c3 , $dot");
			$gp_pc["B2"] = array($num , $w , $c1 , $c2 , $c3 , $dot);
			return;
		// B3,num,X1,Y1,X2,Y2,V:  メッセージウィンドウの座標を設定する
		case 3:
			$st += 2;
			$num = sco35_calli($file, $st);
			$x1 = sco35_calli($file, $st);
			$y1 = sco35_calli($file, $st);
			$x2 = sco35_calli($file, $st);
			$y2 = sco35_calli($file, $st);
			$v = sco35_calli($file, $st);
			$w = var_size( $x2-$x1 );
			$h = var_size( $y2-$y1 );
			trace("text B3 $num , $x1 , $y1 , $w , $h , $v");
			$gp_pc["B3"][$num] = array($x1 , $y1 , $w , $h , $v);
			return;
		// B4,num,W,C1,C2,N(C3),M(dot):  メッセージウィンドウを切り替える
		case 4:
			$st += 2;
			$num = sco35_calli($file, $st);
			$w  = sco35_calli($file, $st);
			$c1 = sco35_calli($file, $st);
			$c2 = sco35_calli($file, $st);
			$c3 = sco35_calli($file, $st);
			$dot = sco35_calli($file, $st);
			trace("text B4 $num , $w , $c1 , $c2 , $c3 , $dot");
			$gp_pc["B4"] = array($num , $w , $c1 , $c2 , $c3 , $dot);
			$gp_pc["T"] = array($gp_pc["B3"][$num][0] , $gp_pc["B3"][$num][1]);
			return;
		// B10 var_x,var_y:  メッセージを次に表示する座標を取得する
		case 10:
			$st += 2;
			list($v1,$e1) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			list($v2,$e2) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			trace("text B10 $v1+$e1 , $v2+$e2");
			sco35_var_put( $v1, $e1, $gp_pc["T"][0] );
			sco35_var_put( $v2, $e2, $gp_pc["T"][1] );
			return;
	}
	return;
}

function F_cmd35( &$file, &$st, &$run )
{
	global $sco_file, $gp_pc;
	$type = ord( $file[$st+1] );
	switch( $type )
	{
		// F1,str_number,skip:  テーブルデータ文字列取得(ベース移動,オフセット指定)
		case 1:
			$st += 2;
			$str_number = sco35_calli($file, $st);
			$skip = sco35_calli($file, $st);

			$ind = $gp_pc["F"][0];
			$pos = $gp_pc["F"][1] + ($skip * 2);
			$old = $pos;
			sco35_load_sco( $ind );
			$jp = sco35_sjis( $sco_file[ $ind ], $pos );
				$pos++; // skip 0
			trace("array F1 $str_number , $skip = $jp");
			$gp_pc["F"][1] += ($pos - $old);
			$gp_pc["X"][$str_number] = base64_encode($jp);
			return;
		// F2,read_var,skip:  テーブルデータ数値取得(ベース移動,オフセット指定)
		case 2:
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$skip = sco35_calli($file, $st);

			$ind = $gp_pc["F"][0];
			$pos = $gp_pc["F"][1] + ($skip * 2);
			sco35_load_sco( $ind );
			$int = str2int( $sco_file[ $ind ], $pos, 2 );

			trace("array F2 $v+$e , $skip = $int");
			sco35_var_put( $v, $e, $int );
			$gp_pc["F"][1] += 2;
			return;
		// F3,read_var,skip:  テーブルデータ数値取得(ベース固定,オフセット指定)
		case 3:
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$skip = sco35_calli($file, $st);

			$ind = $gp_pc["F"][0];
			$pos = $gp_pc["F"][1] + ($skip * 2);
			sco35_load_sco( $ind );
			$int = str2int( $sco_file[ $ind ], $pos, 2 );

			trace("array F3 $v+$e , $skip = $int");
			sco35_var_put( $v, $e, $int );
			return;
		// F4,read_var,count:  テーブルデータ数値取得(ベース移動,個数指定)
		// F5,read_var,count:  テーブルデータ数値取得(ベース固定,個数指定)
		// F6,var,index:  F7コマンドで読み込む変数の指定
		// F7,data_width,count:  F6で指定されたデータの読み込み
	}
	return;
}

function J_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	$type = ord( $file[$st+1] );
	switch( $type )
	{
		// J0 x,y:  次に来るGコマンドでのCG表示開始座標を指定する (絶対座標指定)
		// J1 x,y:  次に来るGコマンドでのCG表示開始座標を指定する (相対指定)
		case 0:
		case 1:
			$st += 2;
			$x = sco35_calli($file, $st);
			$y = sco35_calli($file, $st);
			trace("cg J  $type , $x , $y");
			$gp_pc["J"][$type] = array($x, $y);
			return;
		// J2 x,y:  GコマンドでのCG表示開始座標を指定する (絶対座標指定)
		// J3 x,y:  GコマンドでのCG表示開始座標を指定する (相対指定)
		// J4:  J2ｺﾏﾝﾄﾞ･J3ｺﾏﾝﾄﾞによる座標指定を解除する
/*
			if ( isset( $gp_pc["J"][2] ) )
				unset( $gp_pc["J"][2] );
			if ( isset( $gp_pc["J"][3] ) )
				unset( $gp_pc["J"][3] );
*/
	}
	return;
}

// 拡張コマンド一覧
function Y_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	$bak = $st;
		$st++;
	$type = sco35_calli($file, $st);
	switch( $type )
	{
		// Y1,0:  −−−代替えコマンド策定中−−−
		// == "A"
		// SYS 3.6+ => B0
		case 1:
			$n = sco35_calli($file, $st);
			trace("Y  1 , $n");
			sco35_text_add( "_NEXT_" );
			return;
		// Y2,0:  −−−今後なるべくNBコマンドを使用する様にしてください−−−
		// var_init V00-V20
		// => NB
		case 2:
			$n = sco35_calli($file, $st);
			trace("Y  2 , $n");
			$copy = array_fill(0, 20+1 , 0);
			sco35_var_put(0, 0, $copy);
			return;
		// Y3,n:  −−−今後なるべく使用しない様にしてください−−− sleep
		// Y3 return RND key
		// => ZT20 , ZT21
		case 3:
			$n = sco35_calli($file, $st);
			trace("Y  3 , $n");
			$gp_pc["var"][0] = 0;
			return;
		// Y4,n:  −−−代替えコマンド策定中−−−
		// Y4 return RND rand() [1-n]
		// SYS 3.6+ => ZR
		case 4:
			$n = sco35_calli($file, $st);
			trace("Y  4 , $n");
			if ( $n == 0 )
			{
				$gp_pc["var"][0] = 0;
				return;
			}
			$rand = (rand() * rand()) & BIT24;
			//$rand = (rand() << 8) + rand();
			$gp_pc["var"][0] = ($rand % $n) + 1;
			return;
	}
	$st = $bak;
	return;
}

// CL start_x,start_y,end_x,end_y,color:  LINE (このコマンドのみ指定パラメータが違うので注意)
// CX mode,src_x,src_y,len_x,len_y,dst_x,dst_y,col:  16bit以上専用のDIBのスプライトコピー（半透明処理が可能）
function C_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// CB start_x,start_y,lengs_x,lengs_y,color:  BOX
		case 'B':
			$st += 2;
			$start_x = sco35_calli($file, $st);
			$start_y = sco35_calli($file, $st);
			$lengs_x = sco35_calli($file, $st);
			$lengs_y = sco35_calli($file, $st);
			$color   = sco35_calli($file, $st);
			trace("CB $start_x , $start_y , $lengs_x , $lengs_y , $color");
			$src = array($start_x , $start_y , $lengs_x , $lengs_y , $color);
			sco35_div_add( "_COLOR_", $src );
			return;
		// CC sorce_x,sorce_y,sorce_lengs_x,sorce_lengs_y,destin_x,destin_y:  画面のコピー
		case 'C':
			$st += 2;
			$sorce_x = sco35_calli($file, $st);
			$sorce_y = sco35_calli($file, $st);
			$sorce_lengs_x = sco35_calli($file, $st);
			$sorce_lengs_y = sco35_calli($file, $st);
			$destin_x = sco35_calli($file, $st);
			$destin_y = sco35_calli($file, $st);
			trace("CC $sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y");
			$src = array($sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y);
			sco35_div_add( "_BG_", $src );
			//$run = false;
			return;
		// CE sorce_x,sorce_y,sorce_lengs_x,sorce_lengs_y,destin_x,destin_y,effect_number,option,wait_flag:  エフェクト機能付きコピー (CD)
		case 'E':
			$st += 2;
			$sorce_x = sco35_calli($file, $st);
			$sorce_y = sco35_calli($file, $st);
			$sorce_lengs_x = sco35_calli($file, $st);
			$sorce_lengs_y = sco35_calli($file, $st);
			$destin_x = sco35_calli($file, $st);
			$destin_y = sco35_calli($file, $st);
			$effect_number = sco35_calli($file, $st);
			$option = sco35_calli($file, $st);
			$wait_flag = sco35_calli($file, $st);
			trace("CE $sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y , $effect_number , $option , $wait_flag");
			$src = array($sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y);
			sco35_div_add( "_BG_", $src );
			return;
		// CF start_x,start_y,lengs_x,lengs_y,color:  BOX-FILL
		case 'F':
			$st += 2;
			$start_x = sco35_calli($file, $st);
			$start_y = sco35_calli($file, $st);
			$lengs_x = sco35_calli($file, $st);
			$lengs_y = sco35_calli($file, $st);
			$color   = sco35_calli($file, $st);
			trace("CF $start_x , $start_y , $lengs_x , $lengs_y , $color");
			$src = array($start_x , $start_y , $lengs_x , $lengs_y , $color);
			sco35_div_add( "_COLOR_", $src );
			//$run = false;
			return;
		case 'K':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// CK 3,x0,y0,cx,cy,dst,src,count,0:  (256色専用) 指定範囲の色を変更する
				case 3:
					$st += 3;
					$x0  = sco35_calli($file, $st);
					$y0  = sco35_calli($file, $st);
					$cx  = sco35_calli($file, $st);
					$cy  = sco35_calli($file, $st);
					$dst = sco35_calli($file, $st);
					$src = sco35_calli($file, $st);
					$cnt = sco35_calli($file, $st);
					$z1  = sco35_calli($file, $st);
					trace("CK 3 , $x0 , $y0 , $cx , $cy , $dst , $src , $cnt , $z1");
					return;
				// CK 1,x0,y0,cx,cy,col,rate,0,0:  (ﾌﾙｶﾗｰ専用) 指定範囲に色を重ねる
				// CK 2,x0,y0,cx,cy,col,dx,dy,0:  指定範囲に網掛けする
			}
		// CM sorce_x,sorce_y,sorce_lengs_x,sorce_lengs_y,destin_x,destin_y,destin_lengs_x,destin_lengs_y,mirror_switch:  拡大・縮小・反転コピー（個々の機能はスイッチで指定可能）
		case 'M':
			$st += 2;
			$sorce_x = sco35_calli($file, $st);
			$sorce_y = sco35_calli($file, $st);
			$sorce_lengs_x = sco35_calli($file, $st);
			$sorce_lengs_y = sco35_calli($file, $st);
			$destin_x = sco35_calli($file, $st);
			$destin_y = sco35_calli($file, $st);
			$destin_lengs_x = sco35_calli($file, $st);
			$destin_lengs_y = sco35_calli($file, $st);
			$mirror_switch  = sco35_calli($file, $st);
			trace("CM $sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y , $destin_lengs_x , $destin_lengs_y , $mirror_switch");
			return;
		// CP start_x,start_y,color:  ペイント
		case 'P':
			$st += 2;
			$start_x = sco35_calli($file, $st);
			$start_y = sco35_calli($file, $st);
			$color = sco35_calli($file, $st);
			trace("CP $start_x , $start_y , $color");
			$src = array($start_x , $start_y , $color);
			sco35_div_add( "_PAINT_", $src );
			return;
		// CS sorce_x,sorce_y,sorce_lengs_x,sorce_lengs_y,destin_x,destin_y,splite:  画面のスプライトコピー
		case 'S':
			$st += 2;
			$sorce_x = sco35_calli($file, $st);
			$sorce_y = sco35_calli($file, $st);
			$sorce_lengs_x = sco35_calli($file, $st);
			$sorce_lengs_y = sco35_calli($file, $st);
			$destin_x = sco35_calli($file, $st);
			$destin_y = sco35_calli($file, $st);
			$splite   = sco35_calli($file, $st);
			trace("CS $sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y , $splite");
			$gp_pc["CS"] = array($sorce_x , $sorce_y , $sorce_lengs_x , $sorce_lengs_y , $destin_x , $destin_y , $splite);
			return;
	}
	return;
}

// DI page_no,use_flag,size:  配列領域の設定情報を取得
// DR data_var  配列の解除
function D_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// DC page_no,size,save_flag  配列領域の作成(メモリを確保して配列領域のページを作成する)
		case 'C':
			$st += 2;
			$page_no   = sco35_calli($file, $st);
			$size      = sco35_calli($file, $st);
			$save_flag = sco35_calli($file, $st);
			trace("data DC $page_no , $size , $save_flag");
			return;
		// DF data_var,count,data  配列のクリア
		case 'F':
			$st += 2;
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			$count = sco35_calli($file, $st);
			$data  = sco35_calli($file, $st);
			trace("data DF var[]_$v+$e , $count , $data");
			$gp_pc["page"][$v+$e] = array_fill(0, $count , $data);
			return;
		// DS poin_var,data_var,位置,ページ  配列の設定 (DCコマンドを参照のこと)
		case 'S':
			$st += 2;
			list($v1,$e1) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			list($v2,$e2) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$pos  = sco35_calli($file, $st);
			$page = sco35_calli($file, $st);
			trace("data DS $v1+$e1 , $v2+$e2 , $pos , $page");
			return;
	}
	return;
}

function E_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// EC num:  ESコマンドで設定された画面領域をクリア
		case 'C':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("EC $num");
			sco35_ec_clear( $num );
			return;
		// ES num,c,x1,y1,x2,y2:  ECコマンドで使用する座標範囲を設定
		case 'S':
			$st += 2;
			$num = sco35_calli($file, $st);
			$c  = sco35_calli($file, $st);
			$x1 = sco35_calli($file, $st);
			$y1 = sco35_calli($file, $st);
			$x2 = sco35_calli($file, $st);
			$y2 = sco35_calli($file, $st);
			$w = var_size( $x2-$x1 );
			$h = var_size( $y2-$y1 );
			trace("ES $num , $c , $x1 , $y1 , $w , $h");
			$gp_pc["ES"][$num] = array($c , $x1 , $y1 , $w , $h);
			return;
	}
	return;
}

// GX cg_no,shadow_no:(24bitDIB only)  影データを指定してCGをロードする
function G_cmd35( &$file, &$st, &$run )
{
	global $gp_pc, $gp_img_meta;
	switch ( $file[$st+1] )
	{
		// GS num,var:  num 番にリンクされているCGの座標とサイズを取得する
		case 'S':
			$st += 2;
			$num = sco35_calli($file, $st);
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			trace("GS $num , $v+$e += 4");
			list($x,$y,$w,$h) = $gp_img_meta[$num];
			sco35_var_put( $v, $e+0, $x );
			sco35_var_put( $v, $e+1, $y );
			sco35_var_put( $v, $e+2, $w );
			sco35_var_put( $v, $e+3, $h );
			return;
	}

	$type = ord( $file[$st+1] );
	switch( $type )
	{
		// G num:  num 番にリンクされてるCGを表示する
		case 0:
			$st += 2;
			$num = sco35_calli($file, $st);
			$c = -1;
			goto cg_add;
			return;
		// G num,c: (256色モードのみ/6万色モードでは不要)  num 番にリンクされてるCGを c 番の色だけ抜いて表示する
		case 1:
			$st += 2;
			$num = sco35_calli($file, $st);
			$c   = sco35_calli($file, $st);
			goto cg_add;
			return;
	}
	return;

cg_add:
	trace("cg G $type , $num , $c");

	// PC = palette read/decompress/cd
	if ( ! isset( $gp_pc["PPC"]) )
		$gp_pc["PPC"] = 7;

	// vsp/pms -> screen(cg)
	if ( $gp_pc["PPC"] & 1 )
	{
		trace("G PC 1 add_cg");
		sco35_g0_add( $num , $c );
		$run = false;
	}

	// program -> screen(clut)[fade-in/out]
	//if ( $gp_pc["PPC"] & 2 ) { }

	// vsp/pms -> program
	if ( $gp_pc["PPC"] & 4 )
	{
		trace("G PC 4 add_clut");
		sco35_g0_clut( $num );
	}
	return;
}

function I_cmd35( &$file, &$st, &$run )
{
	global $gp_pc, $gp_input, $gp_key;
	switch( $file[$st+1] )
	{
		// IC cursol_num,oldcursol  マウスカーソルの形状変更
		case 'C':
			$st += 2;
			$num = sco35_calli($file, $st);
			$old = sco35_calli($file, $st);
			trace("IC $num , $old");
			return;
		// IG var,code,num,reserve:  キー入力状態取得
		case 'G':
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$code = sco35_calli($file, $st);
			$num  = sco35_calli($file, $st);
			$resv = sco35_calli($file, $st);
			trace("IG $v+$e , $code , $num , $resv");
			sco35_var_put( $v, $e, 0 ); // 1=pressed 0=released
			return;
		// IK num:  キー入力状態取得
		// IK return RND = key
		case 'K':
			$bak = $st;
			$num = ord( $file[$st+2] );
			switch ( $num )
			{
				// 6 input data (2+3+4+5)
				case 5:
				case 6:
					$st += 3;
					if ( sco35_loop_IK0( $file, $st ) )
					{
						$st += 17;
						return;
					}

					if ( empty($gp_input) )
					{
						trace("IK $num wait = 0");
						$gp_pc["var"][0] = 0;
						$st = $bak;
						return;
					}

					$in = array_shift($gp_input);
					$gp_pc[ $in ] = $gp_input;
					$gp_input = array();

					if ( isset( $gp_pc["key"] ) )
					{
						$key = $gp_pc["key"][0];
						trace("IK $num key = %d", $key);
						$gp_pc["var"][0] = $key;
						unset( $gp_pc["key"] );
						return;
					}
					else
					{
						trace("IK $num $in");
						$gp_pc["var"][0] = $gp_key["enter"];
						return;
					}

					return;
				// 0/1 wait for input data (2+3+4+5)
				// 2 mouse input data
				// 3 keyboard input data
				// 4 joypad input data
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
					$st += 3;
					trace("IK $num = %d", $gp_pc["var"][0]);
					//trace("IK $num = 0");
					//$gp_pc["var"][0] = 0;
					return;
			}
			return;
		// IM cursol_x,cursol_y  マウスカーソルの座標取得
		// IM return RND = left/right click
		case 'M':
			$bak = $st;
			$st += 2;
			list($v1,$e1) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			list($v2,$e2) = sco35_varno($file, $st);
				$st++; // skip 0x7f

			if ( isset( $gp_pc["mouse"] ) )
			{
				list($x,$y) = $gp_pc["mouse"];
				sco35_var_put( $v1, $e1, $x );
				sco35_var_put( $v2, $e2, $y );
				$gp_pc["var"][0] = $gp_key["enter"];
				unset( $gp_pc["mouse"] );
				trace("IM mouse = $x , $y");
				return;
			}
			else
			{
				trace("IM $v1+$e1 , $v2+$e2 skip");
				$gp_pc["var"][0] = 0;
				return;
			}
/*
			if ( empty( $gp_input ) )
				//$st = $bak;
				trace("IM $v1+$e1 , $v2+$e2 {$gp_input[0]}");
				return;
*/
			return;
		// IX var:  「次の選択肢まで進む」の状態取得
		case 'X':
			$st += 2;
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			trace("IX $v+$e");
			sco35_var_put( $v, $e, 0 );
			return;
		// IY 0:  [次の選択肢まで進む]のフラグ解除
		// IY 2: 　次の選択肢まで進む状態のときに、選択肢もしくはマウスクリックでメッセージ送りを停止する様に指定
		// IY 3: 　次の選択肢まで進む状態のときに、選択肢もしくはマウスクリックでもメッセージ送りを停止しない様に指定
		case 'Y':
			$st += 2;
			$type = sco35_calli($file, $st);
			trace("IY $type");
			return;
		// IZ start_x,start_y:  マウスカーソルの座標を変更する (マウスカーソルはスムーズに移動する)
		case 'Z':
			$st += 2;
			$start_x = sco35_calli($file, $st);
			$start_y = sco35_calli($file, $st);
			trace("IZ $start_x , $start_y");
			return;
	}
	return;
}

// LC x,y,ﾌｧｲﾙ名:  CGを表示する
// LH? 1,no:  CDのデータをHDDへ登録する
// LH? 2,no:  CDのデータをHDDへ削除する
function L_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// LD num:  セーブデータをロードする（全ロード）
		// LD return RND 0 = OK , 200 > ERROR
		case 'D':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("save LD $num");
			$pc = pc_load( "sav{$num}" );
			if ( empty($pc) )
				$gp_pc["var"][0] = 255;
			else
			{
				$gp_pc = $pc;
				$st = $pc["pc"][1];
			}
			// copy( SAVE_FILE."sav{$num}" , SAVE_FILE."pc" );
			return;
		// LE type,file_name,start_var,read_num:  変数の値をファイルから読み込む
		// LE return RND 0 = OK , 200 > ERROR
		case 'E':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				case 0: // int
				case 1: // str
					$st += 3;
					$fn = sco35_ascii( $file, $st, ':' );
					list($v,$e) = sco35_varno( $file, $st );
						$st++; // skip 0x7f
					$read_num  = sco35_calli($file, $st);
					trace("LE $type , $fn , $v+$e , $read_num");
					$gp_pc["var"][0] = 255;
					return;
			}
			return;
		// LL 0,link_no,start_var,read_num:  変数の値をリンクファイルから読み込む
		// LL return RND 0 = OK , 200 > ERROR
		case 'L':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				case 0:
					$st += 3;
					$link_no = sco35_calli($file, $st);
					list($v,$e) = sco35_varno( $file, $st );
						$st++; // skip 0x7f
					$read_num = sco35_calli($file, $st);
					trace("data LL $type , $link_no , $v+$e , $read_num");
					$gp_pc["page"][$v+$e] = sco35_load_data($link_no , $read_num);
					$gp_pc["var"][0] = 0;
					return;
			}
			return;
		// LP num,point,count:  セーブデータの一部分をロードする(数値変数部)
		// LP return RND 0 = OK , 200 > ERROR
		case 'P':
			$st += 2;
			$num = sco35_calli($file, $st);
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$count = sco35_calli($file, $st);
			trace("save LP $num , $v+$e , $count");

			$pc = pc_load( "sav{$num}" );
			if ( empty($pc) )
				$gp_pc["var"][0] = 255;
			else
			{
				for ( $i=0; $i < $count; $i++ )
					$gp_pc["var"][$v+$e+$i] = $pc["var"][$v+$e+$i];
				$gp_pc["var"][0] = 0;
			}
			return;
		// LT num,var:  タイムスタンプの読み込み
		// LT return RND 0 = OK , 200 > ERROR
		case 'T':
			$st += 2;
			$num = sco35_calli($file, $st);
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			if ( file_exists( SAVE_FILE . "sav{$num}" ) )
			{
				$mod = time2date( filemtime(SAVE_FILE . "sav{$num}") );
				sco35_var_put( $v, $e+0, $mod[0] ); // year 1980-2079
				sco35_var_put( $v, $e+1, $mod[1] ); // month 1-12
				sco35_var_put( $v, $e+2, $mod[2] ); // days 1-31
				sco35_var_put( $v, $e+3, $mod[3] ); // hour 0-23
				sco35_var_put( $v, $e+4, $mod[4] ); // minute 0-59
				sco35_var_put( $v, $e+5, $mod[5] ); // second 0-59
				$gp_pc["var"][0] = 0;
				trace("save LT $num , $v+$e (%d-%d-%d %d:%d:%d)", $mod[0], $mod[1], $mod[2], $mod[3], $mod[4], $mod[5]);
			}
			else
			{
				trace("save LT $num , $var");
				$gp_pc["var"][0] = 255;
			}
			return;
	}
	return;
}

// MA num1,num2:  num1 の文字列の後ろに num2 をつなげる
// MD dst_str_no,src_str_no,len:  文字列変数を指定長さ分コピーする
// ME dst_str_no,dst_pos,src_str_no,src_pos,len:  位置指定つきの文字列コピー
// MF var,dst_no,key_no,start_pos:  文字列中から指定文字列の位置を探す
// MF return RND 0 = OK , 255 = ERROR
// MH num1,fig,num2:  数値を文字列に変換する (参考;Hｺﾏﾝﾄﾞ)
// MP num1,num2:  指定の文字列を指定文字数だけ表示する(Xコマンドの桁数指定)
function M_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// MC num1,num2:  num1 , num2 を比較して結果を RND に返す (RND=0 不一致 , RND=1 一致)
		// MC return RND 0 = DIFF , 1 = SAME
		case 'C':
			$st += 2;
			$num1 = sco35_calli($file, $st);
			$num2 = sco35_calli($file, $st);
			trace("MC $num1 , $num2");
			if ( $gp_pc["X"][$num1] == $gp_pc["X"][$num2] )
				$gp_pc["var"][0] = 1;
			else
				$gp_pc["var"][0] = 0;
			return;
		case 'G':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// MG4, no:  表示文字列取得番号設定
				case 4:
					$st += 3;
					$no = sco35_calli($file, $st);
					trace("MG 4 , $no");
					return;
				// MG100,switch:  文字列表示オン/オフ
				case 100:
					$st += 3;
					$switch = sco35_calli($file, $st);
					trace("MG 100 , $switch");
					return;
				// MG0, sw:  表示文字列を文字列変数に取得する/しない切替
				// MG1, str_no:  表示文字列取得開始文字列番号の指定
				// MG2, sw:  表示文字列取得の文字列番号更新の設定
				// MG3, sw:  表示文字列取得の改頁時の動作の指定
				// MG5, var:  表示文字列取得番号取得
				// MG6, sw:  表示文字列取得の番号を強制更新/更新解除
				// MG7, var:  表示文字列取得、現在番号の取得済み文字数の取得
			}
			return;
		// MI dst_no,max_len,title:  ユーザーによる文字列の入力
		// MI return RND strlen
		case 'I':
			$st += 2;
			$dst_no  = sco35_calli($file, $st);
			$max_len = sco35_calli($file, $st);
			$title   = sco35_sjis($file, $st);
				$st++; // skip ':'
			trace("MI $dst_no , $max_len , $title");
			return;
		// ML var,str_no:  文字列の長さを取得する
		case 'L':
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$str_no = sco35_calli($file, $st);
			trace("ML $v+$e , $str_no");
			$str = base64_decode( $gp_pc["X"][$str_no] );
			$len = strlen($str) / 2;
			sco35_var_put($w, $e, (int)$len);
			return;
		// MM num1,num2:  num1 の文字列に num2 をコピーする
		case 'M':
			$st += 2;
			$num1 = sco35_calli($file, $st);
			$num2 = sco35_calli($file, $st);
			trace("MM $num1 , $num2");
			$gp_pc["X"][$num1] = $gp_pc["X"][$num2];
			return;
		// MS num,string:  Xコマンドで表示される文字列領域に文字列を入れる
		case 'S':
			$st += 2;
			$num    = sco35_calli($file, $st);
			$string = sco35_sjis($file, $st);
				$st++; // skip ':'
			trace("MS $num , %s", $string);
			$gp_pc["X"][$num] = base64_encode($string);
			return;
		// MT title:  ウインドウのタイトル文字列を設定する
		case 'T':
			$st += 2;
			$title = sco35_sjis($file, $st);
				$st++; // skip ':'
			trace("MT %s", $title);
			$gp_pc["MT"] = base64_encode($title);
			return;
		// MV version:  シナリオバージョンをシステムへ通知する
		case 'V':
			$st += 2;
			$version = sco35_calli($file, $st);
			trace("MV $version");
			$gp_pc["MV"] = $version;
			return;
		// MZ0, max_len,max_num,reserve:  文字列変数の文字数・個数の設定の変更
		case 'Z':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				case 0:
					$st += 3;
					$max_len = sco35_calli($file, $st);
					$max_num = sco35_calli($file, $st);
					$reserve = sco35_calli($file, $st);
					trace("MZ $type , $max_len , $max_num , $reserve");
					return;
			}
			return;
	}
	return;
}

// NI var,default,min,max:  数値入力
// NT ﾀｲﾄﾙ:  NIコマンドで表示するタイトルを設定する
// NR var1,var2:  var1にvar2のルートを求める
// N> var1,num,count,var2:  var1 から始まるcount個の変数からnumより大きいければ1を、以下ならば0を
// N< var1,num,count,var2:  var1から始まるcount個の変数からnumより小さければ1を、以上ならば0を
// N= var1,num,count,var2:  var1から始まるcount個の変数からnumに等しければ1を、等しくなければ0を
// N¥ var1,count:  var1から始まるcount個の変数の0,1を反転する
// N‾ var,count:  ﾋﾞｯﾄ反転する
// NDM str,w64n:  数値w64nを文字列領域strへ文字列として反映
// NDA str,w64n:  文字列領域strを数値としてw64nへ反映
// NDH str,w64n:  数値w64nを画面に表示（パラメータの意味はHコマンドに準拠）
function N_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// NB var1,var2,count:  var1 から始まるcount個の変数へ
		case 'B':
			$st += 2;
			list($v1,$e1) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			list($v2,$e2) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			$count = sco35_calli($file, $st);
			trace("NB $v1+$e1 , $v2+$e2 , $count");
			$copy = sco35_var_get($v1, $e1, $count);
			sco35_var_put($v2, $e2, $copy);
			return;
		// NC var1,count:  var1から始まるcount個の変数を0でクリアする
		case 'C':
			$st += 2;
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			$count = sco35_calli($file, $st);
			trace("NC $v+$e , $count");
			$copy = array_fill(0, $count , 0);
			sco35_var_put($v, $e, $copy);
			return;
		case 'D':
			switch( $file[$st+2] )
			{
				// NDC w64n,num:  w64nにnumをコピーする
				case 'C':
					$st += 3;
					$w64n = sco35_calli($file, $st);
					$num  = sco35_calli($file, $st);
					trace("NDC $w64n , $num");
					$gp_pc["ND"][$w64n] = $num;
					return;
				// NDD var,w64n:  verにw64nをコピーする(変数に読み出す)
				case 'D':
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					$w64n = sco35_calli($file, $st);
					trace("NDD $v+$e , $w64n");
					sco35_var_put($v, $e, $gp_pc["ND"][$w64n]);
					return;
				// ND+ w64n1,w64n2,w64n3:  w64n2とw64n3を足してw64n1に代入
				case '+':
					$opr = "add";
					goto w64_math;
					return;
				// ND- w64n1,w64n2,w64n3:  w64n2からw64n3を引いてw64n1に代入
				case '-':
					$opr = "sub";
					goto w64_math;
					return;
				// ND* w64n1,w64n2,w64n3:  w64n2とw64n3を掛けてw64n1に代入
				case '*':
					$opr = "mul";
					goto w64_math;
					return;
				// ND/ w64n1,w64n2,w64n3:  w64n2をw64n3で割ってw64n1に代入
				case '/':
					$opr = "div";
					goto w64_math;
					return;
			}
			return;
		case 'O':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// NO 0,dst_var,src_var,bit_num:  変数並びをビット列に圧縮する
				// NO 1,dst_var,src_var,bit_num:  ビット列を変数並びに展開する
				case 0:
				case 1:
					$st += 3;
					list($v1,$e1) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					list($v2,$e2) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					$bit_num = sco35_calli($file, $st);
					trace("NO $type , $v1+$e1 , $v2+$e2 , $bit_num");
					return;
			}
			return;
		// N& var1,count,var2:  var1,var2のcount個の変数のANDをとる
		case '&': // 0x26
			$opr = "and";
			goto n_bits;
			return;
		// N| var1,count,var2:  var1から始まるcount個の変数のORをとる
		case '|': // 0x7c
			$opr = "or";
			goto n_bits;
			return;
		// N^ var1,count,var2:  var1から始まるcount個の変数のXORをとる
		case '^': // 0x5e
			$opr = "xor";
			goto n_bits;
			return;
		// N+ var1,num,count:  var1から始まるcount個の変数にnumを足す
		case '+': // 0x2b
			$opr = "add";
			goto n_math;
			return;
		// N- var1,num,count:  var1から始まるcount個の変数からnumを引く
		case '-': // 0x2d
			$opr = "sub";
			goto n_math;
			return;
		// N* var1,num,count:  var1から始まるcount個の変数にnumを掛ける
		case '*': // 0x2a
			$opr = "mul";
			goto n_math;
			return;
		// N/ var1,num,count:  var1から始まるcount個の変数をnumで割る
		case '/': // 0x2f
			$opr = "div";
			goto n_math;
			return;
	}
	return;

w64_math:
	$st += 3;
	$w64n1 = sco35_calli($file, $st);
	$w64n2 = sco35_calli($file, $st);
	$w64n3 = sco35_calli($file, $st);
	trace("ND_$opr $w64n1 , $w64n2 , $w64n3");
	$gp_pc["ND"][$w64n1] = var_math( $opr, $gp_pc["ND"][$w64n2] , $gp_pc["ND"][$w64n3] );
	return;
n_bits:
	$st += 2;
	list($v1,$e1) = sco35_varno( $file, $st );
		$st++; // skip 0x7f
	$count = sco35_calli($file, $st);
	list($v2,$e2) = sco35_varno( $file, $st );
		$st++; // skip 0x7f
	trace("data N_$opr $v1+$e1 , $count , $v2+$e2");

	$copy1 = sco35_var_get($v1, $e1, $count);
	$copy2 = sco35_var_get($v2, $e2, $count);
	sco35_n_math( $opr, $copy1, $copy2 );
	sco35_var_put($v1, $e1, $copy1);
	return;
n_math:
	$st += 2;
	list($v,$e) = sco35_varno( $file, $st );
		$st++; // skip 0x7f
	$num   = sco35_calli($file, $st);
	$count = sco35_calli($file, $st);
	trace("data N_$opr $v+$e , $num , $count");

	$copy = sco35_var_get($v, $e, $count);
	sco35_n_math( $opr, $copy, $num );
	sco35_var_put($v, $e, $copy);
	return;
}

// PD num:  CG展開の明度を指定する
// PT 0,var,x,y:  (256色用) 指定座標の色番号を取得する
// PT 1,r_var,g_var,b_var,x,y:  (ﾌﾙｶﾗｰ専用) 指定座標の色を取得する
function P_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// PC num:  システム制御コマンドにつき通常は使用しないこと
		case 'C':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("PC $num");
			$gp_pc["PPC"] = $num;
			return;
		case 'F':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// PF0,num:  // fade in
				// PF1,num:  // fade out
				case 0:
				case 1:
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("PF $type , $num");
					return;
				// PF2,num,wait_flag:  グラフィック画面をフェードインする（黒画面→通常画面）
				// PF3,num,wait_flag:  グラフィック画面をフェードアウトする（通常画面→黒画面）
			}
		// PG ver,num1,num2:  直接画面には反映されないので使用注意 CLUT_READ
		case 'G':
			$st += 2;
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			$num1 = sco35_calli($file, $st);
			$num2 = sco35_calli($file, $st);
			trace("PG $v+$e , $num1 , $num2");
			return;
		// PP ver,num1,num2:  直接画面には反映されないので使用注意 CLUT_WRITE
		case 'P':
			$st += 2;
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			$num1 = sco35_calli($file, $st);
			$num2 = sco35_calli($file, $st);
			trace("PP $v+$e , $num1 , $num2");
			return;
		// PS Plane,Red,Green,Blue:  直接画面には反映されないので使用注意
		case 'S':
			$st += 2;
			$plane = sco35_calli($file, $st);
			$red   = sco35_calli($file, $st);
			$green = sco35_calli($file, $st);
			$blue  = sco35_calli($file, $st);
			trace("PS $plane , $red , $green , $blue");
			$clr = sprintf("#%02x%02x%02x", $red , $green , $blue);
			$gp_pc["PS"][$plane] = $clr;
			return;
		case 'W':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// PW0,num: // fade in
				// PW1,num: // fade out
				case 0:
				case 1:
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("PW $type , $num");
					return;
				// PW2,num,wait_flag:  グラフィック画面をホワイトフェードインする（白画面→通常画面）
				// PW3,num,wait_flag:  グラフィック画面をホワイトフェードアウトする（通常画面→白画面）
			}
			return;
	}
	return;
}

// QP num,point,count:  変数領域などのデータを一部セーブする(数値変数部)
// QP return RND 1 = OK , 200 > ERROR
// QC num1,num2:  セーブファイルをnum2の領域からnum1の領域へコピー
// QC return RND 1 = OK , 200 > ERROR
function Q_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// QD num:  変数領域などのデータをセーブする（全セーブ）
		// QD return RND 1 = OK , 200 > ERROR
		case 'D':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("save QD $num");
			pc_save( "sav{$num}", $gp_pc );
			$gp_pc["var"][0] = 1;
			return;
		// QE 0,file_name,start_var,write_num:  変数の値をファイルに書き込む
		// QE return RND 1 = OK , 200 > ERROR
		case 'E':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				case 0: // int
				case 1: // str
					$st += 3;
					$fn = sco35_ascii( $file, $st, ':' );
					list($v,$e) = sco35_varno( $file, $st );
						$st++; // skip 0x7f
					$write_num  = sco35_calli($file, $st);
					trace("QE $type , $fn , $v+$e , $write_num");
					$gp_pc["var"][0] = 255;
					return;
			}
			return;
	}
	return;
}

// SM no:  PCMデータをメモリ上に乗せる
// SO var:  PCMデバイスのサポート情報を取得
// SQ noL, noR, loop:  左右別々のPCMデータを合成して演奏する
// SW var,channel,S-rate,bit:  指定データ形式が演奏出来るかチェックする．
function S_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// SC var:  CDの再生位置を取得する
		case 'C':
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			trace("bgm SC $v+$e += 4");
			sco35_var_put($v, $e+0, 999); // track
			sco35_var_put($v, $e+1, 999); // min
			sco35_var_put($v, $e+2, 999); // sec
			sco35_var_put($v, $e+3, 999); // frame
			return;
		case 'G':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// SG 0,0:  演奏中のMIDIを停止する
				// SG 1,num:  MIDIを演奏する
				case 0:
				case 1:
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("midi SG 1 , $num");
					$gp_pc["bgm"] = array("midi", $num);
					return;
				// SG 2,var:  MIDI演奏位置を1/100秒単位で取得する
				case 2:
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("midi SG 2 , $v+$e");
					sco35_var_put($v, $e, 999);
					return;
				// SG 3,0:  演奏中のMIDIを一時停止する
				// SG 3,1:  一時停止中のMIDIの一時停止を解除する
				case 3:
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("midi SG 3 , $num");
					return;
			}
			return;
		// SL num:  次の音楽(CD)のループ回数を指定する
		case 'L':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("bgm SP $num");
			return;
		// SP no,loop:  PCMデータを演奏する
		case 'P':
			$st += 2;
			$no   = sco35_calli($file, $st);
			$loop = sco35_calli($file, $st);
			trace("wave SP $no , $loop");
			$gp_pc["SP"] = $no;
			return;
		// SS num:  音楽演奏を開始する(CD)
		case 'S':
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("bgm SS $num");
			$gp_pc["bgm"] = array("audio", $num);
			return;
		// ST time:  PCMデータの演奏を停止する
		case 'T':
			$st += 2;
			$time = sco35_calli($file, $st);
			trace("wave ST $time");
			return;
		// SU var1,var2:  PCMの演奏状態を変数 var1 , var2 に返す
		case 'U':
			$st += 2;
			list($v1,$e1) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			list($v2,$e2) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			trace("wave SU $v1+$e1 , $v2+$e2");
			sco35_var_put($v1, $e1, 0); // 0=stop , 1=play
			sco35_var_put($v2, $e2, 100); // timer 1/100
			return;
	}
	return;
}

// UC mode,num:  (getd,cali) ラベル・シナリオコールのスタックフレームを削除する
// UD mode:  (cali) mode = モード
// UR var:  ｽﾀｯｸ情報取得
// UP 3,work_dir,file_name:  外部ﾌﾟﾛｸﾞﾗﾑ起動後SYSTEM3.5終了
function U_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// UG 変数,cali:  システム制御コマンドにつき通常は使用しないこと
		// stack pop
		case 'G':
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$cali = sco35_calli($file, $st);
			trace("UG $v+$e , $cali");
			while ( $cali > 0 )
			{
				$cali--;
				$p = $v + $e + $cali;
				$gp_pc["var"][$p] = array_shift( $gp_pc["stack"] );
			}
			return;
		// US 変数,cali:  システム制御コマンドにつき通常は使用しないこと
		// stack push
		case 'S':
			$st += 2;
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$cali = sco35_calli($file, $st);
			trace("US $v+$e , $cali");
			for ( $i=0; $i < $cali; $i++ )
			{
				$p = $v + $e + $i;
				array_unshift( $gp_pc["stack"] , $gp_pc["var"][$p] );
			}
			return;
	}
	return;
}

// VF:  ユニットマップの画面への反映
// VT sp,sa,sx,sy,cx,cy,dp,da,dx,dy:  ユニットマップデータを矩形指定でコピーする
// VIC x,y,cx,cy:  ユニットマップの画面反映(ユニットマップ座標指定)
// VIP x,y,cx,cy:  ユニットマップの画面反映(画面座標指定)
function V_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// VC nPageNum,x0Map,y0Map,cxMap,cyMap,cxUnit,cyUnit:  ユニットマップディスプレイ表示領域の設定 （転送先設定）
		// VC return RND 0 = ERROR , 1 = OK
		case 'C':
			$st += 2;
			$nPageNum = sco35_calli($file, $st);
			$x0Map  = sco35_calli($file, $st);
			$y0Map  = sco35_calli($file, $st);
			$cxMap  = sco35_calli($file, $st);
			$cyMap  = sco35_calli($file, $st);
			$cxUnit = sco35_calli($file, $st);
			$cyUnit = sco35_calli($file, $st);
			trace("VC $nPageNum , $x0Map , $y0Map , $cxMap , $cyMap , $cxUnit , $cyUnit");
			$gp_pc["VC"] = array( $nPageNum , $x0Map , $y0Map , $cxMap , $cyMap , $cxUnit , $cyUnit );
			$gp_pc["var"][0] = 1;
			return;
		// VE pos_x,pos_y,len_x,len_y,out_ptn,flag:  範囲指定付きでユニットマップの内容を画面に描画する
		case 'E':
			$st += 2;
			$pos_x = sco35_calli($file, $st);
			$pos_y = sco35_calli($file, $st);
			$len_x = sco35_calli($file, $st);
			$len_y = sco35_calli($file, $st);
			$out_ptn = sco35_calli($file, $st);
			$flag  = sco35_calli($file, $st);
			trace("VE $pos_x , $pos_y , $len_x , $len_y , $out_ptn , $flag");
			$src = array( $pos_x , $pos_y , $len_x , $len_y , $out_ptn , $flag );
			sco35_vp_div_add( $src );
			return;
		// VG nPage,nType,x,y:  ユニットマップの値の取得
		// VG return RND Data
		case 'G':
			$st += 2;
			$nPage = sco35_calli($file, $st);
			$nType = sco35_calli($file, $st);
			$x = sco35_calli($file, $st);
			$y = sco35_calli($file, $st);

			$varno = $gp_pc["VR"][$nPage][$nType];
			list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];
			$pos = ($y * $mx) + $x;

			$bak = $gp_pc["var"][$varno][$pos];
			trace("VG $nPage , $nType , $x , $y = %d", $bak);
			$gp_pc["var"][0] = $bak;
			return;
		// VH nPage,x,y,lengs_x,lengs_y,max:  歩数ペイント
		case 'H':
			$st += 2;
			$nPage = sco35_calli($file, $st);
			$x = sco35_calli($file, $st);
			$y = sco35_calli($file, $st);
			$lengs_x = sco35_calli($file, $st);
			$lengs_y = sco35_calli($file, $st);
			$max = sco35_calli($file, $st);
			trace("VH $nPage , $x , $y , $lengs_x , $lengs_y , $max");
			return;
		// VP nPage,x0Unit,y0Unit,nxUnit,nyUnit,bSpCol:  ユニットCG取得位置＆並び状態設定＆スプライト色指定 (転送元設定)
		case 'P':
			$st += 2;
			$nPage  = sco35_calli($file, $st);
			$x0Unit = sco35_calli($file, $st);
			$y0Unit = sco35_calli($file, $st);
			$nxUnit = sco35_calli($file, $st);
			$nyUnit = sco35_calli($file, $st);
			$bSpCol = sco35_calli($file, $st);
			trace("VP $nPage , $x0Unit , $y0Unit , $nxUnit , $nyUnit , $bSpCol");
			$src = array( $nPage , $x0Unit , $y0Unit , $nxUnit , $nyUnit , $bSpCol );
			$gp_pc["VP"][$nPage] = sco35_vp_g0( $src );
			$gp_pc["VP"][$nPage]["set"] = $src;
			return;
		// VR nPage,nType,var:  変数→MAPデータ転送
		case 'R':
			$st += 2;
			$nPage = sco35_calli($file, $st);
			$nType = sco35_calli($file, $st);
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			trace("VR $nPage , $nType , $v+$e");
			$gp_pc["VR"][$nPage][$nType] = $v+$e;
			return;
		// VS nPage,nType,x,y,wData:  ユニットマップへの値のセット
		// VS return RND previous wData
		case 'S':
			$st += 2;
			$nPage = sco35_calli($file, $st);
			$nType = sco35_calli($file, $st);
			$x = sco35_calli($file, $st);
			$y = sco35_calli($file, $st);
			$wData = sco35_calli($file, $st);
			trace("VS $nPage , $nType , $x , $y , $wData");

			$varno = $gp_pc["VR"][$nPage][$nType];
			list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];
			$pos = ($y * $mx) + $x;

			$bak = $gp_pc["page"][$varno][$pos];
			$gp_pc["page"][$varno][$pos] = $wData;
			$gp_pc["var"][0] = $bak;
			return;
		// VV nPage,fEnable:  ユニットマップの層ごとの表示有効/無効の切り替え
		case 'V':
			$st += 2;
			$nPage   = sco35_calli($file, $st);
			$fEnable = sco35_calli($file, $st);
			trace("VV $nPage , $fEnable");
			$gp_pc["VV"][$nPage] = $fEnable;
			return;
		// VW nPage,nType,var:  MAP→変数データ転送
		case 'W':
			$st += 2;
			$nPage = sco35_calli($file, $st);
			$nType = sco35_calli($file, $st);
			list($v,$e) = sco35_varno( $file, $st );
				$st++; // skip 0x7f
			trace("VW $nPage , $nType , $v+$e");
			$gp_pc["VR"][$nPage][$nType] = $v+$e;
			//unset( $gp_pc["VR"][$nPage][$nType] );
			return;
		case 'X':
			$bak = $st;
			$st += 2;
			$type = sco35_calli($file, $st);
			switch ( $type )
			{
				// VX 0,nPage,x0Unit,y0Unit:  ユニットCG取得位置の変更 (VP x0Unit,y0Unit)
				case 0:
					$nPage  = sco35_calli($file, $st);
					$x0Unit = sco35_calli($file, $st);
					$y0Unit = sco35_calli($file, $st);
					trace("VX 0 , $nPage , $x0Unit , $y0Unit");

					$src = $gp_pc["VP"][$nPage]["set"];
					$src[1] = $x0Unit;
					$src[2] = $y0Unit;

					//$gp_pc["VP"][$nPage] = sco35_vp_g0( $src );
					$gp_pc["VP"][$nPage]["set"] = $src;
					return;
				// VX 1,nPage,nxUnit,nyUnit:  ユニットCG取得並び個数の変更 (VP nxUnit,nyUnit)
				case 1:
					$nPage  = sco35_calli($file, $st);
					$nxUnit = sco35_calli($file, $st);
					$nyUnit = sco35_calli($file, $st);
					trace("VX 1 , $nPage , $nxUnit , $nyUnit");

					$src = $gp_pc["VP"][$nPage]["set"];
					$src[3] = $nxUnit;
					$src[4] = $nyUnit;

					//$gp_pc["VP"][$nPage] = sco35_vp_g0( $src );
					$gp_pc["VP"][$nPage]["set"] = $src;
					return;
				// VX 2,nPage,bSpCol,reserve:  ユニットCGスプライト色変更 (VP bSpCol)
				// VX 3,nPage,0,0:  VFのユニットマップ全反映指定
			}
			$st = $bak;
			return;
		case 'Z':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// VZ 1,nPage,unit_no:  透明パターン番号の指定(ﾃﾞﾌｫﾙﾄでは指定無し)
				case 1:
					$st += 3;
					$nPage = sco35_calli($file, $st);
					$unit_no = sco35_calli($file, $st);
					trace("VZ 1 , $nPage , $unit_no");
					return;
				// VZ 2,x0Map,y0Map:  ユニットマップ表示位置の変更 (VC x0Map,y0Map)
				case 2:
					$st += 3;
					$x0Map = sco35_calli($file, $st);
					$y0Map = sco35_calli($file, $st);
					trace("VZ 2 , $x0Map , $y0Map");
					$gp_pc["VC"][1] = $x0Map;
					$gp_pc["VC"][2] = $y0Map;
					return;
				// VZ 3,cxUnit,cyUnit:  ユニットサイズの変更 (VC cxUnit,cyUnit)
				case 3:
					$st += 3;
					$cxUnit = sco35_calli($file, $st);
					$cyUnit = sco35_calli($file, $st);
					trace("VZ 3 , $cxUnit , $cyUnit");
					$gp_pc["VC"][5] = $cxUnit;
					$gp_pc["VC"][6] = $cyUnit;
					return;
				// VZ 0,nPage,reserve:  透明パターン番号指定解除(ﾃﾞﾌｫﾙﾄでは解除されている)
			}
			return;
	}
	return;
}

function W_cmd35( &$file, &$st, &$run )
{
	global $gp_pc;
	switch( $file[$st+1] )
	{
		// WV start_x,start_y,size_x,size_y:  VIEW領域を設定する
		case 'V':
			$st += 2;
			$start_x = sco35_calli($file, $st);
			$start_y = sco35_calli($file, $st);
			$size_x  = sco35_calli($file, $st);
			$size_y  = sco35_calli($file, $st);
			trace("WV $start_x , $start_y , $size_x, $size_y");
			$gp_pc["WV"] = array($start_x , $start_y , $size_x, $size_y);
			return;
		// WW x_size,y_size,color:  全画面領域を設定する
		case 'W':
			$st += 2;
			$x_size = sco35_calli($file, $st);
			$y_size = sco35_calli($file, $st);
			$color  = sco35_calli($file, $st);
			trace("WW $x_size , $y_size , $color");
			$gp_pc["WW"] = array($x_size , $y_size , $color);
			return;
		// WX x0,y0,cx,cy:  画面反映 on (指定範囲のみ再描画)
		case 'X':
			$st += 2;
			$x0 = sco35_calli($file, $st);
			$y0 = sco35_calli($file, $st);
			$cx = sco35_calli($file, $st);
			$cy = sco35_calli($file, $st);
			trace("WX $x0 , $y0 , $cx , $cy");
			return;
		// WZ0, sw:  描画DIB→画面反映のon/off切替
		case 'Z':
			$b1 = ord( $file[$st+2] );
			$st += 3;
			$sw = sco35_calli($file, $st);
			trace("WZ $b1 , $sw");
			return;
	}
	return;
}

// ZB 太さ:  メッセージ文字を太さを設定
// ZL line:  メッセージ領域の文字の縦方向行間ドット数を指定する
function Z_cmd35( &$file, &$st, &$run )
{
	global $gp_pc, $gp_input, $gp_key;
	switch( $file[$st+1] )
	{
		// ZA 0,type:  文字飾りの種類を指定する
		// ZA 1,col:  文字飾りに用いる色の指定
		// ZA 3,mode:  自動改頁制御
		case 'A':
			$b1 = ord( $file[$st+2] );
			$st += 3;
			$b2 = sco35_calli($file, $st);
			trace("ZA $b1 , $b2");
			$gp_pc["ZA"] = array($b1 , $b2);
			return;
		// ZC m,n:  システムの使用環境を変更する
		case 'C':
			$st += 2;
			$m = sco35_calli($file, $st);
			$n = sco35_calli($file, $st);
			trace("ZC $m , $n");
			$gp_pc["ZC"][$m] = $n;
			return;
		case 'D':
			$type = ord( $file[$st+2] );
			switch( $type )
			{
				// ZD0,sw:  デバッグモード時のデバッグメッセージの出力 ON/OFF/PAUSE
				case 0:
					$st += 3;
					$sw = sco35_calli($file, $st);
					trace("ZD $type , $sw");
					return;
				// ZD1,sw:  デバッグ用コマンド change disc dialog
				case 1:
					$st += 3;
					$sw = sco35_calli($file, $st);
					trace("ZD $type , $sw");
					return;
				// ZD2,num:  デバッグ用コマンド num dialog
				// ZD3,value:  デバッグ用コマンド value dialog
			}
			return;
		// ZE sw:  選択肢を選んだらメッセージ領域を初期化するかどうかを指定する
		case 'E':
			$st += 2;
			$sw = sco35_calli($file, $st);
			trace("ZE $sw");
			$gp_pc["ZE"] = $sw;
			return;
		// ZF sw:  選択肢枠サイズを可変にするか固定にするかを指定
		case 'F':
			$st += 2;
			$sw = sco35_calli($file, $st);
			trace("ZF $sw");
			$gp_pc["ZF"] = $sw;
			return;
		// ZG var:  CGのロードした回数をリンク番号毎に配列に書き込む配列を設定
		case 'G':
			$st += 2;
			$var = sco35_calli($file, $st);
			trace("ZG $var");
			return;
		// ZH switch:  全角半角切替え
		case 'H':
			$st += 2;
			$switch = sco35_calli($file, $st);
			trace("ZH $switch");
			$gp_pc["ZH"] = $switch;
			return;
		// ZI key,mode:  Aコマンドのキー入力待ち時の各キーの動作の指定
		case 'I':
			$st += 2;
			$key  = sco35_calli($file, $st);
			$mode = sco35_calli($file, $st);
			trace("ZI $key , $mode");
			return;
		// ZM size:  シナリオメッセージのフォントサイズを指定する
		case 'M':
			$st += 2;
			$size = sco35_calli($file, $st);
			trace("ZM $size");
			$gp_pc["ZM"] = $size;
			return;
		// ZS size:  選択肢のフォントサイズを指定する
		case 'S':
			$st += 2;
			$size = sco35_calli($file, $st);
			trace("ZS $size");
			$gp_pc["ZS"] = $size;
			return;
		case 'T':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// ZT0,var:  現在の日時を var0〜var6 の変数列に返す
				case 0:
					$st += 3;
					//$var = sco35_calli($file, $st);
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("ZT $type , $v+$e += 6");
					$time = time2date( time() );
					sco35_var_put( $v, $e+0, $time[0] ); // year 1980-2079
					sco35_var_put( $v, $e+1, $time[1] ); // month 1-12
					sco35_var_put( $v, $e+2, $time[2] ); // days 1-31
					sco35_var_put( $v, $e+3, $time[3] ); // hour 0-23
					sco35_var_put( $v, $e+4, $time[4] ); // minute 0-59
					sco35_var_put( $v, $e+5, $time[5] ); // second 0-59
					sco35_var_put( $v, $e+6, 1 ); // day 1-7:Sun-Sat
					return;
				// ZT1,n:  タイマーを n の数値でクリアする
				case 1:
					$st += 3;
					$var = sco35_calli($file, $st);
					trace("ZT $type , $var");
					$gp_pc["ZT"] = 0;
					return;
				// ZT2,var:  タイマーを var に取得する 1/10
				// ZT3,var:  タイマーを var に取得する 1/30
				// ZT4,var:  タイマーを var に取得する 1/60
				// ZT5,var:  タイマーを var に取得する 1/100
				case 2:
				case 3:
				case 4:
				case 5:
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("ZT $type , $v+$e");
					$gp_pc["ZT"] += 1;
					sco35_var_put( $v, $e, $gp_pc["ZT"] );
					if ( $gp_pc["ZT"] > 0 )
						$gp_input = array("key",-1);
					return;
			}
		// ZW sw:  CAPS 状態の内部的制御を変更する
		case 'W':
			$st += 2;
			$sw = sco35_calli($file, $st);
			trace("ZW $sw");
			$gp_pc["ZW"] = $sw;
			return;
		case 'Z':
			$type = ord( $file[$st+2] );
			switch ( $type )
			{
				// ZZ0,sw:  SYSTEM3.5を終了する
				// ZZ0 return RND 1/2 = OK , 254/255 = ERROR
				case 0:
					$bak = $st;
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("ZZ 0 , $num");
					$gp_pc["var"][0] = $num;
					$st = $bak;
					sco35_text_add( "_NEXT_" );
					sco35_text_add( "SYSTEM 3.5 END" );
					//$gp_pc["pc"] = array(0,0);
					return;
				// ZZ1,var:  現在の動作機種コードを var に返す
				case 1:
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("ZZ 1 , $v+$e");
					sco35_var_put( $v, $e, 1 ); // windows
					return;
				// ZZ2,num:  (新規) 機種文字列を文字列領域 num に返す(MAX12文字)
				case 2:
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("ZZ 2 , $num");
					return;
				// ZZ3,var:  WINDOWSの全画面サイズや表示色数を変数列に返す
				case 3:
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("ZZ 3 , $v+$e");
					return;
				// ZZ9,var:  起動時のｽｸﾘｰﾝｻｲｽﾞを取得する
				case 9:
					$st += 3;
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("ZZ 9 , $v+$e");
					list($w,$h,$c) = $gp_pc["WW"];
					sco35_var_put( $v, $e+0, $w ); // width
					sco35_var_put( $v, $e+1, $h ); // height
					sco35_var_put( $v, $e+2, $c ); // bit
					return;
				// ZZ13,num:  表示ﾌｫﾝﾄを設定する
				case 13: // 0xd
					$st += 3;
					$num = sco35_calli($file, $st);
					trace("ZZ 13 , $num");
					return;
				// ZZ4,var:  DIB の全画面 サイズや色数を変数列に返す
				// ZZ5,var:  SYSTEM3.5用表示画面 の サイズや色数を変数列に返す
				// ZZ7,var:  セーブドライブの残りディスク容量を得る
				// ZZ8,var:  メモリオンバッファの残り容量を得る
			}
			return;
	}
	return;
}

function sco35_cmd( &$id, &$st, &$run, &$select )
{
	$func = __FUNCTION__;
	global $sco_file, $gp_pc, $gp_input, $gp_key;
	$file = &$sco_file[$id];
	switch( $file[$st] )
	{
		case 'B':  return B_cmd35( $file, $st, $run );
		case 'C':  return C_cmd35( $file, $st, $run );
		case 'D':  return D_cmd35( $file, $st, $run );
		case 'E':  return E_cmd35( $file, $st, $run );
		case 'F':  return F_cmd35( $file, $st, $run );
		case 'G':  return G_cmd35( $file, $st, $run );
		case 'I':  return I_cmd35( $file, $st, $run );
		case 'J':  return J_cmd35( $file, $st, $run );
		case 'L':  return L_cmd35( $file, $st, $run );
		case 'M':  return M_cmd35( $file, $st, $run );
		case 'N':  return N_cmd35( $file, $st, $run );
		case 'P':  return P_cmd35( $file, $st, $run );
		case 'Q':  return Q_cmd35( $file, $st, $run );
		case 'S':  return S_cmd35( $file, $st, $run );
		case 'U':  return U_cmd35( $file, $st, $run );
		case 'V':  return V_cmd35( $file, $st, $run );
		case 'W':  return W_cmd35( $file, $st, $run );
		case 'Y':  return Y_cmd35( $file, $st, $run );
		case 'Z':  return Z_cmd35( $file, $st, $run );
		// A  キー入力待ちをして、入力があればメッセージ領域の初期化と
		case 'A':
			trace("text NEXT");
			if ( ! empty( $gp_input ) )
			{
				if ( $gp_input[0] == "key" && $gp_input[1] < 0 )
					return;
				$st++;
				sco35_text_add( "_NEXT_" );
				$gp_input = array();
			}
			return;
		// H fig,num:  数字を表示する
		case 'H':
			$fig = ord( $file[$st+1] );
			$st += 2;
			$num = sco35_calli($file, $st);
			trace("text H $fig , $num");
			$str = sprintf("%0{$fig}d ", $num);
			sco35_text_add( $str );
			return;
		// R  メッセージ領域の文字列を改行する
		case 'R':
			$st++;
			trace("text CRLF");
			sco35_text_add( "_CRLF_" );
			return;
		// T text_x,text_y:  文字の表示開始座標を指定する
		case 'T':
			$st++;
			$text_x = sco35_calli($file, $st);
			$text_y = sco35_calli($file, $st);
			trace("text T  $text_x , $text_y");
			$gp_pc["T"] = array($text_x , $text_y);
			return;
		// X num:  指定の文字列を表示する
		case 'X':
			$st++;
			$num = sco35_calli($file, $st);
			trace("text X  $num");
			sco35_text_add( base64_decode( $gp_pc["X"][$num] ) );
			return;

		case ':': // 0x3a
			$st++;
			trace("NOP");
			return;
		case '!': // 0x21
			$st++;
			list($v,$e) = sco35_varno($file, $st);
			$exp = sco35_calli($file, $st);
			trace("var_$v+$e = $exp");
			sco35_var_put( $v, $e, $exp );
			return;
		case ' ': // 0x20
			$jp = sco35_sjis($file, $st);
			trace("text %s", $jp);
			sco35_text_add( $jp );
			return;
		// $label$文字列$  選択肢を登録する
		case '$': // 0x24
			if ( $select ) // within select loop
			{
				$select = false;
				return;
			}
			else
			{
				$st++;
				$sel_jmp = str2int( $file, $st, 4 );
				trace("select loc_%x , TEXT", $sel_jmp);

				$bak = $gp_pc["text"];
				$gp_pc["text"] = array();
				$loop = true;
				$select = true;
				while ( $select )
				{
					trace("= select_%x : ", $gp_pc["pc"][1]);
					$func($gp_pc["pc"][0], $gp_pc["pc"][1], $loop, $select);
				}
				$sel_txt = "";
				foreach ( $gp_pc["text"] as $text )
					$sel_txt .= base64_decode($text['jp']);
				$gp_pc["text"] = $bak;
					$gp_pc["pc"][1]++; // skip '$'

				trace("select TEXT = $sel_txt");
				$gp_pc["select"][] = array( $sel_jmp , base64_encode($sel_txt) );
			}
			return;
		// ]  選択肢を開く
		case ']': // 0x5d
			if ( isset( $gp_pc["select"]['B'] ) )
				unset( $gp_pc["select"]['B'] );

			$gp_pc["select"]['B'] = array(
				$st + 1,
				base64_encode("&lt;&lt;&lt;"),
			);

			if ( ! empty( $gp_input ) )
			{
				if ( $gp_input[0] == "select" )
				{
					$sel = $gp_input[1];
					if ( isset( $gp_pc["select"][$sel] ) )
					{
						trace("select $sel");
						$gp_pc["pc"][1] = $gp_pc["select"][$sel][0];
						$gp_pc["select"] = array();
						$gp_input = array();
						sco35_text_add( "_NEXT_" );
						return;
					}
				}
			}
			trace("select menu");
			return;
		case '{': // 0x7b
			if ( sco35_loop_inf( $file, $st ) )
				return;
			if ( sco35_loop_IK0( $file, $st ) )
			{
				$st += 17;
				return;
			}

			$st++;
			$exp = sco35_calli($file, $st);
			$end = str2int( $file, $st, 4 );
			trace("if not %d then goto loc_%x", $exp, $end);
			if ( ! $exp )
				$gp_pc["pc"][1] = $end;
			return;
		// < var,start,end,sign,step:  FORループ開始
		case '<': // 0x3c
			$type = ord( $file[$st+1] );
			$st += 2;
			if ( $type == 0 )
				$st += 2;
			$done = str2int( $file, $st, 4 );
			list($v,$e) = sco35_varno($file, $st);
				$st++; // skip 0x7f
			$end  = sco35_calli($file, $st);
			$sign = sco35_calli($file, $st);
			$step = sco35_calli($file, $st);

			if ( $sign ) // i++
			{
				if ( $type )
					$gp_pc["var"][$v+$e] += $step;

				$var = $gp_pc["var"][$v+$e];
				if ( $var <= $end )
					trace("for ($var += $step) < $end");
				else
				{
					trace("for end  loc_%x", $done);
					$gp_pc["pc"][1] = $done;
				}
				return;
			}
			else // i--
			{
				if ( $type )
					$gp_pc["var"][$v+$e] -= $step;

				$var = $gp_pc["var"][$v+$e];
				if ( $var >= $end )
					trace("for ($var -= $step) > $end");
				else
				{
					trace("for end  loc_%x", $done);
					$gp_pc["pc"][1] = $done;
				}
				return;
			}
			return;
		// >  FORループ終了
		case '>': // 0x3e
			$st++;
			$forloop = str2int( $file, $st, 4 );
			trace("for loop  loc_%x", $forloop);
			$gp_pc["pc"][1] = $forloop;
			return;
		// #label,number:  データを読み込む位置を指定する (関連 Fｺﾏﾝﾄﾞ)
		case '#': // 0x23
			$st++;
			$label  = str2int( $file, $st, 4 );
			$number = sco35_calli($file, $st);

			if ( $number )
			{
				$table = $label + (($number-1) * 4);
				$label = str2int( $file, $table, 4 );
			}

			trace("array [ %d ] = loc_%x", $number, $label);
			$gp_pc["F"] = array($gp_pc["pc"][0], $label);
			return;
		case '~': // 0x7e , function call
			$st++;
			$page = str2int( $file, $st, 2 );
			switch ( $page )
			{
				case 0:
					$var = sco35_calli($file, $st);
					trace("return func = $var");
					$gp_pc["return"] = $var;
					$jal = array_shift( $gp_pc["jal"] );
					$gp_pc["pc"] = $jal;
					return;
				case 0xffff:
					list($v,$e) = sco35_varno($file, $st);
						$st++; // skip 0x7f
					trace("return $v+$e = var");
					sco35_var_put($v, $e, $gp_pc["return"]);
					return;
				default:
					$label = str2int( $file, $st, 4 );
					trace("func sco_%d , loc_%x", $page, $label);

					array_unshift( $gp_pc["jal"], array($gp_pc["pc"][0], $gp_pc["pc"][1]) );
					$gp_pc["pc"] = array($page, $label);
					return;
			}
			return;
		// @mmmm:  ラベルジャンプ文
		case '@': // 0x40
			$st++;
			$label = str2int( $file, $st, 4 );
			trace("jump loc_%x", $label);
			$gp_pc["pc"][1] = $label;
			return;
		// &  page jump
		case '&': // 0x26
			$st++;
			$page = sco35_calli($file, $st) + 1;
			trace("jump sco_%d", $page);
			$gp_pc["pc"] = array($page, 0);
			return;
		// ¥mmmm:  ラベルコール文
		// \0  label return
		case '\\': // 0x5c
			$st++;
			$label = str2int( $file, $st, 4 );
			if ( $label == 0 )
			{
				trace("return label");
				$jal = array_shift( $gp_pc["jal"] );
				$gp_pc["pc"] = $jal;
			}
			else
			{
				trace("call loc_%x", $label);
				array_unshift( $gp_pc["jal"], array($gp_pc["pc"][0], $gp_pc["pc"][1]) );
				$gp_pc["pc"][1] = $label;
			}
			return;
		// %mmmm:  ページコール文
		// %0  page return
		case '%': // 0x25
			$st++;
			$page = sco35_calli($file, $st) + 1;
			if ( $page == 1 )
			{
				trace("return page");
				$jal = array_shift( $gp_pc["jal"] );
				$gp_pc["pc"] = $jal;
			}
			else
			{
				trace("call sco_%d", $page);
				array_unshift( $gp_pc["jal"], array($gp_pc["pc"][0], $gp_pc["pc"][1]) );
				$gp_pc["pc"] = array($page, 0);
			}
			return;
		default:
			$b1 = ord( $file[$st] );
			if ( $b1 & 0x80 )
			{
				$jp = sco35_sjis($file, $st);
				trace("text %s", $jp);
				sco35_text_add( $jp );
				return;
			}
			return;
	}
	return;
}
