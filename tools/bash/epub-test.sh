#!/bin/bash

# run from terminal/desktop check
[ -t 0 ] || exit

# make clean a working epub
dir='epub_test'
[ -d $dir ] && rm -vfr $dir
[ -e $dir ] && exit
[ -f $dir.epub ] && rm -vf $dir.epub

container='<?xml version="1.0"?>
<container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
  <rootfiles>
    <rootfile full-path="root.opf" media-type="application/oebps-package+xml"/>
  </rootfiles>
</container>'

root='<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" xmlns:opf="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookID">
  <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
    <dc:title>Dummy EPUB</dc:title>
    <dc:identifier id="BookID">0000000000000000</dc:identifier>
    <dc:language>en-US</dc:language>
    <meta property="dcterms:modified">2000-01-01T00:00:00Z</meta>
  </metadata>
  <manifest>
    <item href="OEBPS/book.html" id="book" media-type="application/xhtml+xml"/>
    <item href="nav.html"        id="nav"  media-type="application/xhtml+xml" properties="nav"/>
  </manifest>
  <spine>
    <itemref idref="book"/>
  </spine>
</package>'

book='<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Dummy EPUB</title>
  <meta charset="utf-8"/>
</head>
<body>
  <p>Dummy EPUB</p>
</body>
</html>'

nav='<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
  <title>Dummy EPUB</title>
  <meta charset="utf-8"/>
</head>
<body>
  <nav epub:type="toc">
    <ol>
      <li><a href="OEBPS/book.html">Book</a></li>
    </ol>
  </nav>
</body>
</html>'

mkdir -p $dir/META-INF/
mkdir -p $dir/OEBPS/
echo -n 'application/epub+zip' > $dir/mimetype
echo "$container" > $dir/META-INF/container.xml
echo "$book"      > $dir/OEBPS/book.html
echo "$nav"       > $dir/nav.html
echo "$root"      > $dir/root.opf

# -0  store only
# -D  do not add dir
# -X  do not add file ext attr
# -r  recurse into dir
cd  $dir
zip  -0 -D -X -r \
	../$dir.epub \
	mimetype     \
	META-INF/*   \
	OEBPS/*      \
	nav.html     \
	root.opf
cd  ..
