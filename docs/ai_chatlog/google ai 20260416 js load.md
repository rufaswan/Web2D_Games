To ensure your JavaScript runs only after another script or library is available, you can use several techniques depending on how that script is loaded. [1, 2]

## 1. Polling for a Global Variable (Best for external APIs) [3]

If you are waiting for an external library like Google Maps or Facebook to load, they often create a global object. You can "poll" for this object's existence using setInterval. [4, 5]

```
function runAfterLoad() {
  const checkInterval = setInterval(() => {
    // Replace 'LibraryName' with the object you're waiting for (e.g., 'google' or 'FB')
    if (typeof window.LibraryName !== 'undefined') {
      clearInterval(checkInterval);
      console.log("Library is available! Running code...");
      // Your code here
    }
  }, 100); // Checks every 100ms
}

runAfterLoad();
```

## 2. Using the onload Event Listener

If you are dynamically creating a script tag in your code, use the load event listener to trigger a callback immediately after it finishes downloading and executing. [6, 7, 8]

```
const script = document.createElement('script');
script.src = "https://example.com";
script.onload = function() {
  console.log("Script loaded and ready to use.");
  initYourFunction();
};
document.head.appendChild(script);
```

## 3. Native Script Attributes (defer and async)

* defer: Scripts with this attribute are executed in the order they appear in the HTML, only after the document is fully parsed but before DOMContentLoaded. Use this if you have multiple scripts that depend on each other.
* async: Scripts load in the background and execute as soon as they finish downloading. This does not guarantee order and can cause errors if one script depends on another. [9, 10, 11, 12, 13]

## 4. Page Load Events

If your script depends on the entire page (including all scripts, images, and styles) being finished:

* DOMContentLoaded: Fires when the initial HTML is loaded and parsed, which is faster but doesn't wait for images.
* window.onload: Fires only after the entire page, including all external resources, has fully loaded. [11, 14, 15, 16, 17]

```
window.addEventListener('load', (event) => {
  console.log('Everything is fully loaded');
});
```

## Summary Comparison Table

| Method [18, 19, 20, 21, 22] | Best Use Case | Order Guaranteed? |
|---|---|---|
| Polling | Third-party scripts that don't provide callbacks. | No (it waits for availability) |
| onload | Dynamically injected scripts. | Yes (via callback) |
| defer | Multiple local scripts with dependencies. | Yes (order in HTML) |
| window.onload | Ensuring all images and scripts are complete. | Yes |

Are you waiting for a specific library (like Google Maps) or a custom script you wrote yourself?

