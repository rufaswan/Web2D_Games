To fix oversized images in an EPUB created from Markdown, you need to constrain the image dimensions using CSS or extended Markdown syntax. Because EPUB is essentially a collection of HTML and CSS files, the most reliable way to ensure images fit the page width is by setting a maximum width in your styling. [1]

## 1. Global Fix: Use CSS [2, 3]

The most efficient method is to apply a global rule to all images. If you are using a tool like [Pandoc](https://pandoc.org/MANUAL.html), you can include a custom CSS file during conversion. [2]

* Add this to your CSS file:

```
img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 1em auto; /* Centers the image */
}
```

This ensures no image is wider than the screen while maintaining its original aspect ratio. [2, 4, 5, 6, 7]

## 2. Individual Fix: Extended Syntax

If only specific images are too large, use extended Markdown attributes (supported by processors like Pandoc) to set the width directly in the text. [8, 9]

* Pandoc Syntax:

```
![Alt text](image.jpg){width=100%}

```

* Alternative (kramdown/Jekyll):

```
![Alt text](image.jpg){: width="100%"}
```

## 3. Using HTML Tags

If your Markdown processor supports raw HTML, you can replace the standard Markdown image link with an <img> tag for more precise control. [10, 11]

```
<img src="your-image.jpg" alt="Description" style="width: 100%; max-width: 600px;" />
```

## 4. Advanced: SVG Wrapping

For full-page images (like covers) that must scale perfectly to any screen size, wrapping the image in an SVG container is the most robust "industry standard" for EPUBs. [4, 12]

```
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100%" viewBox="0 0 [width] [height]" preserveAspectRatio="xMidYMid meet">
  <image width="[width]" height="[height]" xlink:href="image.jpg"/>
</svg>
```

Pro Tip: For the best balance of quality and file size, aim for images around 1,536 px on their longest side at 150 ppi. This fits iPad Retina displays perfectly while remaining small enough for older e-readers. [13, 14]

Are you using a specific conversion tool like Pandoc, Calibre, or a specialized Markdown editor to generate your EPUB? [15, 16, 17]

[1] [https://medium.com](https://medium.com/@sharkbombs/a-recipe-for-accessibility-turning-your-ttrpg-into-an-epub-ba81713cb958#:~:text=Essentially%20what%20happens%20during%20the%20conversion%20process,images%2C%20fonts%E2%80%A6%29%20together%20into%20a%20single%20file.)
[2] [https://cmichel.io](https://cmichel.io/how-to-create-beautiful-epub-programming-ebooks/)
[3] [https://community.adobe.com](https://community.adobe.com/questions-671/have-images-fill-epub-column-882027)
[4] [https://ebooks.stackexchange.com](https://ebooks.stackexchange.com/questions/7422/how-to-create-an-epub-full-page-image-which-properly-reflows)
[5] [https://discourse.devontechnologies.com](https://discourse.devontechnologies.com/t/css-for-markdown-centering-blocks-and-styling-images/71368)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/31931767/resizing-images-in-an-epub-v3)
[7] [https://discourse.devontechnologies.com](https://discourse.devontechnologies.com/t/markdown-a-simple-method-for-scaling-images/70035)
[8] [https://superuser.com](https://superuser.com/questions/796565/correctly-sizing-png-images-in-markdown-with-pandoc-for-html-pdf-docx)
[9] [https://pandoc.org](https://pandoc.org/demo/example33/8.17-images.html)
[10] [https://www.markdownguide.org](https://www.markdownguide.org/hacks/)
[11] [https://www.geeksforgeeks.org](https://www.geeksforgeeks.org/git/how-to-resize-image-in-github-using-markdown/)
[12] [https://stackoverflow.com](https://stackoverflow.com/questions/19711904/css-image-sizing)
[13] [https://medium.com](https://medium.com/@iampariah/what-is-the-ideal-size-for-images-in-epub-ebooks-5b85b3aedcd3)
[14] [https://medium.com](https://medium.com/@iampariah/what-is-the-ideal-size-for-images-in-epub-ebooks-5b85b3aedcd3)
[15] [https://mason.gmu.edu](https://mason.gmu.edu/~dtaciuch/2020/S20/377/epub.html)
[16] [https://barkerbooks.com](https://barkerbooks.com/how-to-create-epub-file/#:~:text=For%20those%20who%20are%20comfortable%20working%20in,Markdown%20%28%20.%20md%20%29%20text%20file.)
[17] [https://www.kdpcommunity.com](https://www.kdpcommunity.com/s/question/0D5f400000FHbiOCAT/free-markdown-to-epub-conversion-app-full-spec?language=de#:~:text=*@Markdown%20gives%20you%2020%20paragraph%20styles%205,convert%20your%20text%20file%20to%20an%20EPUB.)

# ---

For a detailed fantasy world map, the goal is to provide a high-resolution version that the reader can zoom into without it appearing blurry or being "shrunk" to fit the screen. [1]

## 1. The "Click-to-Zoom" Trick (Markdown)

Most modern e-readers (Kindle, [Kobo](https://www.kobo.com/), Apple Books) allow users to double-tap or "pinch-to-zoom" on an image to view it full-screen. However, if your Markdown converter forces the image to width: 100%, it may limit the zoom quality.

A clever way to ensure a "deep zoom" is to wrap the small display image in a link to itself. This forces many e-readers to treat the image as an external, zoomable asset.

* Markdown Syntax:

```
[![](map.jpg)](map.jpg)
```

* How it works: This displays the image normally in the text, but clicking it opens the "linked" version (the same file) in the device's native image viewer, which usually has much better zoom and pan controls. [2, 3]

## 2. Increase Source Resolution

If the map is still blurry when zoomed, your source file resolution is likely too low.

* Target Resolution: Use an image that is at least 3000 to 4000 pixels on its longest side.
* DPI: Ensure it is set to 300 DPI. While digital screens vary, 300 DPI is the standard for maintaining crispness when a user pinches to zoom in on tiny labels or icons. [4, 5, 6]

## 3. Divide the Map into "Plates" [7]

If a single world map is too complex to see clearly on a small e-ink screen, consider the "Atlas approach" used by many professional fantasy authors: [7, 8]

   1. Overview Map: Insert one image of the entire world (fitting the page width).
   2. Regional Maps: Follow it with 2–4 "zoomed-in" images of specific regions (e.g., North, South, East, West).
   3. Labeling: This allows the reader to see the "big picture" first, then flip the page to see the fine details of the specific area where the story takes place.

## 4. CSS for "Full Screen" (Advanced) [9]

If you are using a tool like [Calibre](https://calibre-ebook.com/download) or [Sigil](https://sigil-ebook.com/) to edit your EPUB after Markdown conversion, you can apply a specific CSS class to your map to ensure it uses the maximum available screen real estate.

* CSS:

```
.map-image {
    width: 100%;
    height: auto;
    max-height: 95vh; /* Prevents the image from being cut off by headers/footers */
    page-break-before: always;
    page-break-after: always;
}
```

* Markdown:

```
<div class="map-image">![World Map](map.jpg)</div>
```

Quick Tip: For Kindle users, you can press and hold on an image until a magnifying glass icon appears; clicking that icon will launch the dedicated zoom view. [10, 11, 12]

Would you like to know how to split your map into regional sections using a specific tool like Photoshop or GIMP?

[1] [https://www.goodreads.com](https://www.goodreads.com/topic/show/18521590-fantasy-map-on-kindle-is-too-small)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/14675913/changing-image-size-in-markdown/74868102)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/31558605/zoomable-image-in-markdown)
[4] [https://www.reddit.com](https://www.reddit.com/r/selfpublish/comments/36ygsf/im_writing_a_detailed_guide_on_formatting/)
[5] [https://designingmaps.com](https://designingmaps.com/2018/06/fantasy-map-design-basics/)
[6] [https://www.facebook.com](https://www.facebook.com/groups/officialconceptart/posts/1601951390003531/)
[7] [https://www.reddit.com](https://www.reddit.com/r/selfpublish/comments/1af6fpd/formatting_a_map_in_book/)
[8] [https://www.kdpcommunity.com](https://www.kdpcommunity.com/s/question/0D5f400000FHtx2CAD/maps-in-ebooks?language=de)
[9] [https://ebooks.stackexchange.com](https://ebooks.stackexchange.com/questions/7422/how-to-create-an-epub-full-page-image-which-properly-reflows)
[10] [https://www.reddit.com](https://www.reddit.com/r/kindle/comments/1ei6bgv/can_you_zoom_in_on_images_charts_graphs_for_epub/)
[11] [https://www.reddit.com](https://www.reddit.com/r/kindle/comments/1ei6bgv/can_you_zoom_in_on_images_charts_graphs_for_epub/)
[12] [https://www.reddit.com](https://www.reddit.com/r/kindle/comments/1ei6bgv/can_you_zoom_in_on_images_charts_graphs_for_epub/)

# ---

The error http://ebps/world-map.jpg occurs because the Markdown converter is interpreting your image path as an external web link rather than an internal file reference. Most e-readers will try to open anything starting with http in a web browser like Chrome, which fails because the image only exists inside your EPUB file.

To fix this, you must ensure the link points to the internal file path within the EPUB structure.

## 1. Use Relative Paths (The Correct Markdown Syntax)

Instead of a full URL, use a simple relative path. If your image is in the same folder as your Markdown file, do not include any slashes at the start.

* Wrong: [![](map.jpg)](/map.jpg) or [![](map.jpg)](http://map.jpg)
* Right: [![](map.jpg)](map.jpg)

## 2. Pandoc-Specific Fix (Implicit Header References)

If you are using Pandoc, it sometimes struggles with images linked to themselves. A more robust way to allow a "zoom" is to use a [linked attribute](https://pandoc.org/MANUAL.html) or a custom link that Pandoc recognizes as internal.

Try this specific syntax if the standard one fails:

```
![World Map](map.jpg){#fig:map}
```

Then, elsewhere in your text, you can provide a text link:

```
[Click here to view the map full-screen](#fig:map)
```

## 3. Post-Processing with Sigil or Calibre (Most Reliable)

Since you are creating a detailed fantasy map, the most professional way to handle this is to edit the converted EPUB using a free tool like [Sigil](https://sigil-ebook.com/) or the Edit Book feature in [Calibre](https://calibre-ebook.com/).

   1. Open your EPUB in the editor.
   2. Locate the HTML file containing your map.
   3. Ensure the link tag (<a>) uses a relative path to the image in the Images (or OEBPS/Images) folder:
   * Correct HTML: <a href="../Images/world-map.jpg"><img src="../Images/world-map.jpg" alt="Map" /></a>
   4. Crucial Step: In the EPUB's content.opf file, make sure the image is listed in the Manifest. If the image isn't in the manifest, the e-reader won't know it's a "local" file and will try to open it as a website.

## 4. Alternative: The "Cover Image" Technique

If the map is the most important visual, you can set it as a secondary cover or a "Non-linear" resource. In Sigil, right-click the image and select Add Semantics > Cover. This tells the e-reader software that this specific file is a high-priority visual asset, often enabling better native zoom tools automatically.

Which tool or command are you currently using to convert your Markdown to EPUB? (e.g., Pandoc, a VS Code extension, or an online converter?)

# ---

It sounds like you've hit the "EPUB wall"—every reader (Kindle, Apple Books, Kobo, Lithium) handles image zooming differently, and some are just plain bad at it.

Since the "link to self" method is failing or triggering the browser, the most reliable way to handle a large fantasy map across different devices is to change how the image is embedded in the HTML.

## 1. The "SVG Wrapper" Method (The Gold Standard)

Professional EPUB designers use an SVG wrapper. This tells the e-reader, "This image is a coordinate system; let the user scale it to any size." It usually forces the internal image viewer to allow zooming.

Replace your Markdown image with this HTML snippet in your source:

```
<div style="text-align: center; page-break-inside: avoid;">
  <svg xmlns="http://w3.org" xmlns:xlink="http://w3.org" version="1.1" width="100%" height="100%" viewBox="0 0 [WIDTH] [HEIGHT]" preserveAspectRatio="xMidYMid meet">
    <image width="[WIDTH]" height="[HEIGHT]" xlink:href="world-map.jpg"/>
  </svg>
</div>
```

Replace [WIDTH] and [HEIGHT] with the actual pixel dimensions of your image.

## 2. Force Landscape Orientation

If your map is wide, e-readers often shrink it to fit the portrait width. You can force the reader to display the map on its own page in landscape mode (though not all e-readers respect this, many modern ones do).

Add this to your CSS:

```
@page {
  size: landscape;
}
.map-page {
  page-break-before: always;
  width: 100%;
}
```

And wrap your image in that class in your Markdown:

```
<div class="map-page">![Map](world-map.jpg)</div>
```

## 3. The "Split Map" Strategy (Most Readable)

If your readers are on small E-ink screens (like a basic Kindle), no amount of zooming will make a world map readable. The best "User Experience" is:

   1. Insert the Full Map (for context).
   2. Insert 4 Crop-ins (Northwest, Northeast, Southwest, Southeast) as separate images on following pages.
   3. This ensures that even without zooming, the labels are large enough to read.

## 4. Check your "Mimetype"

If a reader is opening http://ebps/, it might think the image is a "resource" rather than a "file." If you are using a tool like Sigil or Calibre to finish the book, right-click the image in the file tree and ensure its Mime-type is correctly set to image/jpeg or image/png.

Which specific e-reader apps/devices are you testing on? (Knowing the app helps pinpoint if it's a software limitation or a code error.)

# ---

For apps like ReadEra and Lithium, the issue is that they use a standard webview to render your EPUB. When you use a link, they often misinterpret the internal path or lack a built-in "high-res zoom" mode for standard image tags. [1, 2]

The most universal method to ensure a map is both correctly sized on the page and fully zoomable without technical errors is a combination of high-resolution sourcing and specific CSS.

## 1. The "Native Viewer" Trigger (Most Reliable)

Instead of a Markdown link, use a standard HTML <img> tag with specific dimensions. Most Android readers (including ReadEra and Lithium) are programmed to open an internal image viewer if you long-press or double-tap an image. [2, 3, 4]

To make this work universally:

* Resolution: Ensure your image is at least 3000px wide. This provides the "data" needed for a sharp zoom.
* HTML in Markdown:

```
<p style="text-align:center;">
  <img src="world-map.jpg" alt="Fantasy Map" style="max-width:100%; height:auto;" />
</p>
```

* Why this works: By setting max-width: 100%, the map fits the screen width by default. Because the source file is actually 3000px, when the user triggers the reader's "Image Mode" (usually by long-pressing), they can pinch-to-zoom into the full-resolution details. [5, 6, 7, 8]

## 2. Fixing the "Broken Link" Error

The error http://ebps/world-map.jpg happens because the reader thinks a link <a> is an external website. To fix this for all readers: [9]

* Avoid the Link: Use the method in Step 1. Most readers have a built-in zoom tool that doesn't require an <a> tag.
* Check Folder Structure: If you must use a link, ensure your image is in an Images folder and the path is relative: <a href="../Images/world-map.jpg">. If the reader sees http://, it will always fail.

## 3. The "Full-Page" CSS Rule

To ensure the map gets as much space as possible (especially in Lithium), add a specific "page-break" rule to your CSS so the map isn't squeezed between paragraphs. [10]

Add this to your CSS file:

```
.map-container {
    page-break-before: always;
    page-break-after: always;
    text-align: center;
    width: 100%;
}

.map-container img {
    width: 100%;
    height: auto;
}
```

Then use this in your Markdown:

```
<div class="map-container">
  <img src="world-map.jpg" alt="World Map">
</div>
```

## Summary for Universal Results:

   1. Don't link the image to itself (it triggers the browser error you saw).
   2. Use a very high-resolution source file (3000px+).
   3. Use max-width: 100% to fit the screen.
   4. Instruct your readers (in a Preface or Map caption) to long-press or double-tap the map to enter "Zoom Mode." This uses the app's native viewer (like [ReadEra's](https://play.google.com/store/apps/details?id=org.readera) or [Lithium's](https://play.google.com/store/apps/details?id=com.faultexception.reader)) which handles the high-res file correctly. [1, 2]

Would you like a sample CSS file pre-configured for these high-resolution fantasy maps?

[1] [https://play.google.com](https://play.google.com/store/apps/details?id=org.readera)
[2] [https://github.com](https://github.com/foobnix/LibreraReader/issues/1295)
[3] [https://www.reddit.com](https://www.reddit.com/r/androidapps/comments/wfei7g/looking_for_an_epub_reader_that_can_zoom/)
[4] [https://www.mobileread.com](https://www.mobileread.com/forums/showthread.php?t=356073)
[5] [https://www.reddit.com](https://www.reddit.com/r/kindle/comments/1ei6bgv/can_you_zoom_in_on_images_charts_graphs_for_epub/)
[6] [https://evelynchartres.com](https://evelynchartres.com/basic-images-styles-in-epub/#:~:text=The%20workaround%20is%20to%20provide%20image%20optimised,can%20further%20zoom%20in%20for%20greater%20detail.)
[7] [https://github.com](https://github.com/foobnix/LibreraReader/issues/1295)
[8] [https://www.reddit.com](https://www.reddit.com/r/androidapps/comments/wfei7g/looking_for_an_epub_reader_that_can_zoom/)
[9] [https://www.reddit.com](https://www.reddit.com/r/KavitaManga/comments/vpshn5/epub_images_do_not_load/)
[10] [https://stackoverflow.com](https://stackoverflow.com/questions/19711904/css-image-sizing)

# ---

In the world of EPUB creation, Markdown is the "draft," but HTML/CSS is the "engine."

To get a high-quality, zoomable fantasy map that works across different apps, you usually have to include a small amount of HTML/CSS. Here is how that works:

## 1. Do you have to add HTML to your Markdown?

Strictly speaking, no, but standard Markdown is very limited. It only knows how to "place" an image; it doesn't know how to handle complex layouts like "full-screen zoom" or "landscape orientation."

If you want a universal solution for a map, you have two choices:

* The "Pure Markdown" way: Use high-resolution images and hope the reader app (like ReadEra) has a good built-in "long-press to zoom" feature.
* The "Hybrid" way: Use a few lines of HTML/CSS inside your Markdown to "force" the reader to display it correctly.

## 2. Can these HTML snippets be "converted" back to Markdown?

No. Markdown cannot "hold" the advanced instructions (like SVG wrappers or specific page breaks) that HTML/CSS provide.

However, most Markdown-to-EPUB converters (like Pandoc or Calibre) follow a "Passthrough" rule: If they see raw HTML in your Markdown file, they leave it exactly as it is.

This means you can write 99% of your book in clean Markdown and only use HTML for the "Map Page."

## The "Best of Both Worlds" Solution

If you want to keep your Markdown document clean but still fix the map, use CSS classes instead of raw HTML tags.

Step 1: Add this to your separate Stylesheet (CSS):

```
/* This tells the EPUB how to handle ANY image with the 'map' class */
.map-style {
  width: 100% !important;
  height: auto !important;
  display: block;
  page-break-before: always;
  page-break-after: always;
}
```

Step 2: Use "Extended Attributes" in your Markdown:

If you use Pandoc or a modern editor, you can attach the CSS class directly to the Markdown image link without using raw HTML:

```
![World Map](world-map.jpg){.map-style}
```

## Why this is better:

   1. Clean Markdown: You aren't cluttering your text with <div> and <svg> tags.
   2. Universal: The .map-style class ensures the map gets its own page and fits the width on every device.
   3. Zoomable: As long as world-map.jpg is a high-resolution file (3000px+), apps like ReadEra and Lithium will allow the user to double-tap or long-press it to zoom in on the details.

Which converter are you using to turn your Markdown into an EPUB? (Knowing this tells me if you can use the {.class} shortcut.)

# ---

Using AsciiDoc is a smart move for creating books like EPUBs because it was specifically designed for publishing, whereas Markdown was built for the web. It handles complex layouts like high-resolution fantasy maps much more natively without needing as much raw HTML. [1, 2, 3]

## Why AsciiDoc helps with your map problem:

* Native Sizing Attributes: You can set separate widths for different formats (like EPUB vs PDF) in one line.
* Interactive Zooming: AsciiDoc's EPUB3 converter (Asciidoctor EPUB3) is better at preserving image quality for "pinch-to-zoom" in apps like Lithium and ReadEra.
* No "Broken Links": Because it handles internal paths better, you won't get that http://ebps/ error. [4, 5, 6]

## How to insert your map in AsciiDoc:

Use the image:: block macro. To ensure it fits the width but stays high-resolution for zooming, use the scaledwidth attribute:

```
.World Map of the Hidden Realms
[#world-map]
image::world-map.jpg[World Map, scaledwidth=100%, align=center]
```

## Pro-Tips for AsciiDoc EPUBs:

* Image Directory: Define where your images are at the top of your document so you don't have to type long paths every time:

```
:imagesdir: ./images
```

* SVG Support: If your map is in SVG format, AsciiDoc can "inline" it, which makes it infinitely zoomable without any pixelation.
* Role-based Scaling: If you want the map to behave differently (e.g., full-screen), you can assign it a "role" that matches a CSS class in your theme:

```
image::world-map.jpg[role=full-page-map]
```

[4, 5, 6, 7, 8]

Ready to try it? If you have Asciidoctor installed, you can convert your file to a professional EPUB with one command: asciidoctor-epub3 book.adoc.
Would you like help setting up the Asciidoctor toolchain on your computer to start the conversion?

[1] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=27744509)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/asciidoc-vs-markdown/)
[3] [https://dev.to](https://dev.to/schadokar/best-tool-to-write-a-technical-ebook-59kd)
[4] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-size/)
[5] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-size/)
[6] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/epub3-converter/latest/)
[7] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)
[8] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)

# ---

In [AsciiDoc](https://asciidoctor.org/), you insert an SVG wrapper using a passthrough block. This allows you to embed raw HTML directly into your document, ensuring the EPUB converter doesn't touch the code. This method is the "gold standard" for making high-resolution maps zoomable across different e-reader apps like ReadEra and Lithium. [1, 2]

## 1. The SVG Wrapper Code

Place your map inside four plus signs (++++). This tells AsciiDoc to pass the content through as-is to the EPUB. [3]

```
++++
<div style="text-align: center; page-break-after: always;">
  <svg xmlns="http://w3.org"
       viewBox="0 0 4000 3000"
       width="100%"
       height="100%"
       preserveAspectRatio="xMidYMid meet">
    <image width="4000" height="3000" href="world-map.jpg"/>
  </svg>
</div>
++++
```

## 2. Key Components for Success

* viewBox: Replace 4000 3000 with the actual width and height of your image in pixels. This defines the "coordinate system" that allows for smooth zooming.
* image href: Ensure the filename exactly matches your map file. Most EPUB readers prefer href over xlink:href for modern compatibility.
* preserveAspectRatio: The xMidYMid meet value ensures your map stays centered and maintains its proportions regardless of the screen size. [4, 5]

## 3. Why this works for ReadEra and Lithium

* Prevents Shrinking: By using width="100%" on the SVG but keeping the large pixel values in the viewBox, the image fits the screen but retains all its high-resolution data.
* Native Zoom: When the user double-taps or pinches, the e-reader's engine recognizes the SVG container as a scalable object, enabling much smoother panning than a standard <img> tag.
* Avoids URL Errors: Since this is embedded HTML, it avoids the "Markdown-to-Link" conversion that was causing your http://ebps/ errors. [2, 4, 5, 6]

## 4. Advanced: External SVG File

If you have already converted your map into a .svg file (rather than a .jpg), you can use the AsciiDoc image macro with the opts=inline attribute to achieve the same effect automatically: [7, 8]

```
image::world-map.svg[opts=inline, width=100%]
```

Note: To use the opts=inline method, your .svg file must contain a valid viewBox attribute in its root tag. [5, 9]

Do you have the exact pixel dimensions of your world map image so I can give you the precise viewBox values?

[1] [https://discuss.asciidoctor.org](https://discuss.asciidoctor.org/Embed-vector-formats-svg-ps-or-pdf-td1334.html)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=YtHrGetFFwE&t=13)
[3] [https://blog.mrhaki.com](https://blog.mrhaki.com/2014/06/awesome-asciidoc-include-raw-html.html)
[4] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)
[5] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)
[6] [https://dev.to](https://dev.to/yakovlev_alexey/creating-an-interactive-svg-map-on-the-web-2d64)
[7] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)
[8] [https://github.com](https://github.com/asciidoctor/asciidoctor-diagram/issues/247)
[9] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)

# ---

No, definitely do not rename the file extension. Renaming a .jpg to .svg won't change how the file works; it will just break the image because the e-reader won't know how to read it.

Here is the difference:

* JPG/PNG: These are "Raster" images (made of pixels).
* SVG: This is a "Vector" format (made of code/math).

When we talk about an SVG Wrapper, we are essentially building a "digital frame" (using SVG code) and putting your JPG map inside it. This "frame" is what tells the e-reader how to handle the zooming.

## How to do it correctly with your JPG:

Keep your file named world-map.jpg. Use the AsciiDoc "passthrough" block to build the frame around it.

Use this exact code in your AsciiDoc file:

```
++++
<div style="text-align: center; page-break-after: always;">
  <svg xmlns="http://w3.org"
       viewBox="0 0 [WIDTH] [HEIGHT]"
       width="100%"
       preserveAspectRatio="xMidYMid meet">
    <image width="[WIDTH]" height="[HEIGHT]" href="world-map.jpg"/>
  </svg>
</div>
++++
```

## Two things you MUST change in that code:

   1. [WIDTH]: Replace this with the actual width of your image in pixels (e.g., 4000).
   2. [HEIGHT]: Replace this with the actual height of your image in pixels (e.g., 3000).

Why this works:

The <svg> tag acts as the "container." Inside it, the <image> tag points to your actual .jpg file. Because the "container" has a viewBox, apps like ReadEra and Lithium understand that there is a large coordinate system they can zoom into, even though the image is being displayed at width="100%" to fit the screen.

Do you know how to find the pixel dimensions (Width and Height) of your map image?

# ---

Yes, you can automate this by creating a custom extension or filter that intercepts your image syntax and wraps it in the SVG code automatically. This keeps your document clean while ensuring the "universal" zoom fix is applied.

## 1. For AsciiDoc: Custom Block Macro (Ruby)

AsciiDoc allows you to define a new macro (e.g., `map::world-map.jpg[]`) that expands into the full SVG wrapper. You can save this as a small Ruby file (e.g., map_macro.rb) and run it with your conversion.

* The Ruby Extension (map_macro.rb):

```
Asciidoctor::Extensions.register do
  block_macro :map do
    process do |parent, target, attrs|
      # Default dimensions or pull from attrs if provided
      width = attrs['width'] || '4000'
      height = attrs['height'] || '3000'

      # The SVG Wrapper HTML
      html = %(
<div style="text-align: center; page-break-after: always;">
  <svg xmlns="http://w3.org" viewBox="0 0 #{width} #{height}" width="100%" preserveAspectRatio="xMidYMid meet">
    <image width="#{width}" height="#{height}" href="#{target}"/>
  </svg>
</div>)
      create_pass_block parent, html, attrs
    end
  endend
```

* Usage in your .adoc file:

```
map::world-map.jpg[width=4000, height=3000]
```

* How to run it:

```
asciidoctor-epub3 -r ./map_macro.rb your-book.adoc [1, 2]
```

## 2. For Markdown: Pandoc Lua Filter

If you stick with Markdown and use [Pandoc](https://pandoc.org/lua-filters.html), you can write a Lua filter that automatically wraps every image (or just specific ones) in an SVG container during the EPUB conversion. [3]

* The Lua Filter (svg-wrap.lua):

```
function Image (img)
  -- This only applies if you are converting to EPUB
  if FORMAT:match 'epub' then
    local src = img.src
    local width = "4000"  -- You can also extract this from img.attributes
    local height = "3000"

    return pandoc.RawBlock('html', [[
<div style="text-align: center; page-break-after: always;">
  <svg xmlns="http://w3.org" viewBox="0 0 ]] .. width .. [[ ]] .. height .. [[" width="100%" preserveAspectRatio="xMidYMid meet">
    <image width="]] .. width .. [[" height="]] .. height .. [[" href="]] .. src .. [["/>
  </svg>
</div>]])
  endend
```

* How to run it:

```
pandoc your-book.md -o your-book.epub --lua-filter=svg-wrap.lua [4, 5]
```

## Which should you choose?

* [AsciiDoc](https://asciidoc.org/) is better if you want a new command (like map::) so you can still use regular image:: for small pictures.
* Markdown + Lua is better if you want to automatically fix all existing images without changing your writing style. [3, 6]

Do you have any experience with Ruby or Lua, or would you like me to walk you through installing the tools needed to run these?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctor/latest/features/)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)
[3] [https://pandoc.org](https://pandoc.org/lua-filters.html)
[4] [https://pandoc.org](https://pandoc.org/filters.html)
[5] [https://github.com](https://github.com/jgm/pandoc/issues/2766)
[6] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctorj/latest/extensions/block-macro-processor/)

# ---

Since you are using Asciidoctor.js, you can create a custom Block Macro to automate the SVG wrapper. This allows you to use a clean syntax like map::world-map.jpg[width=4000, height=3000] instead of manual HTML blocks. [1, 2]

## 1. The Javascript Extension (map-extension.js)

Create a file to define your custom map:: macro. This extension intercepts the macro and replaces it with the high-resolution SVG "frame". [2, 3]

```
const asciidoctor = require('@asciidoctor/core')()
function register (registry) {
  registry.blockMacro('map', function () {
    const self = this
    self.process((parent, target, attrs) => {
      const width = attrs.width || '4000'
      const height = attrs.height || '3000'
      const title = attrs.title || ''

      // The SVG wrapper as raw HTML
      const content = `
<div class="imageblock" style="text-align: center; page-break-after: always;">
  <div class="content">
    <svg xmlns="http://w3.org" viewBox="0 0 ${width} ${height}" width="100%" preserveAspectRatio="xMidYMid meet">
      <image width="${width}" height="${height}" href="${target}"/>
    </svg>
  </div>
  ${title ? `<div class="title">${title}</div>` : ''}
</div>`

      // Pass the HTML directly into the output (context: 'pass')
      return self.createBlock(parent, 'pass', content)
    })
  })
}
module.exports = register
```

## 2. Usage in your AsciiDoc file

Once registered, you can use the macro anywhere in your .adoc document: [4]

```
.Detailed World Map
map::world-map.jpg[width=4000, height=3000]
```

## 3. How to Run the Conversion

To use the extension, you must register it with the Asciidoctor.js processor when you convert your file. [2, 5]

If you are using a Node.js script:

```
const asciidoctor = require('@asciidoctor/core')()const registry = asciidoctor.Extensions.create()require('./map-extension.js')(registry)

asciidoctor.convertFile('my-book.adoc', {
  to_file: true,
  safe: 'safe',
  backend: 'epub3',
  extension_registry: registry
})
```

## Why this is the best method:

* Encapsulation: You keep the ugly SVG/HTML code out of your manuscript.
* Correct Resolution: By defining the viewBox with your actual pixel dimensions, you give apps like ReadEra and Lithium the data they need to enable high-quality zooming.
* Consistency: You can reuse the map:: macro for every map in your book without re-typing the SVG code. [6]

Do you have the exact width and height of your map image so you can set the correct default values in your script?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctorj/latest/extensions/block-macro-processor/)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctor.js/latest/extend/extensions/block-macro-processor/)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctorj/latest/extensions/block-macro-processor/)
[4] [https://blog.yuzutech.fr](https://blog.yuzutech.fr/write-a-gist-extension/)
[5] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoctor.js/latest/extend/extensions/register/#:~:text=const%20asciidoctor%20=%20require%28%27asciidoctor%27%29%28%29%20const%20registryA%20=,//%20Say%20it%20proud%20!!!%0A//%20%0Ajavascript%20Copied!)
[6] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-svg/)

# ---

To insert a book cover, you typically use document-level metadata attributes rather than a standard image link. This tells the EPUB packager to register the image in a special "cover" slot in the book's internal structure.

## 1. Cover Syntax

Instead of placing an image in the body of your text, you define it at the very top of your file.

* Markdown (Pandoc):

Add cover-image to your YAML metadata block at the start of the file.

```
---title: Your Book Titlecover-image: path/to/cover.jpg
---
```

* AsciiDoc:
Use the :front-cover-image: attribute in the document header.

```
= Your Book Title
:front-cover-image: image:cover.jpg[Front Cover,1050,1600]
```

Note: Asciidoctor EPUB3 requires the alt text, width, and height to be explicitly defined in positional attributes for covers. [1, 2, 3]

## 2. Why you can't use this for the World Map

While it’s tempting to use "cover" logic for your map, it won't work for your needs for several reasons:

* Slot Limitation: EPUBs generally only have one "Front Cover" slot. If you try to assign the map as a cover, it will either replace your actual book cover or be ignored.
* Navigation Issues: Covers are treated as metadata. Most e-readers won't allow you to "turn the page" to a second cover; they expect a cover at the start and then the content begins.
* Fixed vs. Reflowable: Covers are often "locked" to the screen size by the device to ensure they look good in the library view. This often disables pinching and zooming because the device assumes the cover is a static placeholder, not a detailed document you need to study.
* Placement: The cover attribute places the image at the absolute beginning of the book. You cannot use this method to place the map later in the book (e.g., after the Table of Contents or at the start of a specific chapter). [4, 5, 6, 7, 8]

## The Best Strategy

Keep the Cover as a metadata attribute so your book looks professional in the ReadEra/Lithium library, but use the SVG Wrapper method we discussed for the World Map. The SVG wrapper gives you "cover-like" full-screen behavior while keeping the image interactive and zoomable within the actual flow of your book. [9]

Do you need help combining your map extension with a standard cover attribute in your build script?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/25923593/add-cover-image-for-asciidoc-epub3)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-position/)
[3] [https://pandoc.org](https://pandoc.org/MANUAL.html)
[4] [https://www.facebook.com](https://www.facebook.com/groups/amazonkdpbooks/posts/1177191223320733/)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=8yqBL_z0oQ8&t=3)
[6] [https://idpf.org](https://idpf.org/forum/topic-715#:~:text=The%20EPUB%203%20spec%20states%20that%20the,corresponding%20item%20element%20in%20the%20Manifest%20Section:)
[7] [https://kdp.amazon.com](https://kdp.amazon.com/en_US/help/topic/G6GTK3T3NUHKLEFX)
[8] [https://www.epubble.com](https://www.epubble.com/kindle-inside-cover-still-mandatory/)
[9] [https://www.reddit.com](https://www.reddit.com/r/Calibre/comments/gr68l9/book_cover_is_showing_up_fine_in_epub_format_but/)

# ---

In AsciiDoc, yes! You can define a "template" using an Attribute Reference. This allows you to "code" the SVG wrapper once at the top of your document and reuse it without needing external Javascript or Ruby files.

In Markdown, no—standard Markdown has no concept of internal macros or variables, so you are forced to use raw HTML or an external filter (like the Lua filter mentioned earlier).

## The AsciiDoc "Internal Macro" Solution

You can define a document attribute that holds the SVG code and use "placeholders" for the image name and dimensions.

## 1. Define the "Macro" in your Header

Add this to the very top of your .adoc file:

```
:map-svg: ++++ \
<div style="text-align: center; page-break-after: always;"> \
  <svg xmlns="http://w3.org" viewBox="0 0 {map-w} {map-h}" width="100%" preserveAspectRatio="xMidYMid meet"> \
    <image width="{map-w}" height="{map-h}" href="{map-src}"/> \
  </svg> \
</div> \
++++
```

## 2. Use it in your Document

When you want to insert a map, you simply set the values for the "variables" and then call the attribute:

```
// Set the specific map details
:map-src: world-map.jpg
:map-w: 4000
:map-h: 3000

// Trigger the "macro"
{map-svg}

// You can reuse it later for a different map
:map-src: regional-map.png
:map-w: 2000
:map-h: 2000
{map-svg}
```

## Why this works:

* No External Files: This lives entirely inside your .adoc file.
* Asciidoctor.js Compatible: Since this uses standard attribute substitution, it works perfectly with the JS version of Asciidoctor.
* Universal Zoom: Because it outputs the SVG wrapper, ReadEra and Lithium will treat it as a high-resolution scalable object.

## The Markdown "Workaround"

If you must stay in Markdown and cannot use a Lua filter, your only option is to use a Snippet/Template feature in your text editor (like VS Code User Snippets). You would type a shortcut (like svgmap), and the editor would paste the HTML block for you to fill in.

Which editor are you using to write your documents? (I can give you the specific snippet code for VS Code or Obsidian if you need it.)

# ---

To do this entirely inside the AsciiDoc file without any external JavaScript or Ruby files, you can use Attribute References with a specific naming convention.

Since AsciiDoc doesn't have true "arrays" or "functions" that take arguments like a programming language, the trick is to define a "Template Attribute" that consumes other attributes you set right before calling it.

## 1. Define the "Template" in your Header

Add this to the very top of your .adoc file. This is your "engine."

```
:map-svg: ++++ \
<div style="text-align: center; page-break-after: always;"> \
  <svg xmlns="http://w3.org" viewBox="0 0 {map-w} {map-h}" width="100%" preserveAspectRatio="xMidYMid meet"> \
    <image width="{map-w}" height="{map-h}" href="{map-src}"/> \
  </svg> \
</div> \
++++
```

## 2. Use it like a "Macro" in your document

To insert a map, you simply update the "variables" and then "call" the template. This acts exactly like passing arguments to a function.

```
// First Map
:map-src: world-map.jpg
:map-w: 4000
:map-h: 3000
{map-svg}

// Second Map (different size)
:map-src: dungeon-map.png
:map-w: 2000
:map-h: 2000
{map-svg}
```

## Why this works for you:

   1. No JavaScript/Ruby: It uses native AsciiDoc attribute substitution.
   2. Universal Zoom: By wrapping it in the <svg> tag with the correct viewBox, ReadEra and Lithium will treat it as a high-resolution canvas, allowing smooth pinching and panning.
   3. No URL Errors: Because it's raw HTML inside a passthrough (++++), the reader won't try to treat it as a broken http://ebps/ web link.
   4. Full Page: The page-break-after: always ensures the map gets its own dedicated screen, which is standard for fantasy maps.

## A Note on the "Array" Request

Because AsciiDoc isn't a programming language, it can't "split" a single string like world-map.jpg,4000,3000 into three parts without an external script. Setting the three attributes (:map-src:, :map-w:, :map-h:) is the cleanest and only way to do this using pure AsciiDoc syntax.

Does this setup work for your workflow, or do you need a way to add a [title] below the map as well?

# ---

You're very welcome! That attribute-based SVG wrapper is the most robust way to handle high-resolution maps in a pure AsciiDoc workflow. It keeps your file clean while ensuring readers like ReadEra and Lithium treat your world map with the detail it deserves.

Happy world-building and book-making!

One last tip: Before you do your final export, double-check that your map image file names don't have spaces (use world-map.jpg instead of world map.jpg), as some EPUB readers can be finicky with spaces in SVG paths.

Do you have your book cover and stylesheet ready for the final conversion, or would you like to see a basic CSS template to make the text look as professional as the map?
