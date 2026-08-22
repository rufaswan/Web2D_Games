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
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-sh');
sh::which('iconv');

$iconv = 'iconv'
	. ' --silent'
	. ' --from-code=utf-8'
	. '   --to-code=windows-31j//IGNORE'
	. ' <<< "%s"';

// bit->byte      = U+xx
//  7->1 =     7f =  0|7f
// 11->2 =    7ff = c0|1f  80|3f
// 16->3 =   ffff = e0|f   80|3f  80|3f
// 21->4 = 1fffff = f8|7   80|3f  80|3f  80|3f
//         10ffff = f8|0   80|1f  80|3f  80|3f  = f8 9f bf bf

$json = "[\r\n";
// sjis is 3-byte utf-8
// from 800 to ffff
for ( $i=0x800; $i <= 0xffff; $i++ )
{
	$b1 = ($i >> 12) & 0x0f;  $b1 |= 0xe0;
	$b2 = ($i >>  6) & 0x3f;  $b2 |= 0x80;
	$b3 = ($i >>  0) & 0x3f;  $b3 |= 0x80;
	$utf = chr($b1) . chr($b2) . chr($b3);
	echo '.';

	$r = sh::exec($iconv, $utf);
	if ( empty($r) )
		continue;

	$json .= sprintf("  [ \"%s\" , \"%s\" ] ,\r\n", $utf, bin2hex($r));
} // for ( $i=0x800; $i <= 0xffff; $i++ )
echo "done\n";

$json .= "  0\r\n";
$json .= "]\r\n";
tool::save(__FILE__ .'.json', $json);


