#!/bin/bash
[ $(which asciidoctor) ] || exit

[ $# = 0 ] && exit
css='--attribute  stylesheet!'
back='xhtml'
doc='book'
while [ "$1" ]; do
	t1="${1%/}"
	shift

	if [ -f "$t1" ]; then
		ext="${t1##*.}"
		case "$ext" in
			'adoc'|'asciidoc')
				nice -n 19  asciidoctor   \
					--backend    $back    \
					--doctype    $doc     \
					--attribute  toc      \
					--attribute  nofooter \
					$css                  \
					./"$t1"
				;;
			'css')  css="--attribute  stylesheet='$t1'";;
		esac
	else
		case "$t1" in
			'css'  )  css='';;

			'html' )  back='html';;
			'xhtml')  back='xhtml';;
			#'xml'  )  back='docbook';;
			#'man'  )  back='manpage';;

			'article')  doc='article';;
			'book'   )  doc='book';;
			#'manpage')  doc='manpage';;
		esac
	fi
done

<<'////'
					--attribute  last-update-label\! \
					--attribute  noheader            \

https://en.wikipedia.org/wiki/EPUB
	Technically, a file in the EPUB format is a ZIP archive file
	consisting of XHTML files carrying the content,
	along with images and other supporting files.
////