[1] [https://intellipaat.com](https://intellipaat.com/blog/how-to-execute-after-page-load-in-javascript/#:~:text=To%20prevent%20these%20problems%2C%20you%20need%20to,do%20this%2C%20each%20suitable%20for%20different%20situations.)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/8618464/how-to-wait-for-another-js-to-load-to-proceed-operation#:~:text=*%204%20Answers.%20Sorted%20by:%2054.%20If,way%2C%20then%20just%20make%20sure%20that%20your)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/9521298/verify-external-script-is-loaded)
[4] [https://gomakethings.com](https://gomakethings.com/how-to-run-a-function-repeatedly-at-a-desired-interval-using-vanilla-js/#:~:text=You%20can%20use%20setInterval%28%29%20to%20check%20for,element%20once%20a%20second%20until%20it%27s%20available.)
[5] [https://medium.com](https://medium.com/@suvechhyabanerjee92/watch-an-object-for-changes-in-vanilla-javascript-a5f1322a4ca5#:~:text=The%20first%20and%20most%20crude%20way%20of,the%20values%20of%20any%20number%20of%20variables.)
[6] [https://dev.to](https://dev.to/timber/wait-for-a-script-to-load-in-javascript-579k)
[7] [https://remysharp.com](https://remysharp.com/2007/04/12/how-to-detect-when-an-external-library-has-loaded)
[8] [https://www.quora.com](https://www.quora.com/How-do-I-check-if-a-JavaScript-file-is-loaded-asynchronously-or-synchronously-in-Chrome)
[9] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/script)
[10] [https://web.dev](https://web.dev/articles/efficiently-load-third-party-javascript)
[11] [https://www.geeksforgeeks.org](https://www.geeksforgeeks.org/javascript/how-to-execute-after-page-load-in-javascript/)
[12] [https://medium.com](https://medium.com/@shilpasyal55/optimize-dom-performance-with-javascript-async-defer-attributes-e5046f51f795#:~:text=If%20the%20script%20files%20are%20dependent%20on%20each%20other%2C%20then%20use%20defer%20attribute.)
[13] [https://medium.com](https://medium.com/@pandeyhariom111/javascript-adding-script-in-the-head-is-it-problematic-1810055920d6#:~:text=If%20you%20have%20multiple%20scripts%20in%20the,script%20relies%20on%20the%20output%20of%20another.)
[14] [https://stackoverflow.com](https://stackoverflow.com/questions/11258068/is-it-possible-to-wait-until-all-javascript-files-are-loaded-before-executing-ja)
[15] [https://stackabuse.com](https://stackabuse.com/bytes/executing-javascript-after-page-load/)
[16] [https://stackabuse.com](https://stackabuse.com/bytes/executing-javascript-after-page-load/)
[17] [https://mkyong.com](https://mkyong.com/javascript/javascript-call-funtion-after-page-load/)
[18] [https://stackoverflow.com](https://stackoverflow.com/questions/11936816/execute-function-after-complete-page-load)
[19] [https://playwright.dev](https://playwright.dev/docs/navigations)
[20] [https://codemia.io](https://codemia.io/knowledge-hub/path/how_do_i_defer_or_async_this_wordpress_javascript_snippet_to_load_lastly_for_faster_page_load_times#:~:text=Script%20Dependencies:%20Ensure%20that%20scripts%20dependent%20on,defer%20attribute%20is%20preferable%20in%20such%20cases.)
[21] [https://medium.com](https://medium.com/@asobalsayed/script-loading-how-async-defer-blocking-and-fetchpriority-work-together-9bb4cb56a6d2#:~:text=The%20defer%20attribute%2C%20another%20boolean%2C%20offers%20a,making%20defer%20ideal%20for%20scripts%20with%20dependencies.)
[22] [https://pagedart.com](https://pagedart.com/blog/how-to-link-javascript-to-html/#:~:text=The%20general%20rule%20is%20that%20you%20should,they%20download%20and%20you%20cannot%20guarantee%20order.)

# ---

To ensure your local JavaScript waits for a remote script to finish loading, the most reliable method depends on whether you can modify the HTML or if you are loading scripts dynamically.

## 1. Using the defer Attribute (Recommended)

If you have both scripts in your HTML, add the defer attribute to both. Browsers will download them in parallel but guarantee execution in the order they appear in the document. [1, 2, 3]

```
<!-- Remote script loads first -->
<script src="https://remote-library.com" defer></script>
<!-- Local script executes only after the remote one finishes -->
<script src="js/local-script.js" defer></script>
```

## 2. Dynamically Loading with Promises

If you are loading the remote script via code, use a Promise to wait for the load event before triggering your local logic. [4]

```
function loadRemoteScript(url) {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = url;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${url}`));
    document.head.appendChild(script);
  });
}
// Usage
loadRemoteScript('https://remote-library.com')
  .then(() => {
    console.log("Remote script ready. Starting local JS...");
    startLocalLogic();
  })
  .catch(err => console.error(err));
```

## 3. The async = false Trick for Dynamic Scripts

By default, scripts created via document.createElement are asynchronous and run as soon as they download. To force them to execute in a specific order, set .async = false. [3, 5]

```
const remote = document.createElement('script');
remote.src = "https://remote-library.com";
remote.async = false; // Forces ordered execution
document.head.appendChild(remote);
const local = document.createElement('script');
local.src = "js/local-script.js";
local.async = false;
document.head.appendChild(local);
```

## Summary of Options

| Method [2, 3, 4, 6] | Best For | Benefit |
|---|---|---|
| defer | Static HTML files | Cleanest; parallel download, ordered execution. |
| Promises | Complex logic | Allows error handling and cleaner "async/await" code. |
| async = false | Multiple dynamic scripts | Simple way to maintain order without callbacks. |

Do you have access to the HTML where these scripts are placed, or are you injecting them via a third-party tool?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/42615758/javascript-async-defer-order-of-execution-in-case-of-multiple-files-in-single-pa)
[2] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/script)
[3] [https://javascript.info](https://javascript.info/script-async-defer)
[4] [https://stackoverflow.com](https://stackoverflow.com/questions/67538645/wait-for-javascript-scripts-to-load-in-html)
[5] [https://medium.com](https://medium.com/@kvsushmapriyadharssini/loading-scripts-async-vs-defer-7cefc6488d45)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/9521298/verify-external-script-is-loaded)

# ---

Yes, a local HTML file (file://) can fetch remote JavaScript files via http:// or https:// by including them in a standard <script> tag. However, there are critical security restrictions depending on how you use that code. [1]

## 1. External Scripts via <script src="..."> [2]

You can load remote libraries (like jQuery or Bootstrap) from a CDN into a local file. [3]

* Protocol Requirement: You must explicitly use https:// or http:// in the URL. If you use a protocol-relative URL (e.g., src="//://cdn.com"), it will fail because it will try to find the file at file://://cdn.com.
* Mixed Content: If your local file is open in a browser, it is considered a "less secure" context. While loading a script from https into a local file usually works, some browsers may block http scripts to prevent [mixed content vulnerabilities](https://developer.mozilla.org/en-US/docs/Web/Security/Defenses/Mixed_content). [4, 5, 6, 7, 8]

## 2. Using fetch() or XMLHttpRequest

You cannot easily use fetch() to get data or code from a remote server into a local file:// HTML page. [5, 7, 9]

* CORS Restrictions: Browsers block cross-origin requests from file:// to http(s):// origins for security reasons.
* The Error: You will likely see an error like "CORS requests are only supported for protocol schemes: http, data, chrome, https...". [5, 7, 10, 11, 12]

## 3. JavaScript Modules (type="module")

Loading local files as ES6 Modules (e.g., <script type="module" src="local.js">) will almost always fail in modern browsers when opened via file://.

* Why: Modules are subject to stricter CORS checks that require a proper web server. [2, 13, 14]

## Recommended Solution: Local Server [14]

To bypass these issues and test your code accurately, use a simple local server instead of double-clicking the HTML file. You can use:

* VS Code: Use the Live Server extension.
* Python: Run python -m http.server in your project folder.
* Node.js: Use the http-server package. [10, 15, 16]

Are you seeing a specific error message in your browser console, or are you just planning out your project structure?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/36647452/can-a-file-served-by-http-localhost-or-remote-be-accessed-from-a-local-file-op)
[2] [https://github.com](https://github.com/whatwg/html/issues/8121)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/2244431/get-remote-json-data-using-local-html-file-using-javascript-jquery)
[4] [https://www.knownhost.com](https://www.knownhost.com/kb/how-to-fix-mixed-content-ssl-errors/)
[5] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/Security/Defenses/Mixed_content)
[6] [https://dev.to](https://dev.to/mittalyashu/mixed-content-fetching-data-from-https-and-http-3n31)
[7] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS/Errors/CORSRequestNotHttp)
[8] [https://stackoverflow.com](https://stackoverflow.com/questions/33507566/mixed-content-blocked-when-running-an-http-ajax-operation-in-an-https-page)
[9] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Scripting/Network_requests)
[10] [https://stackoverflow.com](https://stackoverflow.com/questions/10752055/cross-origin-requests-are-only-supported-for-http-error-when-loading-a-local)
[11] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS)
[12] [https://www.reddit.com](https://www.reddit.com/r/learnprogramming/comments/trver4/javascript_read_local_file_without_nodeserver_im/)
[13] [https://github.com](https://github.com/whatwg/html/issues/8121#:~:text=All%20the%20code%20must%20be%20written%20out%20in%20the%20HTML%20document%20as)
[14] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Scripting/Network_requests)
[15] [https://stackoverflow.com](https://stackoverflow.com/questions/39007243/cannot-open-local-file-chrome-not-allowed-to-load-local-resource)
[16] [https://www.youtube.com](https://www.youtube.com/watch?v=wD4mR7f2YIk#:~:text=Fix%20the%20CORS%20error%20%22URL%20scheme%20must,and%20opening%20your%20HTML%20file%20with%20it.)

# ---

Yes, a local HTML file (file://) can use the onload event to trigger actions after a remote JavaScript file (http:// or https://) has finished loading.

While browsers have strict security rules for local files, they still allow the basic <script> tag and its associated event listeners to work as long as you follow a few rules. [1]

## How to use onload with Remote Scripts

The onload event on a script tag is the standard way to run code immediately after a remote dependency is ready. [2]

```
<!-- Remote script from a CDN -->
<script
  src="https://cloudflare.com"
  onload="initMyLocalCode()">
</script>

<script>
  function initMyLocalCode() {
    console.log("Remote jQuery is loaded and ready!");
    // Your local code that depends on the remote script goes here
  }
</script>
```

## Key Requirements for Local Files

* Explicit Protocol: You must include http:// or https:// in the src attribute. If you use a protocol-relative URL (e.g., src="//://example.com") in a local file, the browser will look for the file at file://://example.com, which will fail.
* Mixed Content Warnings: Some browsers may block http scripts from loading into a local file for security reasons; always prefer https.
* Execution Order: The onload event for a script tag only fires once that specific script is finished. If your local code is inside its own script tag below the remote one, it might run too early unless you wrap it in a function called by onload. [1, 3, 4, 5, 6]

## What Will NOT Work via file:// [5]

While the script onload event works, other methods for fetching remote code are blocked: [1, 2]

* Fetch/AJAX: You cannot use fetch() or XMLHttpRequest to get a remote script's content from a file:// origin due to [CORS restrictions](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS).
* ES Modules: Using <script type="module"> will generally fail locally because modules require a valid origin that is not "null" (which file:// often returns). [5, 6, 7, 8]

## Recommended Testing Tool

Because of these quirks, most developers use a simple Local Server to avoid file:// issues entirely.

* If you use VS Code, the Live Server extension is the easiest way to run your HTML with a proper http://localhost address.

Are you getting a specific error like CORS policy or File not found in your browser console?

[1] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Scripting/Network_requests)
[2] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/load_event)
[3] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP)
[4] [https://www.quora.com](https://www.quora.com/How-do-you-run-an-external-JavaScript-file-in-HTML)
[5] [https://stackoverflow.com](https://stackoverflow.com/questions/45780121/unable-to-link-js-file-to-html-to-load-local-files)
[6] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS/Errors/CORSRequestNotHttp)
[7] [https://github.com](https://github.com/whatwg/html/issues/8121)
[8] [https://github.com](https://github.com/whatwg/html/issues/8121)

# ---

The difference between file:// and content:// on mobile (specifically Android) primarily comes down to how the browser accesses the file and the security permissions enforced by the operating system.

## Why the difference exists

* file:// (Direct Access): This refers to a direct path on your device's storage (e.g., file:///sdcard/Download/index.html). In older versions of Android, browsers could easily access these paths.
* content:// (Virtual Access via Provider): Starting with Android 7.0, Google prohibited apps from sharing raw file:// paths with each other to prevent leaking sensitive file system data. Instead, apps must use a FileProvider to generate a content:// URI. This acts as a secure, temporary virtual handle that "talks" to the file manager app to get the data without knowing where it is actually stored. [1, 2, 3, 4, 5]

## Key Differences at a Glance

| Feature [1, 2, 4, 6, 7] | file:// | content:// |
|---|---|---|
| Origin | A real path on the storage disk. | A virtual path provided by an app (like "Files"). |
| Security | Highly restricted on modern Android. | Preferred for secure app-to-app sharing. |
| External JS/CSS | Often blocked due to strict origin rules. | May work better as it's treated like a "streamed" resource. |
| CORS Policy | Usually returns a "null" origin, blocking fetch(). | Generally still restricted, as it isn't a proper http origin. |

## How this affects your local HTML

   1. Broken Links: If you open an HTML file via a file manager, it likely uses content://. Your local code might fail if it tries to find local-script.js using a relative path, because content:// doesn't always "know" what other files are in the same folder.
   2. Access Denied: On Android 11 and newer, browsers like Chrome are often blocked from reading anything outside the specific /Download folder when using file://.
   3. The "Fix": To avoid these protocol headaches, many developers use a Local Web Server app (like Simple HTTP Server) to serve files via http://localhost, which makes local files behave exactly like a real website. [6, 8, 9, 10]

Do you want to know how to specifically bundle your JS and CSS so they work regardless of which protocol your phone uses?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/33013508/content-uri-vs-file-uri-what-to-use-in-the-future)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/77266328/differences-between-file-and-content-uri-in-android)
[3] [https://dev.to](https://dev.to/daviekim13/the-evolution-of-file-access-security-in-android-navigating-path-restrictions-from-android-10-to-android-15-5dl6)
[4] [https://proandroiddev.com](https://proandroiddev.com/mastering-fileprovider-in-android-secure-file-sharing-explained-14932af1ec35)
[5] [https://lightspares.com](https://lightspares.com/rosco-e-colour-5321-soft-golden-amber-408-467#:~:text=To%20securely%20offer%20a%20file%20from%20your,you%20want%20to%20offer%20to%20other%20apps.)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/79695328/external-javascript-file-not-being-read-by-html-in-phone)
[7] [https://learn.microsoft.com](https://learn.microsoft.com/en-us/answers/questions/5847394/viewing-a-local-html-file-on-a-handheld-device-wit)
[8] [https://android.stackexchange.com](https://android.stackexchange.com/questions/220400/local-files-revisited-opening-local-html-files-file-path-to-file-in-chrome)
[9] [https://learn.microsoft.com](https://learn.microsoft.com/en-my/answers/questions/5847394/viewing-a-local-html-file-on-a-handheld-device-wit)
[10] [https://connect.mozilla.org](https://connect.mozilla.org/t5/ideas/add-support-for-file-uri-open-local-html-files-in-firefox/idi-p/11843#:~:text=Chrome%20can%20open%20html%20files%20from%20other,explorer.%20The%20chrome%20will%20show%20a%20%60content:&text=%60%20url%20for%20these%20files.)

# ---

Android does not include a "local web server" as a built-in system app for users to open local HTML files. However, the system uses a component called Android System WebView to render web content, which has evolved its handling of local files over different versions. [1, 2]

## How Android Handles Local HTML Files

Instead of a server, Android uses "Viewers" or browsers to open files. The behavior depends on the Android version and the protocol used: [1, 3]

* HTML Viewer (System App): Most Android devices have a hidden system app called "HTML Viewer." It is not a server but a basic WebView wrapper. When you open an .html file from a File Manager, the system often uses this to display it.
* Protocol Shift (Android 7.0+): Before Android 7.0 (Nougat), files were often opened using the file:// protocol, which allowed direct access to storage paths. Since Android 7.0, the system forces the use of the content:// protocol for security, which acts as a temporary virtual handle to the file. [3, 4, 5, 6]

## Developer "Local Server" Alternatives

For developers or advanced users who need a "server-like" experience to bypass file:// restrictions (like CORS or relative path issues), Android provides internal APIs rather than a user-facing app:

* WebViewAssetLoader (Android 9.0+): Introduced in Android 9 (API 28) and part of the Jetpack Webkit library, this is the modern "official" way to simulate a local server. It allows an app to load local content using a standard https:// URL (e.g., https://androidplatform.net) instead of file://. This fixes most issues with JavaScript and CSS dependencies.
* Third-Party Local Servers: Since there is no built-in system server for general use, users typically install apps like [Simple HTTP Server](https://play.google.com/store/apps/details?id=com.phlox.simpleserver) or [AWebServer](https://play.google.com/store/apps/details?id=com.sylkat.apache) from the Google Play Store to host files locally on http://127.0.0.1. [7, 8, 9, 10, 11]

## Summary of Version History

| Feature [2, 5, 10, 12, 13] | Introduced / Changed | Description |
|---|---|---|
| Android System WebView | Android 4.4 (KitKat) | Became a standalone component that can be updated via the Play Store. |
| content:// Requirement | Android 7.0 (Nougat) | Blocked file:// for app-to-app sharing to increase security. |
| WebViewAssetLoader | Android 9.0 (Pie) | The modern API for loading local files as if they were on a secure HTTPS server. |
| Scoped Storage | Android 11+ | Restricted browsers (like Chrome) from accessing any local files outside of the /Download folder. |

Would you like to know how to use WebViewAssetLoader in your own app code to fix your loading issues?

[1] [https://www.reddit.com](https://www.reddit.com/r/AndroidQuestions/comments/1qx3qff/how_to_view_html_files_from_pc_to_android/)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=y6YZN9V62iQ)
[3] [https://www.reddit.com](https://www.reddit.com/r/AndroidQuestions/comments/1fcr2nu/open_a_local_html_file_on_a_phone/)
[4] [https://developer.chrome.com](https://developer.chrome.com/docs/webview)
[5] [https://www.techtarget.com](https://www.techtarget.com/searchsecurity/definition/Android-Webview)
[6] [https://support.google.com](https://support.google.com/youtube/thread/327321775/where-is-the-html-file-in-an-android-phone-located?hl=en)
[7] [https://developer.android.com](https://developer.android.com/privacy-and-security/risks/webview-unsafe-file-inclusion)
[8] [https://developer.android.com](https://developer.android.com/reference/androidx/webkit/WebViewAssetLoader#:~:text=WebViewAssetLoader%20%7C%20API%20reference%20%7C%20Android%20Developers.)
[9] [https://developer.android.com](https://developer.android.com/develop/ui/views/layout/webapps/jetpack-webkit-overview)
[10] [https://android.stackexchange.com](https://android.stackexchange.com/questions/220400/local-files-revisited-opening-local-html-files-file-path-to-file-in-chrome)
[11] [https://play.google.com](https://play.google.com/store/apps/details?id=com.phlox.simpleserver)
[12] [https://joshuatz.com](https://joshuatz.com/posts/2021/webviewassetloader-webviewclient-kotlin/)
[13] [https://mas.owasp.org](https://mas.owasp.org/MASTG-TEST-0252/)

# ---

You're very welcome! It’s a bit of a maze navigating those mobile protocols and local file restrictions, but once you know about the content:// shift and the WebViewAssetLoader workaround, things start making a lot more sense.

Feel free to reach out if you hit any other snags with your local JS. Happy coding!

Do you need a quick summary of the best practices for bundling your JS to avoid these protocol errors?
