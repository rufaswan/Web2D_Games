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

// <script type="text/javascript">
//   window.jQuery || document.write('<script src="http://127.0.0.1/resource/0000017e-7fd1-d4e5-adfe-7ff9f1280001/styleguide/assets/external-libraries/jquery-1.11.0.min.09e2302066dc95597682cbcb72b3e5b3.gz.js"><\/script>')
// </script>

// <a href=https://developer.mozilla.org/en-US/docs/Web/API/Document/forms
//    title="The forms read-only property of the Document interface returns an HTMLCollection listing all the <form> elements contained in the document."
//    >Document/forms</a>

// <pre><code class='html'>
//   <c- p>&lt;</c-><c- f>p</c-><c- p>&gt;</c->
//   <c- p>&lt;</c-><c- f>svg</c-><c- p>&gt;</c->
// </code></pre>

class html
{
	private static function is_htmltag( &$file, $pos , $tag )
	{
		$taglen = strlen($tag);

		$sub = substr($file, $pos, $taglen);
		if ( strlen($sub) !== $taglen )
			return false;

		$sub = strtolower($sub);
		return ( $sub === $tag );
	}

	private static function endpos( &$file, $pos )
	{
		while ( isset($file[$pos]) )
		{
			$c = $file[$pos];
			if ( $c === '>' )
				return $pos + 1;
			switch ( $c )
			{
				case '"':
				case "'":
					$edpos = strpos($file, $c, $pos + 1);
					if ( $edpos === false )
						return -1;
					$pos = $edpos + 1;
					//while ( $file[$pos] === $c )
						//$pos++;
					break;
				case '\\':
					$pos += 2;
					break;
				default:
					$pos++;
					break;
			} // switch ( $c )
		} // while ( isset($file[$pos]) )

		return -1;
	}

	private static function subcomment( &$file, $stpos, $sttag, $edtag )
	{
		if ( ! self::is_htmltag($file,$stpos,$sttag) )
			return 0;
		$edpos = strpos($file, $edtag, $stpos);
		if ( $edpos === false )
			return 0;
		if ( $stpos > $edpos )
			return 0;
		$edpos += strlen($edtag);
		return substr($file, $stpos, $edpos - $stpos);
	}

	private static function subtext( &$file, $stpos, $tag )
	{
		if ( ! self::is_htmltag($file,$stpos,'<'.$tag) )
			return 0;
		$edpos = strpos($file, '</'.$tag, $stpos);
		if ( $edpos === false )
			return 0;
		if ( $stpos > $edpos )
			return 0;
		$end = strpos($file, '>', $edpos + 2);
		$edpos = $end + 1;
		return substr($file, $stpos, $edpos - $stpos);
	}
	//////////////////////////////
	public static function text2dom( &$file )
	{
		$dom = array();
		$tag = array(
			array('<![CDATA[', ']]>') ,
			array('<!--'     , '-->') ,
			'script'   , 'style'    , // Raw text elements
			'textarea' , 'title'    , // Escapable raw text elements
			'form'     ,              // Content model: Flow content, but with no form element descendants.
			'iframe'   , 'template' , // Content model: Nothing
			'svg'      , 'math'     , // SVG and MathML
			'pre'      ,
			'button'   ,
		);

		$len = strlen($file);
		$stpos = 0;
		while ( $stpos < $len )
		{
			//printf("stpos = %x\n", $stpos);
			// >...<
			if ( $file[$stpos] !== '<' )
			{
				$edpos = strpos($file, '<', $stpos);
				if ( $edpos === false )
					$edpos = $len;
				$dom[] = substr($file, $stpos, $edpos - $stpos);
				$stpos = $edpos;
				continue;
			}

			// <...> , text/raw text
			foreach ( $tag as $tv )
			{
				if ( is_array($tv) )
					$t = self::subcomment($file, $stpos, $tv[0], $tv[1]);
				else
					$t = self::subtext   ($file, $stpos, $tv);
				if ( ! $t )
					continue;
				goto done_tag;
			} // foreach ( $tag as $tv )

			// <...> , general
			$edpos = self::endpos($file, $stpos);
			if ( $edpos < 0 )
				return 0;
			$t = substr($file, $stpos, $edpos - $stpos);
			if ( preg_match('|>[\x09\x0a\x0c\x0d\x20]+<|',$t) )
				trigger_error('>< = '.$t, E_USER_NOTICE);

done_tag:
			$dom[] = $t;
			$stpos += strlen($t);
		} // while ( $stpos < $len )
		return $dom;
	}
} // class html