/*
No, there are absolutely no characters in Shift_JIS or CP932 that will map to a code point of U+XXXXXX (6-digit hex format starting at U+100000) or higher. [1]

In fact, nothing in CP932 maps anywhere near that range, though there is a subtle catch depending on how you define "Shift_JIS." [2]

------------------------------
## 1. Standard CP932 / Traditional Shift_JIS (No, max is 4 hex digits)

If you are strictly talking about CP932 (the Microsoft Windows extension of Shift_JIS) or standard Shift_JIS (JIS X 0208), every single character maps entirely inside the Basic Multilingual Plane (BMP). [2, 3, 4, 5, 6]

* Their Unicode mappings fall strictly between U+0000 and U+FFFF.
* When encoded into UTF-8, they will utilize a maximum of 3 bytes.
* Therefore, you will never see a 5-digit hex code point (U+XXXXX) or a 6-digit hex code point (U+XXXXXX) using these encodings. [3, 7, 8, 9, 10]

------------------------------
## 2. The Exception: Shift_JIS-2004 / JIS X 0213 (5-digit hex points)

If your mapping tool or decoder considers newer extensions like Shift_JIS-2004 (which implements the JIS X 0213 standard), a small group of about 300 rare Han characters (Plane 2 CJK Ideographs) will map to Plane 2 of Unicode. [6, 11]

An example of this is the Kanji character 𩸕 (a type of fish), which maps as follows: [5]

* Shift_JIS-2004 byte sequence: 0x9872
* Unicode Code Point: U+29E15 (Notice this is a 5-digit hex format, not 6)
* UTF-8 representation: 4 bytes (0xF0 0xA9 0xB8 0x95) [7]

------------------------------
## 3. Why U+XXXXXX and Above is Impossible

A 6-digit Unicode hex string starting with U+100000 belongs to Plane 16 (the final plane of Unicode). [3, 12]

The absolute architectural ceiling for the entire Unicode standard is U+10FFFF. Unicode purposefully blocks any code point from ever existing above this value to maintain compatibility with UTF-16 surrogate pairs. Because of this: [3, 13, 14, 15, 16]

* Code points formatting as 6-digit hex values only exist between U+100000 and U+10FFFF.
* These positions are reserved exclusively for Private Use Area (PUA) characters. No historical, native Japanese encoding has standard mappings to this area. [3, 6, 17, 18, 19]

## Summary Overview

| Encoding Variant | Max Unicode Code Point | Hex Format Length | Max UTF-8 Bytes |
|---|---|---|---|
| CP932 / Shift_JIS (JIS X 0208) | U+FFFF (Inside BMP) | 4 Digits (U+XXXX) | 3 Bytes |
| Shift_JIS-2004 (JIS X 0213) | U+2A6DF (Plane 2) | 5 Digits (U+XXXXX) | 4 Bytes |
| Any Japanese Encoding | Never exceeds U+2FFFF | Never 6 Digits (U+XXXXXX) | 4 Bytes max |


[1] [https://www.ibm.com](https://www.ibm.com/docs/en/cognos-analytics/12.1.x?topic=security-round-trip-safety-configuration-shift-jis-characters)
[2] [https://dev.mysql.com](https://dev.mysql.com/doc/mysql-g11n-excerpt/8.0/en/charset-cp932.html)
[3] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Plane_%28Unicode%29)
[4] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Code_page_932_%28Microsoft_Windows%29)
[5] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Shift_JIS)
[6] [https://mbrenndoerfer.com](https://mbrenndoerfer.com/writing/character-encoding-ascii-unicode-utf8-nlp)
[7] [https://medium.com](https://medium.com/@dmosyan/utf-8-and-the-problem-that-it-solves-a294aed2aa20)
[8] [https://hwb.ecc.u-tokyo.ac.jp](https://hwb.ecc.u-tokyo.ac.jp/hwb2023/information/text/character/unicode/)
[9] [https://atmarkit.itmedia.co.jp](https://atmarkit.itmedia.co.jp/ait/articles/1603/10/news049.html)
[10] [https://dmitripavlutin.com](https://dmitripavlutin.com/what-every-javascript-developer-should-know-about-unicode/)
[11] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Tilde)
[12] [https://www.reddit.com](https://www.reddit.com/r/programming/comments/1mx7v5/utf8_the_most_beautiful_hack/)
[13] [https://medium.com](https://medium.com/@andygineer/understanding-utf-8-and-utf-16-how-they-help-avoid-encoding-bugs-264cbc55dca3)
[14] [https://medium.com](https://medium.com/@dmosyan/utf-8-and-the-problem-that-it-solves-a294aed2aa20)
[15] [https://www.b-list.org](https://www.b-list.org/weblog/2017/sep/05/how-python-does-unicode/)
[16] [https://www.owlfolio.org](https://www.owlfolio.org/development/corrected-utf-8/)
[17] [https://dmitripavlutin.com](https://dmitripavlutin.com/what-every-javascript-developer-should-know-about-unicode/)
[18] [https://community.openai.com](https://community.openai.com/t/dealing-with-occasional-nonsensical-output-during-translation/862752)
[19] [https://www.ibm.com](https://www.ibm.com/docs/en/cognos-analytics/11.2.x?topic=administration-japanese-shift-jis-character-mapping)

# ---

Yes, in 99% of modern tech contexts, CP932, MS932, and Windows-31J refer to the exact same Japanese character encoding. [1, 2]

They represent Microsoft’s modernized, unified extension of Shift_JIS that includes NEC and IBM special characters (like circled numbers ① or Roman numerals Ⅰ). [3, 4]

However, they are named differently depending on the specific software ecosystem or standard registry you are using. There is also one rare historical edge-case to keep in mind. [1]

------------------------------
## 1. Where You Encounter Each Name

* Windows-31J: This is the official canonical name registered with IANA (Internet Assigned Numbers Authority). You use this specific string in web contexts—such as HTML headers or HTTP content-type parameters. The "31" historical nod comes from Windows 3.1 Japanese edition, which is when Microsoft finalized this unified mapping. [1, 5, 6, 7]
* MS932: This string is heavily used in the Java / JVM ecosystem. Java explicitly created the MS932 label to map perfectly to Windows-31J. [8, 9, 10]
* CP932: This stands for "Code Page 932". It is the general term used across Windows APIs, Python (encoding='cp932'), .NET, and database environments like MySQL. [3, 7, 8, 11, 12]

------------------------------
## 2. The One Technical Warning (The Java Context)

If you are developing inside Java, CP932 and MS932 are intentionally treated as different encodings: [9, 10]

* In Java, MS932 (or Windows-31J) is the modern Microsoft version you actually want.
* In Java, CP932 refers strictly to an old, legacy IBM variant of Code Page 932. [9, 10]

Because of this historical divide, if you pass the string "CP932" to an older Java compiler or runtime, it might apply IBM's character mappings instead of Microsoft's. This causes subtle character corruption (mojibake) on specialized extended characters. [1, 5, 10, 13]

## Summary Action Plan

* When writing HTML/CSS/Web Protocols: Use Windows-31J.
* When coding in Java: Use MS932 or Windows-31J.
* When coding in Python, C++, or Databases: Use cp932. [1, 3, 6, 10, 14]

------------------------------
If you are writing mapping code, do you need to handle duplicate/round-trip mapping issues (like the wave dash 〜 vs tilde ～ problem), or are you just setting up character-set aliases?

[1] [https://monologu.com](https://monologu.com/sjis-cp932-windows31j/)
[2] [https://hackmd.io](https://hackmd.io/@anqou/rkOWQ3mn9)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/61997186/decoding-japanese-circled-numbers-special-characters)
[4] [https://weblabo.oscasierra.net](https://weblabo.oscasierra.net/shift_jis-windows31j/)
[5] [https://marunouchi-tech.i-studio.co.jp](https://marunouchi-tech.i-studio.co.jp/1930/)
[6] [https://monologu.com](https://monologu.com/sjis-cp932-windows31j/)
[7] [https://www.swisteria.com](https://www.swisteria.com/posts/shift-jis_ms932/)
[8] [https://e-words.jp](https://e-words.jp/w/CP932.html)
[9] [https://qiita.com](https://qiita.com/yoshi389111/items/9060c8b62df7cac31de9)
[10] [https://ja.wikipedia.org](https://ja.wikipedia.org/wiki/Microsoft%E3%82%B3%E3%83%BC%E3%83%89%E3%83%9A%E3%83%BC%E3%82%B8932)
[11] [https://bugs.mysql.com](https://bugs.mysql.com/7607)
[12] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Code_page_932_%28Microsoft_Windows%29)
[13] [https://unicode.org](http://unicode.org/mail-arch/unicode-ml/y2003-m05/0787.html)
[14] [https://github.com](https://github.com/espressif/esp-idf/issues/4977)

# ---

No, there are no unique Japanese characters found at U+07FF and below. Every single Japanese-specific character resides much higher up in the Unicode architecture.

## Why U+07FF Contains No Japanese Characters

The Unicode range from U+0000 to U+07FF is strictly allocated to the following:

* Basic Latin (ASCII) (U+0000 to U+007F)
* European and Middle Eastern scripts (U+0080 to U+07FF), which includes Latin extensions, Greek, Cyrillic, Hebrew, Arabic, and Syriac. [1, 2, 3, 4]

Because U+07FF is the exact boundary where 2-byte UTF-8 encoding ends, it means no native Japanese character can ever be encoded in just 2 bytes of UTF-8. [5, 6]

------------------------------
## Where Japanese Characters Actually Live

To find actual Japanese characters (Kana, Kanji, or full-width symbols), you must look deep inside the 3-byte UTF-8 range (U+0800 to U+FFFF). [7, 8]

| Character Type | Unicode Range | UTF-8 Byte Length |
|---|---|---|
| Half-width Katakana | U+FF65 to U+FF9F | 3 Bytes |
| Hiragana | U+3040 to U+309F | 3 Bytes |
| Katakana | U+30A0 to U+30FF | 3 Bytes |
| Common Kanji (CJK) | U+4E00 to U+9FFF | 3 Bytes |
| Japanese Punctuation | U+3000 to U+303F | 3 Bytes |

------------------------------
## The Only Exception: Full-Width ASCII Alternatives

While not "unique Japanese characters" in terms of meaning, Japanese encodings like CP932 frequently use Full-width (Zen-kaku) alphanumeric characters (e.g., Ａ, Ｂ, １, ２) so that English text aligns visually with Kanji grids.

Even these cloned characters do not live below U+07FF. They are pushed all the way up to the Halfwidth and Fullwidth Forms block starting at U+FF00 (which also takes 3 bytes in UTF-8). [9]

[1] [https://betterexplained.com](https://betterexplained.com/articles/unicode/)
[2] [https://di-mgt.com.au](https://di-mgt.com.au/cryptoInternational2.html)
[3] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Comparison_of_Unicode_encodings)
[4] [https://numa.hypotheses.org](https://numa.hypotheses.org/674)
[5] [https://www.reddit.com](https://www.reddit.com/r/programming/comments/1mx7v5/utf8_the_most_beautiful_hack/)
[6] [https://www.reddit.com](https://www.reddit.com/r/computerscience/comments/1czyfsh/why_does_utf32_exist/)
[7] [https://realpython.com](https://realpython.com/python-encodings-guide/)
[8] [https://www.gyanmirai.com](https://www.gyanmirai.com/tools/fullwidth-converter)
[9] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms)

# ---

Yes, [Japan](https://www.google.com/search?kgmid=/m/03_3d) absolutely modified standard ASCII, but their modifications are actually encoded well below U+0800. They reside in the 1-byte and 2-byte UTF-8 ranges. [1, 2, 3, 4]

The technical breakdown of this historical crossover explains exactly how it maps to Unicode today.

------------------------------
## 1. The 1-Byte "Yen vs. Backslash" Swap

In the 1960s, Japan established its own national 7-bit variant of ASCII called JIS X 0201. Because early keyboards and screens lacked memory for extra currency signs, they made two distinct permanent modifications to the standard ASCII positions: [1, 2, 5, 6]

* 0x5C (Standard ASCII Backslash \) → Reassigned to the Yen Sign (¥)
* 0x7E (Standard ASCII Tilde ~) → Reassigned to the Overline (‾) [5, 7, 8]

## How this maps to Unicode today:

Unicode resolved this overlap using a standard known as strict logical mapping. They assigned the native, standalone symbols their own official homes in the Latin-1 Supplement block (U+0080 to U+00FF): [9, 10]

* Yen Sign (¥): Mapped to U+00A5. In UTF-8, this is 2 bytes (0xC2 0xA5).
* Overline (‾): Mapped to U+203E (this one is up in the 3-byte range). [3, 9]

## The Catch: How CP932 and Windows Handle It

To keep thousands of legacy systems from breaking, Microsoft's CP932 chose a completely different route from standard Japanese Shift_JIS: [8]

* CP932 explicitly maps the byte value 0x5C straight to the standard ASCII backslash U+005C.
* When a Windows PC is set to a Japanese locale, the OS visually overrides the U+005C glyph inside system fonts (like MS Gothic) to display as a Yen sign. [8, 11, 12, 13]

This is why, on a Japanese Windows command line, a file directory path looks exactly like C:¥Users¥Admin, but under the hood, the system is strictly processing 1-byte ASCII backslashes (U+005C). [1, 11, 14, 15, 16]

------------------------------
## 2. Half-Width Katakana (The 1-Byte Block)

JIS X 0201 also added an 8-bit extension containing thin Half-width Katakana (e.g., ｱ, ｲ, ｳ). These characters were assigned single-byte slots from 0xA1 to 0xDF. [5, 17]

* Unicode kept these compressed variations for legacy support.
* They were given positions in the Halfwidth and Fullwidth Forms block at U+FF61 through U+FF9F.
* Because these are well above U+0800, they require 3 bytes in UTF-8. [3, 8, 12, 18, 19]

------------------------------
## Summary Table: Where the Modified "ASCII" Characters Live

| Original Byte | Intended Character | Strict Unicode Mapping | UTF-8 Byte Length | CP932 Mapping |
|---|---|---|---|---|
| 0x5C | Yen Sign (¥) | U+00A5 (Below U+0800) | 2 Bytes | Maps to U+005C (Visual font swap) |
| 0x7E | Overline (‾) | U+203E (Above U+0800) | 3 Bytes | Maps to U+007E (Tilde) |
| 0xA1 - 0xDF | Half-Width Kana (ｱ) | U+FF61+ (Above U+0800) | 3 Bytes | Maps to U+FF61+ |


[1] [https://tex.stackexchange.com](https://tex.stackexchange.com/questions/126628/does-a-japanese-latex-user-really-type-commands-start-with-the-yen-symbol%EF%BF%A5-a)
[2] [https://superuser.com](https://superuser.com/questions/1167662/why-is-windows-10-displaying-as-%C2%A5-on-the-command-line)
[3] [https://developer.mamezou-tech.com](https://developer.mamezou-tech.com/en/blogs/2025/06/13/moji-code2/)
[4] [https://www.gyanmirai.com](https://www.gyanmirai.com/tools/fullwidth-converter)
[5] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Yen_and_yuan_sign)
[6] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=42077886)
[7] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Shift_JIS)
[8] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Code_page_932_%28Microsoft_Windows%29)
[9] [https://www.compart.com](https://www.compart.com/en/unicode/U+00A5)
[10] [https://unicode-explorer.com](https://unicode-explorer.com/c/00A5)
[11] [https://learn.microsoft.com](https://learn.microsoft.com/en-us/answers/questions/4763186/change-yen-symbol-to-backslash)
[12] [https://www.ghisler.ch](https://www.ghisler.ch/board/viewtopic.php?t=51822)
[13] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=29177000)
[14] [https://github.com](https://github.com/w3c/csswg-drafts/issues/6848)
[15] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Extended_Unix_Code)
[16] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/ASCII)
[17] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Shift_JIS)
[18] [https://www.shadecoder.com](https://www.shadecoder.com/topics/what-is-utf-8-vs-ascii-a-practical-guide-for-2025)
[19] [https://unifoundry.com](https://unifoundry.com/japanese/)


# SJIS  Windows encoding of JIS
# CP932 NEC expand of SJIS (add 8740-879f)
# CP932 IBM expand of SJIS (add fa40-faff,fb40-fbff,fc40-fc4f)
# CP932 NEC expand of IBM CP932 (add ed40-edff,ee40-eeff)
# MS932 Microsoft unified CP932 (aka Windows-31J)

echo    '\x41' = \x41
echo -e '\x41' = A
printf  '\x41' = A

// 0-9  824f-8258
// A-Z  8260-8279
// a-z  8281-829a
sjis 824f-8258 == full width 0-9
	== \uff10-\uff19

iconv -f sjis -t utf8 <<< $(printf '\x82\x50')
echo $? = 0
iconv -f sjis -t utf8 <<< $(printf '\x82\x5a')
echo $? = 1

sjis code
	81-9f  full width
		40-fc
	a1-df  half width
	e0-ef  full width
		40-fc
		ed40-eefc  nec/ibm extension
*/
