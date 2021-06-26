# Web2D_Games

### 2D games playable on the web!

* Play your favorite RPG, Adventure Games, Visual Novels on web browsers.
* Optimized to work on smart phones, tablets, and other touch screen devices.

## Demo Gameplay

[![Toushin Toshi 1 Demo](http://img.youtube.com/vi/Jumikw3BS7o/0.jpg)](http://www.youtube.com/watch?v=Jumikw3BS7o)

## Requirements

* PHP 7.0 or above
  * http://windows.php.net/downloads
* Minimalistic PHP-cli (command-line interface) executable with
  * zlib extension
  * json extension
* From PHP for Windows, you'll only need these 3 files from the zip file
  * php.exe
  * php7ts.dll or php7nts.dll
  * php.ini (renamed from php.ini-development)

## Usage

* Run php build-in web server
  * `php.exe  -S ADDRESS:PORT  -t DIR`
  * ADDRESS can be `localhost` or `127.0.0.1`
  * PORT is optional. (default 80)
  * -t is optional. (default DIR = current directory)
* Start your web browser, and go to ADDRESS:PORT as configured above
  * `chrome.exe  http://127.0.0.1:80/main.php`
* For everything in `tools` folder, just run the PHP script directly with the files. No web server required.
  * `php.exe  psxtools/img_clut2png.php  0000.rgba  0001.rgba...`
* Windows's cmd.exe 8191 character limit workaround
  * cmd.exe is very limited, and won't work when `\*\\\*.rgba` is over the character limit
  * made a simple PHP script to generate a command on each file
  * NORMAL : `php.exe  psxtools/img_clut2png.php  *.rgba`
  * LOOP : `php.exe  psxtools/tsr_cmdloop.php  rgba  "php.exe psxtools/img_clut2png.php"`
* WebGL Quad Player is one-page web app, using only Javascript and HTML5
  * updates is located at `docs/quad_player`
  * Online version :
    * http://rufaswan.github.io/Web2D_Games/docs/quad_player/quad-frame.html
    * http://rufaswan.github.io/Web2D_Games/docs/quad_player/quad-anim.html

## Game Status

* on-hold for a rewrite
