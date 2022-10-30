## Tools (PHP)

* For everything in `tools` folder, just run the PHP script directly with the files. No web server required.
  * `php.exe  psxtools/img_clut2png.php  0000.rgba  0001.rgba...`
* Windows's cmd.exe 8191 character limit workaround
  * cmd.exe is very limited, and won't work when `*.rgba` is over the character limit
  * made a simple PHP script to generate a command on each file
  * NORMAL : `php.exe  psxtools/img_clut2png.php  *.rgba`
  * LOOP : `php.exe  psxtools/tsr_cmdloop.php  rgba  "php.exe psxtools/img_clut2png.php"`

