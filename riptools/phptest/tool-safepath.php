<?php
declare( strict_types=1 );

require 'tool.inc';

$path = [
	'/attachments/398274632408629252/1498872203641622719/(01) [堕悪祭怪&昼] へたれサンドバッグ.flac',
	'/attachments/398278703161016320/1520869947176128573/(02) [ぱらどっと] Fountain Plaza.flac',
	'/attachments/822323293204905995/1158159541725319248/ADCreHdqpErSqCOC1zP0i8M3hpkusisBlfiNonSy1J0CQECC826m0gTYMM8dmqBNABmo6zHqKF7DLMNchv-ub725afobsuH7a-PVLU_S4uEIvGL9X4m-AMg9X1WRiATUoCPeXlYr64jA4ZoxXfXTsw_ffdUvN6RWSW2T9lM3UQmeLO2xRrkJCNUOFzz1IyoZndyBjG6SUf80Tz48omc6WIoB7diA6dA9j5okxBTSzSX1UQMZWOOiKbZGfvqq1E3sFTUcWEPniQYMgu2Bonf3C1Vb-4Ht9ooXZ8ne-jeZBE-L9xUTCxS_DPHS3u9CbhsqfzObWl_bpLkf_NyPKYVfXjMUV_ZSFmgsjq5HQkq5oqoSaZV2t5v5DNXQHjIkf6mXrYX3cgwXOQl8cyNZJdhD2_9hR2-sh0kX4ENu22yZOnhWt9OHUTIN2bntKR2vshRuTefxzKSfxAc0IjzX5j3HFqWOy9gkG5muolb6FIMNfzRf1aljfTCa793l0Kha.png',
	'/attachments/725293597188030525/1530925188043243652/preinterview_afterdeath_ea.stupid_.embodies_sough-uncontestable-Cantabrian.fore-elder_adnexed.Mozambican.endothelial_gold-fringed_Gloverville-unrayed-sugar-chopped-Torrasbench_Thallo_Ashfieldhydrolysed.Cambodialavacre-alchemistermonogynic-loomed_danger-fraught_thrice-crowned-barbulesunapproximate.colascione-Trans-balkanperradial.kwe-bird_Donellecommemorate_dishmop.aurivorousneedlewomansuperethically.extuberant.euchysideritevelocity-convoker.declaredness-flesh-colour_Mordella.jpg',
	//'',
];
foreach ( $path as $k => $v )
{
	$safe = tool::safepath($v, 15, 0x40);
	printf('[%x] %s'."\n", $k, $v);
	printf('-> %s'."\n", $safe);
} // foreach ( $path as $p )
