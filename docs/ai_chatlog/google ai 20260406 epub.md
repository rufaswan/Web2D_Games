To create a tutorial in AsciiDoc with images and text for each step, you can use Ordered Lists combined with Block Image Macros. The key is using the list continuation character (+) to attach the image to the list item so it doesn't break the numbering. [1, 2]

## Basic Step-by-Step Structure

To keep your steps numbered correctly while including images and multiple paragraphs, follow this syntax:

```
. Step One Title
This is the description for the first step.
+
image::step1.png[Alt text for step 1]

. Step Two Title
This is the description for the second step.
+
image::step2.png[Alt text for step 2]
```

## Key Elements for Tutorials

* Ordered Lists (. ): Use a single period followed by a space for top-level numbered steps.
* List Continuation (+): Place a plus sign on its own line between the text and the image. This "attaches" the image block to the preceding list item, ensuring the next step continues with the correct number (e.g., from 1 to 2).
* Block Image Macro (image::[]): Use two colons for block images to ensure they appear on their own line.
* Block Titles (.Title): You can add a caption or title directly above an image using a period followed immediately by the title text. [3, 4, 5, 6, 7, 8, 9]

## Advanced Formatting Tips

* UI Macros: For software tutorials, use btn:[Button Name], menu:File[Save], or kbd:[Ctrl+C] to make instructions clearer. Note: These require enabling the :experimental: attribute in your document header.
* Image Sizing: Control the display size within the square brackets, e.g., image::step1.png[Step 1, 400, 300] to set width and height.
* Admonitions: Use blocks like TIP:, NOTE:, or IMPORTANT: to highlight critical information within a step. [8, 9, 10, 11]

Do you need help aligning the images (e.g., floating them to the right of the text) or setting up a global images directory?