/*
https://html.spec.whatwg.org/multipage/infrastructure.html#xml
2.1.3 XML compatibility

	To ease migration from HTML to XML, user agents conforming to this specification
	will place elements in HTML in the http://www.w3.org/1999/xhtml namespace,
	at least for the purposes of the DOM and CSS.
	The term "HTML elements" refers to any element in that namespace, even in XML documents.

	Except where otherwise stated, all elements defined or mentioned in this specification
	are in the HTML namespace ("http://www.w3.org/1999/xhtml"),
	and all attributes defined or mentioned in this specification have no namespace.

https://html.spec.whatwg.org/multipage/dom.html#embedding-custom-non-visible-data-with-the-data-*-attributes
3.2.6.6 Embedding custom non-visible data with the data-* attributes

	A custom data attribute is an attribute in no namespace whose name starts with the string "data-",
	has at least one character after the hyphen, is XML-compatible, and contains no ASCII upper alphas.

Tag omission in text/html:
	Neither tag is omissible.

	https://html.spec.whatwg.org/multipage/sections.html#the-header-element
	4.3.8 The header element
		Content model:
			Flow content, but with no header or footer element descendants.

	https://html.spec.whatwg.org/multipage/sections.html#the-footer-element
	4.3.9 The footer element
		Content model:
			Flow content, but with no header or footer element descendants.

	https://html.spec.whatwg.org/multipage/sections.html#the-address-element
	4.3.10 The address element
		Content model:
			Flow content, but with no heading content descendants, no sectioning content descendants, and no header, footer, or address element descendants.

	https://html.spec.whatwg.org/multipage/text-level-semantics.html#the-a-element
	4.5.1 The a element
		Content model:
			Transparent, but there must be no interactive content descendant, a element descendant, or descendant with the tabindex attribute specified.

	https://html.spec.whatwg.org/multipage/text-level-semantics.html#the-dfn-element
	4.5.8 The dfn element
		Content model:
			Phrasing content, but there must be no dfn element descendants.

	https://html.spec.whatwg.org/multipage/iframe-embed-object.html#the-iframe-element
	4.8.5 The iframe element
		Content model:
			Nothing.

	https://html.spec.whatwg.org/multipage/media.html#the-video-element
	4.8.8 The video element
		Content model:
			If the element has a src attribute: zero or more track elements, then transparent, but with no media element descendants.
			If the element does not have a src attribute: zero or more source elements, then zero or more track elements, then transparent, but with no media element descendants.

	https://html.spec.whatwg.org/multipage/media.html#the-audio-element
	4.8.9 The audio element
		Content model:
			If the element has a src attribute: zero or more track elements, then transparent, but with no media element descendants.
			If the element does not have a src attribute: zero or more source elements, then zero or more track elements, then transparent, but with no media element descendants.

	https://html.spec.whatwg.org/multipage/forms.html#the-form-element
	4.10.3 The form element
		Content model:
			Flow content, but with no form element descendants.

	https://html.spec.whatwg.org/multipage/forms.html#the-label-element
	4.10.4 The label element
		Content model:
			Phrasing content, but with no descendant labelable elements unless it is the element's labeled control, and no descendant label elements.

	https://html.spec.whatwg.org/multipage/form-elements.html#the-button-element
	4.10.6 The button element
		Content model:
			Phrasing content, but there must be no interactive content descendant and no descendant with the tabindex attribute specified.

	https://html.spec.whatwg.org/multipage/form-elements.html#the-progress-element
	4.10.13 The progress element
		Content model:
			Phrasing content, but there must be no progress element descendants.

	https://html.spec.whatwg.org/multipage/form-elements.html#the-meter-element
	4.10.14 The meter element
		Content model:
			Phrasing content, but there must be no meter element descendants.

	https://html.spec.whatwg.org/multipage/scripting.html#the-noscript-element
	4.12.2 The noscript element
		Content model:
			When scripting is disabled, in a head element: in any order, zero or more link elements, zero or more style elements, and zero or more meta elements.
			When scripting is disabled, not in a head element: transparent, but there must be no noscript element descendants.
			Otherwise: text that conforms to the requirements given in the prose.

	https://html.spec.whatwg.org/multipage/scripting.html#the-template-element
	4.12.3 The template element
		Content model:
			Nothing (for clarification, see example).

	https://html.spec.whatwg.org/multipage/canvas.html#the-canvas-element
	4.12.5 The canvas element
		Content model:
			Transparent, but with no interactive content descendants except for a elements, img elements with usemap attributes, button elements, input elements whose type attribute are in the Checkbox or Radio Button states, input elements that are buttons, and select elements with a multiple attribute or a display size greater than 1.

https://html.spec.whatwg.org/multipage/syntax.html#elements-2
13.1.2 Elements

	Void elements
		area, base, br, col, embed, hr, img, input, link, meta, source, track, wbr
	Raw text elements
		script, style
	Escapable raw text elements
		textarea, title

	Void elements can't have any contents (since there's no end tag,
	no content can be put between the start tag and the end tag).

	Raw text elements can have text, though it has restrictions described below.

	Escapable raw text elements can have text and character references,
	but the text must not contain an ambiguous ampersand.
	There are also further restrictions described below.

https://html.spec.whatwg.org/multipage/syntax.html#syntax-attributes
13.1.2.3 Attributes

	Attributes for an element are expressed inside the element's start tag.

	Attributes have a name and a value.
	Attribute names must consist of one or more characters other than
	controls, U+0020 SPACE, U+0022 ("), U+0027 ('), U+003E (>), U+002F (/), U+003D (=), and noncharacters.
	In the HTML syntax, attribute names, even those for foreign elements,
	may be written with any mix of ASCII lower and ASCII upper alphas.

	Attribute values are a mixture of text and character references,
	except with the additional restriction that the text cannot contain an ambiguous ampersand.

	Single-quoted attribute value syntax

		The attribute name, followed by zero or more ASCII whitespace,
		followed by a single U+003D EQUALS SIGN character,
		followed by zero or more ASCII whitespace,
		followed by a single U+0027 APOSTROPHE character ('),
		followed by the attribute value, which,
			in addition to the requirements given above for attribute values,
			must not contain any literal U+0027 APOSTROPHE characters ('),
		and finally followed by a second single U+0027 APOSTROPHE character (').

	Double-quoted attribute value syntax

		The attribute name, followed by zero or more ASCII whitespace,
		followed by a single U+003D EQUALS SIGN character,
		followed by zero or more ASCII whitespace,
		followed by a single U+0022 QUOTATION MARK character ("),
		followed by the attribute value, which,
			in addition to the requirements given above for attribute values,
			must not contain any literal U+0022 QUOTATION MARK characters ("),
		and finally followed by a second single U+0022 QUOTATION MARK character (").

https://html.spec.whatwg.org/multipage/syntax.html#optional-tags
13.1.2.4 Optional tags

	Certain tags can be omitted.

		An html element's start tag may be omitted if the first thing inside the html element is not a comment.
		A head element's start tag may be omitted if the element is empty, or if the first thing inside the head element is an element.
		A body element's start tag may be omitted if the element is empty, or if the first thing inside the body element is not ASCII whitespace or a comment, except if the first thing inside the body element is a meta, noscript, link, script, style, or template element.
		A colgroup element's start tag may be omitted if the first thing inside the colgroup element is a col element,
			and if the element is not immediately preceded by another colgroup element whose end tag has been omitted. (It can't be omitted if the element is empty.)
		A tbody element's start tag may be omitted if the first thing inside the tbody element is a tr element,
			and if the element is not immediately preceded by a tbody, thead, or tfoot element whose end tag has been omitted. (It can't be omitted if the element is empty.)

		An html element's end tag may be omitted if the html element is not immediately followed by a comment.
		A body element's end tag may be omitted if the body element is not immediately followed by a comment.

		A head element's end tag may be omitted if the head element is not immediately followed by ASCII whitespace or a comment.
		A colgroup element's end tag may be omitted if the colgroup element is not immediately followed by ASCII whitespace or a comment.
		A caption element's end tag may be omitted if the caption element is not immediately followed by ASCII whitespace or a comment.

		A dd element's end tag may be omitted if the dd element is immediately followed by another dd element
			or a dt element, or if there is no more content in the parent element.
		A dt element's end tag may be omitted if the dt element is immediately followed by another dt element or a dd element.
		An li element's end tag may be omitted if the li element is immediately followed by another li element
			or if there is no more content in the parent element.
		An rp element's end tag may be omitted if the rp element is immediately followed by an rt or rp element,
			or if there is no more content in the parent element.
		An rt element's end tag may be omitted if the rt element is immediately followed by an rt or rp element,
			or if there is no more content in the parent element.
		An optgroup element's end tag may be omitted if the optgroup element is immediately followed by another optgroup element,
			if it is immediately followed by an hr element,
			or if there is no more content in the parent element.
		An option element's end tag may be omitted if the option element is immediately followed by another option element,
			if it is immediately followed by an optgroup element,
			if it is immediately followed by an hr element,
			or if there is no more content in the parent element.
		A thead element's end tag may be omitted if the thead element is immediately followed by a tbody or tfoot element.
		A tbody element's end tag may be omitted if the tbody element is immediately followed by a tbody or tfoot element,
			or if there is no more content in the parent element.
		A tr element's end tag may be omitted if the tr element is immediately followed by another tr element,
			or if there is no more content in the parent element.
		A th element's end tag may be omitted if the th element is immediately followed by a td or th element,
			or if there is no more content in the parent element.
		A td element's end tag may be omitted if the td element is immediately followed by a td or th element,
			or if there is no more content in the parent element.
		A tfoot element's end tag may be omitted if there is no more content in the parent element.

		A p element's end tag may be omitted if the p element is immediately followed by
			an address, article, aside, blockquote, details, dialog, div, dl, fieldset, figcaption, figure, footer, form, h1, h2, h3, h4, h5, h6, header, hgroup, hr, main, menu, nav, ol, p, pre, search, section, table, or ul element,
			or if there is no more content in the parent element and the parent element is an HTML element that is not
			an a, audio, del, ins, map, noscript, or video element,
			or an autonomous custom element.

	However, a start tag must never be omitted if it has any attributes.

https://html.spec.whatwg.org/multipage/syntax.html#cdata-rcdata-restrictions
13.1.2.6 Restrictions on the contents of raw text and escapable raw text elements

	The text in raw text and escapable raw text elements
	must not contain any occurrences of the string "</" (U+003C LESS-THAN SIGN, U+002F SOLIDUS)
	followed by characters that case-insensitively match the tag name of the element
	followed by one of
		U+0009 CHARACTER TABULATION (tab),
		U+000A LINE FEED (LF),
		U+000C FORM FEED (FF),
		U+000D CARRIAGE RETURN (CR),
		U+0020 SPACE,
		U+003E GREATER-THAN SIGN (>),
		or U+002F SOLIDUS (/).

https://html.spec.whatwg.org/multipage/syntax.html#cdata-sections
13.1.5 CDATA sections

	CDATA sections must consist of the following components, in this order:

		The string "<![CDATA[".
		Optionally, text, with the additional restriction that the text must not contain the string "]]>".
		The string "]]>".

https://html.spec.whatwg.org/multipage/syntax.html#comments
13.1.6 Comments

	Comments must have the following format:

		The string "<!--".
		Optionally, text, with the additional restriction that the text must not start with the string ">",
			nor start with the string "->",
			nor contain the strings "<!--", "-->", or "--!>",
			nor end with the string "<!-".
		The string "-->".
*/