[1] [https://writetheasciidocs.netlify.app](https://writetheasciidocs.netlify.app/getting-started#:~:text=A%20special%20feature%20of%20AsciiDoc%20is%20that,a%20line%20with%20only%20a%20plus%20character.)
[2] [https://wiki.evergreen-ils.org](https://wiki.evergreen-ils.org/doku.php?id=evergreen-docs:dig_style_guide#:~:text=Common%20Mistakes%20AsciiDoc%20lists%20%28numbered%20or%20bulleted%29,the%20numbering%20continues%20in%20the%20correct%20order.)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)
[4] [https://www.writethedocs.org](https://www.writethedocs.org/guide/writing/asciidoc/)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=1pAdWsmbRUo&t=210)
[6] [https://docs.antora.org](https://docs.antora.org/antora/latest/asciidoc/lists/)
[7] [https://stackoverflow.com](https://stackoverflow.com/questions/64609897/how-do-i-include-an-image-in-a-nested-list-with-asciidoc-without-the-image-tabbi)
[8] [https://www.seinan-gu.ac.jp](https://www.seinan-gu.ac.jp/~shito/asciidoctor/html_chunk/chap29.html)
[9] [https://docs.couchbase.com](https://docs.couchbase.com/home/contribute/basics.html)
[10] [https://docs.couchbase.com](https://docs.couchbase.com/home/contribute/basics.html)
[11] [https://www.youtube.com](https://www.youtube.com/watch?v=DfXAV8hztQQ&t=5)

# ---

To create a tutorial where images and text are paired without the "Figure 1:" labels or standard figure captions, you can use a combination of document attributes and layout blocks.

## 1. Disable Automatic Figure Captions [1]

To prevent AsciiDoc from automatically adding "Figure 1:" or "Figure 2:" labels to your images, add this line to your document header: [2, 3]

```
:figure-caption!:
```

## 2. Side-by-Side Layout (Text next to Image)

If you want the description to sit right next to the image instead of below it, use a Table or a Side-by-Side block. This is a common way to avoid the "figure" look.

## Using a Table (Easiest for alignment)

Tables are the most reliable way to force text and images to stay together in a specific layout.

```
[cols="1,2", frame=none, grid=none]

|===
| image::step1.png[Step 1]
| *Step 1: Open the App* +
This text appears next to the image. Because the table grid and frame are set to `none`, it looks like a clean layout rather than a formal table.


| image::step2.png[Step 2]
| *Step 2: Log In* +
Enter your credentials here.
|===
```

## 3. Floating Images (Wrapping Text)

If you want the text to wrap around the image (like a magazine layout), use the float attribute. This avoids any "caption" structure entirely. [4, 5]

```
image::step1.png[Step 1, 200, float="right"]
*Step 1: The Setup*
Start by configuring your workspace. The image on the right shows the default layout. You can adjust the width (here `200`) to fit your page.

[after-float]
'''
// Use a horizontal rule or empty block to clear the float for the next step
```

## 4. Simple Block Style (Image above Text)

If you just want a clean vertical list without any "Figure" labels, simply place the image and text one after another without using the .Title syntax (which triggers captions). [3, 6, 7]

```
=== Step 1: Initialize
image::step1.png[Alt text]
First, run the initialization command in your terminal.

=== Step 2: Configure
image::step2.png[Alt text]
Next, open the settings file.
```

Which layout fits your tutorial better: a side-by-side table or a vertical stack?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/#:~:text=Figure%20caption%20label%20When%20a%20title%20is,the%20figure%2Dcaption%20attribute%20in%20the%20document%20header.)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/#:~:text=Figure%20caption%20label%20When%20a%20title%20is,the%20figure%2Dcaption%20attribute%20in%20the%20document%20header.)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)
[4] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-position/)
[5] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/image-position/)
[6] [https://asciidoctor.org](https://asciidoctor.org/docs/asciidoc-writers-guide/)
[7] [https://www.writethedocs.org](https://www.writethedocs.org/guide/writing/asciidoc.html)

# ---

To solve the "Is this text for the image above or below?" problem without using captions, you need visual grouping.

In AsciiDoc, you can use Delimited Blocks or Open Blocks to create a clear "container" for each step. This creates a visual break that tells the reader "everything inside these bounds belongs together."

## 1. The Open Block (Cleanest Look)

Use -- to wrap each step. This groups the image and text into a single logical unit. In many HTML themes, this also adds a subtle indentation or spacing that separates Step 1 from Step 2.

```
--
image::step1.png[]
*Step 1:* Click the "Start" button to begin the process.
--

--
image::step2.png[]
*Step 2:* Enter your username in the field shown above.
--
```

## 2. The "Example" Block (Visual Border)

If your theme supports it, using an Example Block (====) puts a border around each step. This makes it impossible to confuse which text goes with which image.

```
====
image::step1.png[]
*Step 1:* Open the dashboard.
====

====
image::step2.png[]
*Step 2:* Select the "Analytics" tab.
====
```

## 3. Role-based Styling (Custom Spacing)

If you want to keep it simple but fix the spacing, use a Role (CSS class). You can add extra bottom margin to the last element of a step to "push" the next step away.

```
[.step]
image::step1.png[]
This text clearly belongs to the image above because of the gap below.

[.step]
image::step2.png[]
This is the next step.
```

## 4. Use a Sidebar Block

If you want the step to look like a "card," use the Sidebar syntax (****). It usually applies a background color or a distinct border.

```
****
image::step1.png[]
*Step 1:* Plugin the device.
****

****
image::step2.png[]
*Step 2:* Wait for the green light.
****
```

Pro Tip: To make it even clearer, always put the text below the image. Readers naturally look at the visual first, then read the explanation.

Would you like to see how to add CSS to make these blocks look like modern "cards"?

# ---

To include captions in your tutorial while keeping the steps clear and grouped, you can use Block Titles (the . syntax) combined with Open Blocks or Admonitions. [1]

In [AsciiDoc](https://asciidoc.org/), if you add a title to an image, it automatically becomes a captioned figure. To prevent the "Figure 1:" prefix from confusing your tutorial numbering, you can either unset the global label or group them inside containers. [2, 3]

## 1. The "Clean Caption" Approach (No "Figure X" Label)

By unsetting :figure-caption!:, you can use image titles as clean captions. This makes it obvious that the text belongs to the image immediately below it without adding extra numbering. [2, 4, 5]

```
:figure-caption!: <1>

.Step 1: Open the configuration menu
image::menu.png[Menu screenshot] <2>
The button is located in the top-right corner.

.Step 2: Enter your API key
image::settings.png[Settings screenshot]
Make sure to copy the full string from your dashboard.


* <1> :figure-caption!:: Disables the automatic "Figure 1:" prefix.
* <2> .Title: Displays the text as a caption directly above (or below, depending on theme) the image. [3, 4]
```

## 2. Grouping with Open Blocks (Strong Visual Association)

To ensure the reader knows which text and caption belong together in a long sequence of img-p-img-p, wrap each step in an Open Block (--). This creates a logical "container". [6]

```
--
.Step 1: Launch the app
image::launch.png[]
Double-click the desktop icon to begin.
--

--
.Step 2: Log in
image::login.png[]
Use your standard credentials provided by IT.
--
```

## 3. Using Admonition Blocks as "Step Cards"

For a high-impact tutorial, use the Example Block (====). This usually renders with a border, making it a "card" where the caption, image, and description are clearly isolated from other steps. [6]

```
[caption=""] <1>
.Phase 1: Installation
====
image::install.png[]
*Description:* Run the installer as an administrator to ensure all files are correctly placed.
====

[caption=""]
.Phase 2: Setup
====
image::setup.png[]
*Description:* Select the "Full Install" option to include all plugins.
====
```

* <1> [caption=""]: Disables the label (like "Example 1:") for just this specific block. [7]

## Summary of Best Practices for Tutorials

* Positioning: Most documentation themes place the .Title above the image by default. If you prefer it below, this is usually controlled via a theme attribute like caption-end: bottom in PDF converters.
* Tooltips: For inline images, the title attribute often appears as a tooltip when hovering.
* Spacing: Do not leave a blank line between the title (.) and the image macro; this ensures they are treated as a single captioned unit. [3, 8, 9, 10, 11]

Would you like to know how to center both the image and the caption together?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/tables/add-title/)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)
[3] [https://www.seinan-gu.ac.jp](https://www.seinan-gu.ac.jp/~shito/asciidoctor/html_chunk/chap29.html)
[4] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/#:~:text=Figure%20caption%20label.%20When%20a%20title%20is,the%20figure%2Dcaption%20attribute%20in%20the%20document%20header.)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=EcSKiNAnKkg)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/61057133/is-it-possible-to-position-image-title-caption-above-the-image-in-asciidoctor)
[7] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/tables/turn-off-title-label/)
[8] [https://stackoverflow.com](https://stackoverflow.com/questions/67413522/how-to-add-a-blank-caption-for-a-figure-in-asciidoc)
[9] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/pdf-converter/latest/theme/block-images/)
[10] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/pdf-converter/latest/theme/block-images/)
[11] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/images/)

# ---

To group multiple steps (each containing an image and description) into a single logical block, you use Delimited Blocks.

The best choice for a tutorial is the Open Block (--) or the Example Block (====). These act as containers that keep everything inside "glued" together, preventing text from one step from bleeding into another.

## 1. The Open Block (Invisible Container)

The Open Block is the most versatile. It doesn't add a border (usually), but it tells the AsciiDoc processor that all the content inside belongs to one unit.

```
.Tutorial Phase: Initial Setup
--
Step 1: Open the terminal.
image::term.png[]

Step 2: Type `npm install`.
image::npm.png[]
--
```

## 2. The Example Block (Visual Card)

If you want a border or a background color to physically separate a group of steps from the rest of the page, use the Example Block.

```
[caption=""]
.Group A: Hardware Connection
====
.Step 1: Plug in the cable
image::cable.png[]
Ensure the red side is facing up.

.Step 2: Turn on the switch
image::switch.png[]
The LED should blink blue.
====
```

## 3. Nesting inside a List (The "+" Continuation)

If your "group" is actually part of a larger numbered list, use the plus sign (+) to attach a whole block of content to a single list item.

```
1. **Phase One: Preparation**
+
--
First, gather your tools.
image::tools.png[]

Then, clear your workspace.
image::desk.png[]
--

2. **Phase Two: Execution**
+
...
```

## Why use these grouping methods?

* Keeps Captions Attached: If you use .Title inside a block, it stays strictly with the image it precedes.
* Prevents Layout Breaks: In PDF or Print, grouping blocks helps prevent a page break from occurring between a description and its corresponding image.
* Styling: You can apply a CSS class (role) to the entire block at once, e.g., [.my-tutorial-card].

Do you want to know how to apply a specific CSS class to these blocks to make them look like "cards" in your HTML output?

# ---

In AsciiDoc, physical tabs or spaces at the start of a line change the meaning of the content (usually turning it into a "Literal Block" with a monospace font), which is why you’ve likely found it breaks your formatting.

To make your .adoc file readable for you (the author) while keeping the output correct, use these three strategies:

## 1. Use "Open Blocks" for Visual Indentation

The Open Block (--) is the best way to "group" a step. While you can't easily indent the text itself, the delimiters provide a clear visual start and stop for each step in your text editor.

```
--
.Step 1: The Setup
image::step1.png[]
This is the description.
--

--
.Step 2: The Config
image::step2.png[]
This is the next description.
--
```

## 2. Use Attribute Offsets (The "Author's Hack")

If you absolutely need the text to look indented in your editor, you can wrap your steps in a List Continuation (+) or a Literal Block, but it's risky.

Instead, use Comment Lines to create "visual gutters" in your text editor. This doesn't change the output but makes the .adoc file much easier to scan:

```
////
    STEP 1 START
////
.Step 1 Title
image::img1.png[]
Step 1 description text goes here.
////
    STEP 1 END
////
```

## 3. Use an Editor with "Soft Wrap" and "Fenced Folders"

Since AsciiDoc relies on the left margin, most pros don't indent the code. Instead, they use editor features:

* VS Code + AsciiDoc Extension: This allows you to "fold" blocks (like the -- blocks mentioned above). You can collapse Step 1 to see only Step 2.
* Breadcrumbs: Use headings (== Step 1) for every step. This allows you to use the "Outline" view in your editor to jump between steps without scrolling through a wall of text.

## 4. The "Table" Method (Best for Source Code Readability)

If you want your source file to look organized, a table actually allows you to see the "Image" and "Text" columns clearly even in the raw text file:

```
[cols="1,1", frame=none, grid=none]

|===
| image::step1.png[] | *Step 1:* Put description here.
| image::step2.png[] | *Step 2:* Put description here.
|===
```

Warning: Never use a Tab at the start of a line in AsciiDoc unless you want that text to appear in a grey box (Code/Literal style).

Would you like to see how to configure VS Code to make these blocks easier to navigate?

# ---

To force a page break in AsciiDoc that works for EPUB conversion, use the Page Break Macro (<<<). [1]

## 1. Standard Page Break

Place three less-than characters (<<<) on their own line. This must be separated from preceding and following content by at least one empty line. [2, 3]

```
This is the end of a section.

<<<
```

This will start on a new "page" in the EPUB.

## 2. Forcing a Break (Always Option)

If a page break macro falls at the top of an already empty page, it is typically ignored. To force it regardless, use the %always option: [2, 4]

```
[%always]
<<<
```

## 3. How EPUB Handles Breaks

Unlike PDF (which has fixed pages), EPUB is reflowable. When the converter sees <<<, it typically creates a new HTML file or inserts a CSS page-break-after: always rule. [5, 6]

* Chapter Breaks: If you use the :doctype: book attribute, top-level headings (== Chapter Title) usually trigger automatic page breaks.
* Manual Control: Use the manual <<< break for specific points within a chapter, like separating "Step Groups" in your tutorial. [7, 8]

## Troubleshooting

* Backends: Ensure you are using a converter that supports this, such as asciidoctor-epub3.
* Conditional Breaks: If you want the break only for EPUB and not for HTML, use an ifdef block:

```
ifdef::backend-epub3[<<<]
```

Would you like to know how to automatically break after every Step Heading using a custom theme or CSS?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/epub3-converter/latest/)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/blocks/breaks/)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/blocks/breaks/)
[4] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/blocks/breaks/)
[5] [https://stackoverflow.com](https://stackoverflow.com/questions/2808652/how-to-implement-page-break-in-epub-reader)
[6] [https://www.reddit.com](https://www.reddit.com/r/software/comments/m6kw3j/i_want_to_insert_a_page_break_between_every/)
[7] [https://discuss.asciidoctor.org](https://discuss.asciidoctor.org/restrain-Section-Chapter-break-td7360.html)
[8] [https://discuss.asciidoctor.org](https://discuss.asciidoctor.org/restrain-Section-Chapter-break-td7360.html)

# ---

There is no single document attribute in AsciiDoc to globally force all links to target="_blank". However, you can achieve this through a simple HTML base tag or a shorthand syntax for individual links.

## 1. The Global "HTML Hack"

If you are converting to HTML, the easiest way to make every link open in a new tab without modifying each line is to insert a <base> tag into the document header.

Add this to the top of your .adoc file:

```
:docinfo: shared

Then, create a file named docinfo.html in the same folder with this content:

<base target="_blank">
```

When you convert to HTML, this will force all links on the page to open in a new tab by default. [1]

## 2. The Caret Shorthand (Best for EPUB/Standard)

For tutorials where you want more control, AsciiDoc provides a caret (^) shorthand. Adding a caret at the end of the link text automatically sets target="_blank" and rel="noopener". [2, 3]

* Syntax: https://google.com[Google^]
* Without text: https://google.com[^]

## 3. Enabling Link Attributes

If you want to use the full window="_blank" attribute, you must first enable link attributes in your document header: [4, 5, 6]

```
:linkattrs:

https://google.com[Google, window="_blank"]
```

## Why isn't there a global toggle?

Standard web accessibility guidelines discourage forcing new tabs because it can be disorienting for users (especially those using screen readers). AsciiDoc follows this by requiring you to explicitly mark links that should break the default browser behavior. [7]

Would you like a way to apply this only to external links while keeping internal ones in the same tab? [8]

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/24428476/target-blank-in-all-link)
[2] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/link-macro-attribute-parsing/)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/link-macro-ref/#:~:text=The%20blank%20window%20target%20can%20also%20be,at%20the%20end%20of%20the%20link%20text.)
[4] [https://blog.mrhaki.com](https://blog.mrhaki.com/2014/06/awesome-asciidoc-use-link-attributes.html)
[5] [https://blog.mrhaki.com](https://blog.mrhaki.com/2014/06/awesome-asciidoc-use-link-attributes.html#:~:text=To%20define%20a%20link%20in%20Asciidoc%20markup,make%20sure%20Asciidoctor%20will%20process%20the%20attributes.)
[6] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/macros/link-macro-attribute-parsing/)
[7] [https://dev.to](https://dev.to/cerchie/3-ways-to-make-target-blank-accessible-3lan)
[8] [https://www.drupal.org](https://www.drupal.org/project/drupal/issues/3536018)

# ---

In AsciiDoc, table borders are often missing due to either explicit attributes in your code or CSS stripping by the platform you are using to view the file.

## 1. Check for frame=none or grid=none

Earlier in this conversation, we used a table to group images and text. If you copied that code, notice the attributes frame=none and grid=none. These explicitly hide the borders. [1.1.1](https://docs.asciidoctor.org/asciidoc/latest/tables/borders/), 1.3.1

* frame: Controls the border around the table.
* grid: Controls the lines between cells. [1, 2]

To show all borders, remove those attributes or set them to all:

```
[cols="1,2", frame=all, grid=all]

|===
| Cell 1 | Cell 2
|===
```

## 2. GitHub or GitLab Rendering

If you are viewing your .adoc file directly on GitHub or GitLab, they often strip custom table styles and classes for security and consistent branding. [1.1.3](https://stackoverflow.com/questions/71081544/how-to-get-no-frame-on-a-table-in-asciidoc-on-github) On these platforms, tables may appear "borderless" or with very faint lines regardless of your AsciiDoc settings. To see the "true" look, you must convert the file to HTML or PDF locally.

## 3. Global Document Attributes

Check the top of your document (the header) for these lines, which might be disabling borders for the entire file: 1.5.1

* :table-frame: none
* :table-grid: none

## 4. Row or Column Spans

If you are using cell spanning (e.g., 2+|Content), certain CSS themes have bugs that cause borders to disappear on spanned cells because the browser can't "detect" where the table boundary ends. 1.1.1, [1.1.4](https://github.com/asciidoctor/asciidoctor/issues/2563) [3]

## 5. Custom Stylesheets

If you are using a custom CSS file, ensure it includes rules for the .tableblock, .frame-all, and .grid-all classes. Without these, the browser doesn't know how wide or what color the borders should be. [1.1.6](https://discuss.asciidoctor.org/Table-control-stripes-none-seems-to-be-ignored-td8223.html)

Are you seeing this issue in a local HTML preview, a PDF, or on a site like GitHub?

[1] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/tables/borders/)
[2] [https://asciidoc-py.github.io](https://asciidoc-py.github.io/chunked/ch23.html)
[3] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/tables/borders/)

# ---

The discrepancy between ReadEra and Lithium is a common issue in EPUB rendering. Many "modern" readers like ReadEra prioritize a clean, book-like reading experience and often ignore or override embedded table styles (like borders) to keep the text reflowable and easy to read. Lithium tends to be more faithful to the original CSS provided by the Asciidoctor converter. [1, 2, 3, 4]

To force borders to show in stricter readers like ReadEra, you typically need to use more "aggressive" CSS or specific AsciiDoc attributes.

## 1. Explicitly Set Frame and Grid [5]

Even if they are on by default, explicitly defining them in your table header can sometimes "wake up" a reader's rendering engine. [6]

```
[cols="1,2", frame=all, grid=all]

|===
| Step 1 | Description
|===
```

## 2. Use a Custom CSS Stylesheet (The "Nuclear" Option)

If the standard attributes aren't working, you can provide a custom CSS file to the asciidoctor-epub3 converter. Many readers ignore the border shorthand (e.g., border: 1px solid black) but will respect explicit declarations for table cells. [7]

Create a custom-epub.css file and add this:

```
/* Force borders on all table cells */
table.tableblock, th.tableblock, td.tableblock {
    border: 1px solid #000000 !important;
    border-collapse: collapse !important;
}
```

Then, convert your file using the styles attribute:

```
asciidoctor-epub3 -a epub3-stylesdir=. -a epub3-stylesheet=custom-epub.css tutorial.adoc
```

## 3. Change ReadEra's Internal Settings

ReadEra has a feature that allows you to switch between "Optimized" and "Original" layouts. [2]

   1. Open your book in ReadEra.
   2. Tap the center of the screen to open the menu.
   3. Look for the Settings (gear icon) or Text Settings.
   4. Ensure the "Original" or "Publisher's" style is enabled. If it is set to a "User" theme, it may be stripping your borders to match its own aesthetic.

## 4. Use an "Admonition" instead of a Table [8]

If borders are critical for your tutorial's visual grouping and tables continue to fail, consider using an Example Block (====) or Sidebar (****). These are often converted into <div> tags with borders, which EPUB readers are historically much better at rendering than <table> elements.

```
====
image::step1.png[]
*Step 1:* Description goes here.
====
```

Would you like me to show you how to bundle that custom CSS into your EPUB conversion command?

[1] [https://www.reddit.com](https://www.reddit.com/r/ebooks/comments/17pgdlh/need_help_with_epub_format_compatibility_across/#:~:text=I%27ve%20crafted%20a%20specific%20text%20format%20within,Moon+%20Reader%2C%20making%20my%20table%20look%20unsightly.)
[2] [https://www.reddit.com](https://www.reddit.com/r/androidapps/comments/118zpb1/is_there_away_to_view_the_original_epub_format_as/)
[3] [https://www.quora.com](https://www.quora.com/What-are-the-differences-between-the-several-eBook-formats-epub-kindle-pdf-etc-What-is-the-best-application-for-Android-for-reading-books-in-each-format)
[4] [https://forum.literatureandlatte.com](https://forum.literatureandlatte.com/t/improvement-tables-should-be-rendered-better-in-epub/144518)
[5] [https://stackoverflow.com](https://stackoverflow.com/questions/71081544/how-to-get-no-frame-on-a-table-in-asciidoc-on-github)
[6] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/asciidoc/latest/tables/borders/)
[7] [https://github.com](https://github.com/asciidoctor/asciidoctor-epub3/issues/43)
[8] [https://docs.asciidoctor.org](https://docs.asciidoctor.org/pdf-converter/latest/theme/tables/)

# ---

The invisibility of borders in EPUB readers like ReadEra is usually because they strip custom CSS to favor their own "clean" reading themes. When table gridlines are missing, large data sets become nearly impossible to scan. [1, 2, 3]

To force readable borders in an EPUB, you must use stricter CSS or structural alternatives that the reader cannot ignore.

## 1. The "Aggressive" CSS Fix

Most EPUB readers that ignore border: 1px solid will respect borders if they are applied directly to the table cells (td, th) using !important. [4]

Create a custom-styles.css file: [4]

```
/* Force visible borders on all table elements */
table.tableblock {
    border-collapse: collapse !important;
    border: 1px solid #333333 !important;
    margin-bottom: 1.25rem !important;
}

td.tableblock, th.tableblock {
    border: 1px solid #333333 !important;
    padding: 0.5em !important;
}
```

Convert with:

```
asciidoctor-epub3 -a epub3-stylesdir=. -a epub3-stylesheet=custom-styles.css your-file.adoc [5]
```

## 2. High-Contrast "Zebra" Striping

If the reader still refuses to show lines, background shading (striping) is the best alternative for readability. This ensures the eye can track rows even without a physical border. [6]

In your AsciiDoc table definition, add the stripes attribute:

```
[cols="1,2,1", stripes=even, frame=all, grid=all]

|===
| ID | Description | Status
| 01 | Initializing the system | DONE
| 02 | Configuring the network | PENDING
|===
```

## 3. Change ReadEra Rendering Mode

ReadEra has multiple rendering engines. If your data looks like a mess:

   1. Tap the center of the screen while reading.
   2. Go to Settings (gear icon) > Reading Mode.
   3. Switch from "Optimized" to "Original" (or "Publisher's Style"). This forces the app to use your CSS instead of its own. [7, 8]

## 4. Use an "Example Block" for Small Data [4]

For small tables that must have a border, wrap the table in an Example Block (====). EPUB readers are far more consistent at drawing borders around div containers than they are with complex table elements.

```
.Critical Data Points
====

|===
| Key | Value
| API | 0x455
|===
====
```

Would you like a sample custom-styles.css specifically optimized for high-contrast accessibility in e-readers?

[1] [https://www.facebook.com](https://www.facebook.com/groups/indesignsecrets/posts/2852996494934828/)
[2] [https://www.jutoh.com](https://www.jutoh.com/kb/html/section-0275.html)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=ONLPHJMfodM)
[4] [https://datatables.net](https://datatables.net/forums/discussion/63745/table-borders-dissapear-on-printing-table)
[5] [https://github.com](https://github.com/asciidoctor/asciidoctor-epub3/issues/43)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/49597579/how-to-get-non-striped-table-in-asciidoctor)
[7] [https://forum.literatureandlatte.com](https://forum.literatureandlatte.com/t/improvement-tables-should-be-rendered-better-in-epub/144518)
[8] [https://forums.j-novel.club](https://forums.j-novel.club/topic/6564/readera-cover-preview-off-for-premium-epubs)

# ---

No, the cols attribute is optional.

If you omit it, Asciidoctor will automatically:

   1. Count the columns based on the first row of data.
   2. Distribute the width equally (e.g., in a 3-column table, each gets 33%).

## When should you keep it?

Even though it's optional, you should use cols if:

* Proportions matter: If one column has a tiny icon and the other has a long paragraph, cols="1,4" prevents the icon column from being unnecessarily wide.
* Alignment: You want to center or right-align a specific column (e.g., cols="1,>2" right-aligns the second column).
* Styling: You want to apply specific formatting (like monospace) to an entire column (e.g., cols="1,2m").

## Example without cols:

```
|===
| Item | Description | Price

| Apple | A red fruit | $1
|===
```

Asciidoctor sees three headers and automatically creates three equal columns.

Are your tables looking "cramped" or "stretched" without it?

# ---

Yes, [stripes=even, frame=all, grid=all] is perfectly valid on its own!

If you omit the cols attribute, Asciidoctor will simply calculate the column widths automatically based on the content of the first row.

## How it looks in your code:

```
[stripes=even, frame=all, grid=all]

|===
| Step | Image | Description

| 1 | image::step1.png[] | Open the app.
| 2 | image::step2.png[] | Click login.
|===
```

## Why this is a good choice for EPUB:

   1. Stripes as a fallback: If ReadEra refuses to show the grid lines, the stripes=even will still give you alternating gray/white rows, making the data readable.
   2. Automatic Widths: On small phone screens (common for EPUB), fixed column ratios like cols="1,4" can sometimes make a column too skinny. Letting Asciidoc decide (by omitting cols) often results in a better reflow for mobile devices.

## One Small Tip

If your table starts to look "squashed" on a phone, you can add the autowidth option to the brackets to ensure the table only takes up as much space as it needs:

```
[stripes=even, frame=all, grid=all, options="autowidth"]
```

Do your tables have a lot of text, or are they mostly short data points?

# ---

In AsciiDoc, there isn't a single document attribute (like :image-align: center) that globally centers all images. However, you have three ways to achieve this depending on your workflow:

## 1. The Global CSS Fix (Best for HTML & EPUB)

Since you are converting to EPUB, the most reliable way is to add a single rule to your custom CSS file. This forces every block image to center without you having to type anything extra in your .adoc file.

Add this to your CSS:

```
div.imageblock {
    text-align: center !important;
}

div.imageblock img {
    margin-left: auto !important;
    margin-right: auto !important;
    display: block !important;
}
```

## 2. The Global Attribute (PDF Only)
If you were using Asciidoctor PDF, you could add this to your document header:

```
:image-align: center
```

Note: This specific attribute is currently only supported by the PDF converter, not the HTML or EPUB3 converters.

## 3. The Role Shorthand (The "Semi-Automatic" way)

If you don't want to use custom CSS, you can create a Role in your header and apply it to images. While it's not "default," it’s very fast to type:

Header:

```
:image-align: center
```

In your content:

```
[.text-center]
image::step1.png[]
```

(Most built-in Asciidoctor themes recognize .text-center or .align-center automatically).

## 4. Centering within Tables

Since you are using tables for your tutorial steps, images inside table cells ignore global alignment. You must center them via the cols attribute:

```
[cols="^,^", stripes=even, frame=all, grid=all] <1>

|===
| image::step1.png[] | Description 1
| image::step2.png[] | Description 2
|===
```

* <1> The ^ symbol in cols="^,^" tells AsciiDoc to horizontally center everything in those columns.

Should I show you how to apply that CSS specifically to your EPUB conversion command?

