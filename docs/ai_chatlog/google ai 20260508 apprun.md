Packaging an old 32-bit application into a portable format is still very possible, though support for 32-bit (i386) is slowly being phased out by major runtimes.

Here are your best options for creating a self-contained 32-bit package:

## 1. AppImage (The Standard Choice)

AppImage remains the most straightforward way to bundle 32-bit apps because it doesn't require a global runtime. [1, 2, 3]

* How to Build: You can manually create an AppDir structure containing your 32-bit binary, its dependencies, an icon, and a .desktop file.
* Tools: Use [appimagetool](https://github.com/Appimage/AppImageKit/releases) to convert the AppDir into the final .AppImage file. While most modern systems are 64-bit, 32-bit versions of appimagetool are still available via the [Open Build Service](https://discourse.appimage.org/t/32-bit-appimages/296).
* Pro Tip: If your app is already in a .deb or .tar.gz format, use [pkg2appimage](https://github.com/AppImageCommunity/awesome-appimage) with a YAML recipe to automate the conversion. [4, 5, 6, 7, 8]

## 2. Flatpak (Using Compatibility Extensions)

Flatpak is more complex for 32-bit apps because the primary runtimes (like GNOME and KDE) have largely dropped 32-bit support. [9, 10]

* Compatibility: To run 32-bit code, you must include the org.freedesktop.Platform.Compat.i386 extension in your manifest.
* Limitation: As of late 2025, the GNOME Flatpak Runtime has officially eliminated its 32-bit compatibility extension, making it harder to bundle apps that require modern 32-bit GTK or WebKit. [9, 11, 12]

## 3. Specialized Repositories

If you are looking for existing 32-bit AppImages or want to see how they are structured, community-driven databases exist: [13]

* [32-bit AppImage Database](https://github.com/ivan-hc/32-bit-AppImage-packages-database): A repository specifically for 32-bit architecture AppImages.
* [AppMan](https://github.com/ivan-hc/AM): A manager specifically designed to handle these portable 32-bit packages locally without root access. [14, 15, 16]

## Summary of Differences

| Feature [5, 12, 17, 18, 19] | AppImage | Flatpak |
|---|---|---|
| Setup | Single file; no installation | Requires flatpak daemon |
| Dependencies | All bundled inside the file | Uses shared external runtimes |
| 32-bit Support | Strong (manual bundling) | Waning (extensions being retired) |

Do you have the source code for this application, or are you working with an existing binary file?

[1] [https://docs.conan.io](https://docs.conan.io/1/integrations/deployment/appimage.html)
[2] [https://dmnfarrell.github.io](https://dmnfarrell.github.io/software/linux-packaging)
[3] [https://cstan.io](https://cstan.io/en/post/2021/12/appimage-flatpak-und-snap-im-vergleich/)
[4] [https://discourse.appimage.org](https://discourse.appimage.org/t/32-bit-appimages/296)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=Wy63jwjpNg4&t=15)
[6] [https://www.youtube.com](https://www.youtube.com/watch?v=Wy63jwjpNg4&t=15)
[7] [https://docs.appimage.org](https://docs.appimage.org/packaging-guide/manual.html)
[8] [https://github.com](https://github.com/AppImageCommunity/awesome-appimage)
[9] [https://blogs.gnome.org](https://blogs.gnome.org/alatiera/2025/10/13/flatpak-32bit/)
[10] [https://prohoster.info](https://prohoster.info/en/blog/novosti-interneta/v-gnome-flatpak-runtime-prekrashhena-podderzhka-32-razryadnyh-prilozhenij)
[11] [https://docs.flatpak.org](https://docs.flatpak.org/en/latest/multiarch.html)
[12] [https://www.phoronix.com](https://www.phoronix.com/news/GNOME-Flatpak-Drops-32-bit)
[13] [https://docs.appimage.org](https://docs.appimage.org/packaging-guide/distribution.html)
[14] [https://github.com](https://github.com/ivan-hc/32-bit-AppImage-packages-database)
[15] [https://github.com](https://github.com/ivan-hc/32-bit-AppImage-packages-database)
[16] [https://itsfoss.com](https://itsfoss.com/appimage-tools/)
[17] [https://www.youtube.com](https://www.youtube.com/watch?v=FemtHe8w7sA&t=155)
[18] [https://www.youtube.com](https://www.youtube.com/watch?v=IG2wTCacEtQ&t=25)
[19] [https://discourse.appimage.org](https://discourse.appimage.org/t/32-bit-appimages/296)

# ---

The fusermount error usually happens because FUSE (Filesystem in Userspace) isn't properly set up or is restricted by your system security. This is common on modern Ubuntu (22.04+), inside Docker containers, or on ChromeOS. [1, 2, 3, 4, 5, 6]

Here is how to fix it, ordered from easiest to most thorough:

## 1. The Quickest Fix (No Install Needed)

Most AppImages have a built-in "extract and run" mode that bypasses FUSE entirely. Run your app with this flag: [6]

```
./your-app.AppImage --appimage-extract-and-run
```

Note: This will be slightly slower to start as it extracts files to a temporary folder every time. [7, 8]

## 2. Install/Fix FUSE Dependencies [4, 9]

If you are on a recent version of Ubuntu or Debian, the necessary legacy FUSE library might be missing: [2, 3, 10, 11]

* Ubuntu 22.04/24.04+:

```
sudo apt update
sudo apt install libfuse2
```

(Note: Do not install the package named fuse on 22.04+ unless you know what you're doing, as it can conflict with the system's default fuse3.)

* Arch Linux:

```
sudo pacman -S fuse2
```
[3, 11]

## 3. Check Permissions (If in Docker or Restricted Environments)

If you are getting "Operation not permitted" inside a container or specialized environment, the system is likely blocking the mount syscall. [6, 12, 13]

* Docker: You must run the container with --device /dev/fuse --cap-add SYS_ADMIN --security-opt apparmor:unconfined.
* Setuid Check: Ensure fusermount has the correct permissions. It typically needs to be owned by root with the "setuid" bit set:

```
ls -l $(which fusermount)# It should look like: -rwsr-xr-x 1 root root ...
```
[2, 12, 13, 14, 15]

## 4. Extract Manually (Permanent Solution)

If you can't get FUSE to work at all, you can "unpack" the AppImage and run it like a normal folder: [6, 9, 16, 17]

   1. Extract it: ./your-app.AppImage --appimage-extract.
   2. Enter the new folder: cd squashfs-root.
   3. Run the launcher: ./AppRun. [2, 9, 16]

Are you running this on a standard desktop Linux (like Ubuntu/Mint) or inside a container/ChromeOS?** [9]

[1] [https://www.reddit.com](https://www.reddit.com/r/chromeos/comments/o0xyeq/natively_run_appimages_in_chrome_os/)
[2] [https://discourse.ubuntu.com](https://discourse.ubuntu.com/t/last-plucky-updates-broke-fusermount-cannot-use-appimage-apps-anymore/55608)
[3] [https://github.com](https://github.com/appimage/appimagekit/wiki/fuse)
[4] [https://docs.appimage.org](https://docs.appimage.org/user-guide/troubleshooting/fuse.html)
[5] [https://discourse.ubuntu.com](https://discourse.ubuntu.com/t/last-plucky-updates-broke-fusermount-cannot-use-appimage-apps-anymore/55608)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/71734470/can-not-run-appimages-inside-rootless-docker-container)
[7] [https://github.com](https://github.com/orgs/AppImage/discussions/1368)
[8] [https://github.com](https://github.com/AppImage/AppImageKit/issues/841)
[9] [https://docs.appimage.org](https://docs.appimage.org/user-guide/run-appimages.html)
[10] [https://github.com](https://github.com/AppImage/AppImageKit/issues/1304)
[11] [https://github.com](https://github.com/appimage/appimagekit/wiki/fuse)
[12] [https://forums.docker.com](https://forums.docker.com/t/dockerizing-chrome-security-questions/45614)
[13] [https://github.com](https://github.com/containers/podman/issues/14402)
[14] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/625206/cant-run-fuse-inside-podman-fusermount-mount-failed-operation-not-permitted)
[15] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/625206/cant-run-fuse-inside-podman-fusermount-mount-failed-operation-not-permitted)
[16] [https://discourse.appimage.org](https://discourse.appimage.org/t/how-to-extract-an-appimage-file/189)
[17] [https://roaldnefs.com](https://roaldnefs.com/posts/2024/12/how-to-extract-an-appimage-and-add-it-to-the-ubuntu-sidebar/)

# ---

No, you do not need to run everything as root. In fact, running an AppImage with sudo is generally discouraged because it can mess up your user permissions and is a security risk. [1, 2, 3]

The "Operation not permitted" error happens because your user doesn't have the specific permission to use the FUSE (Filesystem in Userspace) system, which AppImage uses to mount itself as a virtual drive. [4, 5, 6, 7]

Here is how to solve it without being root all the time:

## 1. Fix the permission once (Requires Sudo) [8]

You only need sudo for this one-time setup to allow your user to use FUSE. [6]

* Add yourself to the fuse group:

```
sudo usermod -a -G fuse $(whoami)
```

Note: You must log out and log back in for this to take effect.

* Fix the fusermount binary permissions (if it's broken):

```
sudo chmod u+s $(which fusermount)
```

This sets the "SUID bit," which lets the program run with the necessary privileges even when you start it as a normal user. [9, 10, 11, 12]

## 2. The "Never Root" Alternative [3]

If you cannot or do not want to use sudo at all, you can bypass the mounting process entirely: [3]

* Run without mounting:

```
./your-app.AppImage --appimage-extract-and-run
```

This tells the AppImage to extract its contents to a temporary folder in your home directory and run from there, avoiding the need for FUSE or root permissions. [13, 14, 15]

## Summary: When to use Sudo vs. User

| Action [16] | Run as... | Why? |
|---|---|---|
| Installing libfuse2 | sudo | System-level library installation. |
| Fixing permissions | sudo | Modifying system binaries/groups. |
| Running the app | User | Running as root can create files in your home folder that you won't be able to delete later. |

Are you using a shared computer (like at a school or work) where you don't have sudo access?

[1] [https://forums.linuxmint.com](https://forums.linuxmint.com/viewtopic.php?t=386175)
[2] [https://www.reddit.com](https://www.reddit.com/r/linuxmint/comments/dhqt0p/is_there_a_way_to_set_an_appimage_to_always_run/)
[3] [https://discourse.appimage.org](https://discourse.appimage.org/t/nosuid-fuse-mount-option-deactivates-capabilities/1652)
[4] [https://docs.appimage.org](https://docs.appimage.org/user-guide/troubleshooting/fuse.html)
[5] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/FUSE/8f451e909dbfbb6fff3dff6076fb13202de1ac7b)
[6] [https://discourse.appimage.org](https://discourse.appimage.org/t/nosuid-fuse-mount-option-deactivates-capabilities/1652)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=WJs8VLuu8Mk&t=1)
[8] [https://www.reddit.com](https://www.reddit.com/r/pop_os/comments/8sy4wc/cant_run_appimage_programs_unless_i_sudo_nautilus/)
[9] [https://docs.appimage.org](https://docs.appimage.org/user-guide/troubleshooting/fuse.html)
[10] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/FUSE/cdc181e202b93ce061ee199a033ed4969bfbfe9e)
[11] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/FUSE/8f451e909dbfbb6fff3dff6076fb13202de1ac7b)
[12] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/625206/cant-run-fuse-inside-podman-fusermount-mount-failed-operation-not-permitted)
[13] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/FUSE/1d229210e640a755cad70c1f91db34ba79d6c498)
[14] [https://docs.appimage.org](https://docs.appimage.org/user-guide/troubleshooting/fuse.html)
[15] [https://github.com](https://github.com/LizardByte/Sunshine/issues/3252)
[16] [https://docs.appimage.org](https://docs.appimage.org/user-guide/troubleshooting/fuse.html)

# ---

The "command not found" for usermod usually means /usr/sbin isn't in your PATH (common on Debian if you used su instead of su -) or you are on a non-standard distro.

Since fusermount is 755, it’s missing the SUID bit. Without that bit, a normal user cannot mount the AppImage, which is exactly why you're getting "Operation not permitted."

Here is the fix:

## 1. Fix the permissions (The right way)

You need to use sudo or switch to root one last time to set the SUID bit:

```
sudo chmod 4755 $(which fusermount)
```

Check it again with ls -l $(which fusermount). It should now show an s instead of an x:

```
-rwsr-xr-x
```

## 2. If you can't use Sudo/Root

If you are stuck and can't change system permissions, stop trying to "run" the AppImage directly. Use the extraction method:

```
./your-app.AppImage --appimage-extract
cd squashfs-root
./AppRun
```

This bypasses fusermount entirely and works for any user.

## 3. Missing usermod?

If you really need usermod, try calling it by its full path:

```
/usr/sbin/usermod -a -G fuse $(whoami)
```

Which Linux distribution are you using? (e.g., Debian, Alpine, NixOS?) This helps narrow down why your paths and permissions are non-standard.

# ---

Since AppImage and FUSE are causing permission headaches, here are three solid "no-root" alternatives for packaging or running 32-bit apps:

## 1. Static-X (Best for Single Binaries)

If your app is mostly a single binary or has just a few dependencies, StaticX is excellent. It bundles your 32-bit binary and all its .so libraries into a single, truly static executable.

* Why use it: It doesn't use FUSE or mounting. It's a self-extracting executable that runs in user space.
* How to do it:
1. Install it via pip: pip install staticx
   2. Run it against your app: staticx ./your-32bit-app ./static-app
   3. The resulting static-app file will run on almost any Linux machine without needing extra libraries. [1, 2]

## 2. JuNest (The "Lightweight Container" Way)

JuNest (Jailed User Nest) allows you to create a tiny Arch Linux-based 32-bit environment that runs entirely as a normal user.

* Why use it: It uses proot instead of FUSE, so it doesn't need "Operation Permitted" or root access to "mount" things.
* Pros: You can use pacman inside the nest to install any 32-bit libraries your old app needs, then bundle the whole folder as a "portable" directory. [3, 4]

## 3. Flatpak (The "Proper" Packaging Way)

If you want a standard format that users can install, Flatpak handles 32-bit apps using compatibility layers. [5]

* Why use it: It's a standard and doesn't rely on FUSE for the end-user (the Flatpak daemon handles the heavy lifting).
* Requirement: You need to include the org.freedesktop.Platform.Compat.i386 runtime in your manifest to provide the 32-bit environment on a 64-bit host. [5, 6]

## 4. The "Old School" Portable Folder

Sometimes the simplest way is to just bundle everything in a folder with a launcher script:

   1. Put your app in a folder.
   2. Copy all required 32-bit .so files into a libs/ subfolder.
   3. Create a script named run.sh:
```
   #!/bin/bash
   HERE=$(dirname "$(readlink -f "$0")")
   export LD_LIBRARY_PATH="$HERE/libs:$LD_LIBRARY_PATH"
   exec "$HERE/your-app-binary" "$@"
```
   4. Zip the folder and send it. No mounting or special permissions required. [7]

Which sounds better for your workflow: creating a single static file (Static-X) or a portable folder (Old School)?

Check out these tools for further research:

* StaticX GitHub for creating static binaries.
* JuNest Guide for user-space containers.
* Flatpak Documentation for official universal packaging. [5]


[1] [https://www.reddit.com](https://www.reddit.com/r/cpp/comments/11o0nbi/best_way_to_distribute_application_with_many/)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/4962662/creating-a-generic-binary-in-linux-for-all-x86-machines)
[3] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/425422/how-to-run-old-32-bit-linux-software-in-a-container-inside-a-modern-64-bit-distr)
[4] [https://www.debugpoint.com](https://www.debugpoint.com/32-bit-linux-distributions/)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=18FvP8yY9rM)
[6] [https://www.zdnet.com](https://www.zdnet.com/article/snap-vs-flatpak-how-to-decide-which-linux-package-manager-is-right-for-you/)
[7] [https://superuser.com](https://superuser.com/questions/164767/distributing-binary-with-dependencies-in-linux)

# ---

Yes, you can absolutely use a .desktop file to add icons and a cleaner look to your run.sh portable folder. Regarding Static-X, there is a major limitation you should know for your 32-bit project. [1]

## 1. Static-X and the libc Requirement

Static-X does not change the libc requirement of your original binary. It simply bundles the existing libc (and other dependencies) into a self-extracting archive. [2]

* Architecture Support: A critical catch—Static-X currently officially supports only 64-bit (x86_64) binaries. It is not designed to package 32-bit apps, so it likely won't work for your specific case.
* Version Compatibility: Even if it did work, Static-X cannot make a binary built for a newer glibc run on a system with an older one. It only helps ensure that a binary built on an older system has all its required libraries when moved to a newer system. [3, 4, 5, 6, 7, 8]

## 2. The "run.sh" + ".desktop" Approach

This is the most reliable "no-root" method for 32-bit apps. You can create a professional-looking launcher that points to your script.

Step 1: Organize your folder

```
YourAppFolder/
├── run.sh          (Your launcher script)
├── app_binary      (The 32-bit executable)
├── libs/           (All required .so files)
└── icon.png        (Your app icon)
```

Step 2: Create the .desktop file

Create a file named MyApp.desktop inside that same folder (or on your actual desktop) with the following content:

```
[Desktop Entry]
Type=Application
Name=My Old 32-bit App
Comment=Run my old app without root
# Use absolute paths here if you move the folder
Exec=/path/to/YourAppFolder/run.sh
Icon=/path/to/YourAppFolder/icon.png
Terminal=false
Categories=Utility;
```

Note: Most Linux desktops (GNOME, KDE) require absolute paths in the Exec and Icon lines to work correctly. [9]

Step 3: Make it clickable [10]

   1. Right-click the .desktop file -> Properties.
   2. Go to the Permissions tab and check "Allow executing file as program".
   3. On some systems (like Ubuntu), you may need to right-click it on your desktop and select "Allow Launching". [10, 11]

## Why this is better for you:

* No FUSE/Mounting: It bypasses the "Operation not permitted" errors entirely.
* 32-bit Friendly: It doesn't care about the architecture; as long as your run.sh points to the right libraries in the libs/ folder, it will work. [10]

Do you need a hand figuring out which libraries (.so files) to copy into that libs/ folder?**

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/17622602/linux-environment-to-build-32-and-64-bit-application-c)
[2] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/2717/running-a-statically-linked-binary-with-a-different-glibc)
[3] [https://developers.redhat.com](https://developers.redhat.com/blog/2019/08/01/how-the-gnu-c-library-handles-backward-compatibility)
[4] [https://staticx.readthedocs.io](https://staticx.readthedocs.io/en/latest/installation.html)
[5] [https://pypi.org](https://pypi.org/project/staticx/0.13.3/)
[6] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/505868/static-linking-libc-possible-or-not-recommended-or-not)
[7] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=43551934)
[8] [https://thecharlatan.ch](https://thecharlatan.ch/GLIBC-Back-Compat/)
[9] [https://www.reddit.com](https://www.reddit.com/r/linuxquestions/comments/jvkls4/desktop_file_help_iconsrunning_in_terminal/)
[10] [https://askubuntu.com](https://askubuntu.com/questions/1347857/how-can-i-run-a-sh-to-with-an-icon-desktop-in-ubuntu-20-04)
[11] [https://www.youtube.com](https://www.youtube.com/watch?v=bw15WIqpX8M&t=8)

# ---

Yes, an AppRun script is conceptually the same thing as a run.sh. In fact, if you were to "unpack" an AppImage, the first thing you would see is an AppRun file at the root of the folder. [1, 2, 3, 4, 5]

The main differences are in how they are triggered and how they handle the environment:

## 1. The Entry Point

* AppRun: This is the mandatory name for the entry point inside an AppImage or an AppDir. When you run an AppImage, the system mounts the image and automatically executes the file named AppRun.
* run.sh: This is just a common name for a custom launcher script. It isn't a standard, so the system doesn't know to run it automatically unless you tell it to (e.g., via a .desktop file). [4, 6, 7, 8, 9]

## 2. Automatic Path Setup

A standard AppRun (especially the precompiled binary version provided by AppImageKit) does extra work for you: [10]

* LD_LIBRARY_PATH: It automatically sets this to include the usr/lib and lib folders inside the AppImage so your 32-bit libraries are found.
* Working Directory: It often changes the directory (cd) to the location of your app's files so internal relative paths don't break. [11, 12, 13]

## 3. Can you use your own?

Absolutely. You don't have to use the official "AppRun" binary. You can delete it and replace it with a shell script—just make sure you name it exactly AppRun and make it executable (chmod +x). [1, 2, 5, 11, 14]

Your custom 32-bit AppRun script would look like this:

```
#!/bin/sh
HERE=$(dirname "$(readlink -f "$0")")
export LD_LIBRARY_PATH="$HERE/usr/lib:$HERE/lib:$LD_LIBRARY_PATH"
exec "$HERE/usr/bin/your-32bit-binary" "$@"
```

## The "No-Root" Takeaway

Since you want to avoid the fusermount and root permission issues, you should:

   1. Keep it as a folder (AppDir) rather than a single .AppImage file.
   2. Use a .desktop file at the root of that folder to give it an icon and a name.
   3. Point that .desktop file's Exec= line to your AppRun (or run.sh) script. [4, 5, 8]

This gives you the "portable" experience of an AppImage without the FUSE mounting requirement that's blocking you. [5]

Do you have all the 32-bit libraries ready to put into that lib/ folder, or should we find a way to list what's missing?

Further Reading:

* [AppDir Structure](https://github.com/AppImage/AppImageKit/wiki/AppDir) for a standard layout.
* [Manual Packaging Guide](https://docs.appimage.org/packaging-guide/manual.html) for creating these scripts. [4]


[1] [https://discourse.appimage.org](https://discourse.appimage.org/t/why-use-apprun/317)
[2] [https://discourse.appimage.org](https://discourse.appimage.org/t/why-use-apprun/317)
[3] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/AppDir)
[4] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/AppDir)
[5] [https://blog.jamesreed.dev](https://blog.jamesreed.dev/packaging-apps-for-linux-with-appimage)
[6] [https://stackoverflow.com](https://stackoverflow.com/questions/38772874/apprun-file-format)
[7] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/AppDir/d3c2bfea097a4623161a65063b2be687c6a23292)
[8] [https://www.booleanworld.com](https://www.booleanworld.com/creating-linux-apps-run-anywhere-appimage/)
[9] [https://blog.jamesreed.dev](https://blog.jamesreed.dev/packaging-apps-for-linux-with-appimage)
[10] [https://stackoverflow.com](https://stackoverflow.com/questions/38772874/apprun-file-format)
[11] [https://stackoverflow.com](https://stackoverflow.com/questions/70823159/appimage-problems)
[12] [https://github.com](https://github.com/AppImage/AppImageKit/wiki/Creating-AppImages/4d08805b51b30a37a0fd008705fd54fcba49b960)
[13] [https://docs.appimage.org](https://docs.appimage.org/introduction/software-overview.html)
[14] [https://github.com](https://github.com/ssh-mitm/appimage)

# ---

For a portable 32-bit app, it is generally better to move everything into a single lib/ directory and simplify your LD_LIBRARY_PATH. [1]

While the i386-linux-gnu structure is the "Multiarch" standard for modern Debian/Ubuntu systems, it adds unnecessary complexity to a portable package where you are already isolating the environment. [2, 3, 4]

## Why you should use a single lib/ folder:

* Simpler Scripting: You only need to add one path to your LD_LIBRARY_PATH instead of managing deep subdirectories.
* Wider Compatibility: Some older 32-bit systems or non-Debian distributions (like Arch or Fedora) may not look for the i386-linux-gnu path by default, but almost all will respect a direct link to a folder named lib.
* Easier Maintenance: It’s easier to see at a glance if you have all your .so files in one place without digging through multi-level folders. [2, 5, 6, 7, 8]

## How to set up your AppRun (or run.sh):

Once you've moved the files to a single lib/ folder at the root of your app directory, your launcher script should look like this:

```
#!/bin/sh# Find the absolute path to where this script is located
HERE=$(dirname "$(readlink -f "$0")")
# Set the library path to your local lib folder first
export LD_LIBRARY_PATH="$HERE/lib:$LD_LIBRARY_PATH"
# Run your 32-bit binary
exec "$HERE/your-32bit-binary" "$@"
```

## Important Tips for 32-bit Portability:

   1. Check for missing links: Use the command ldd your-32bit-binary while inside your portable folder to ensure all listed libraries point to your local lib/ folder rather than system paths.
   2. The Dynamic Linker: If your app still won't run on different systems, you may also need to bundle the 32-bit dynamic linker (ld-linux.so.2) and call it directly in your script. [6, 9, 10]

Are there any specific error messages appearing when you try to run your 32-bit binary now?

[1] [https://www.reddit.com](https://www.reddit.com/r/linux_gaming/comments/dh7xhp/to_kill_of_32_bit_libraries_or_not_to_kill_of_32/)
[2] [https://askubuntu.com](https://askubuntu.com/questions/555728/packaging-usr-lib-vs-usr-lib-linux-gnu)
[3] [https://www.reddit.com](https://www.reddit.com/r/linux/comments/1nc40h2/why_linux_has_a_scattered_file_system_a_deep_dive/)
[4] [https://askubuntu.com](https://askubuntu.com/questions/52617/what-is-usr-lib-i386-linux-gnu-for)
[5] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/367600/what-is-the-order-that-linuxs-dynamic-linker-searches-paths-in)
[6] [https://brioche.dev](https://brioche.dev/blog/portable-dynamically-linked-packages-on-linux/)
[7] [https://forums.linuxmint.com](https://forums.linuxmint.com/viewtopic.php?t=279809)
[8] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/586792/difference-between-usr-lib-and-usr-lib-x86-64-linux-gnu-folders)
[9] [https://askubuntu.com](https://askubuntu.com/questions/187286/setting-ld-library-path-before-running-an-app-from-a-terminal-has-no-effect)
[10] [https://discourse.appimage.org](https://discourse.appimage.org/t/create-an-appimage-with-latest-libs-which-works-everywhere/2582)

# ---

To pack an old 32-bit Python 2.7 app as a portable folder (AppDir style), you should bundle the Python interpreter, its libraries, and your script into a single directory that doesn't rely on system-wide installations. [1, 2, 3]

Since Python 2.7 is legacy, you shouldn't rely on modern packaging tools that might have dropped support for it. Instead, follow this manual "portable folder" structure: [4, 5]

## 1. Structure Your Portable Folder

Create a folder for your app (e.g., My32BitApp/) with the following layout:

```
My32BitApp/
├── AppRun (or run.sh)
├── python/             <-- The Python 2.7 interpreter folder
│   ├── bin/python
│   └── lib/python2.7/  <-- Standard libraries and site-packages
├── lib/                <-- All 32-bit .so dependencies
├── main.py             <-- Your main Python script
└── icon.png
```

## 2. Create the Launcher Script (AppRun)

This script ensures the app uses its internal Python and libraries instead of the system ones. [1, 6]

```
#!/bin/bash
HERE=$(dirname "$(readlink -f "$0")")
# 1. Point to your local 32-bit libraries
export LD_LIBRARY_PATH="$HERE/lib:$HERE/python/lib:$LD_LIBRARY_PATH"
# 2. Tell Python where to find its own modules
export PYTHONPATH="$HERE/python/lib/python2.7:$HERE/python/lib/python2.7/site-packages"
# 3. Run your script using the bundled 32-bit Python interpreter
exec "$HERE/python/bin/python" "$HERE/main.py" "$@"
```

## 3. How to get the 32-bit Python 2.7

* Existing Installation: If you have it installed on an old 32-bit system, copy the /usr/bin/python2.7 binary and the /usr/lib/python2.7 folder into your python/ directory.
* Pre-built Binaries: You can find archived 32-bit Linux binaries for Python 2.7 on [SourceForge](https://sourceforge.net/directory/?q=python%202.7%2C9%2032%20bit) or via [python-appimage releases](https://python-appimage.readthedocs.io/en/latest/apps/).
* PyPy2: Consider using [PyPy2.7](https://www.daniweb.com/programming/software-development/threads/497127/portable-python-2-7-x86-and-x64-win-mac-linux-interpreters-needed), which often comes as a more "relocatable" 32-bit binary that is easier to bundle. [3, 7, 8, 9, 10, 11]

## 4. Create the .desktop File

To make it launchable with an icon, place this MyApp.desktop file in the same folder: [12]

```
[Desktop Entry]
Type=Application
Name=My Python App
Exec=bash -c "$(dirname "%k")/AppRun"
Icon=icon.png
Terminal=false
```

Note: The $(dirname "%k") trick allows the .desktop file to find the AppRun script relatively, though some file managers may still require absolute paths.

Would you like help identifying the exact 32-bit libraries your Python binary needs to run on other systems?

[1] [https://dev.to](https://dev.to/treehouse/portable-python-bundles-on-windows-41ac)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=z8kgu74jERM&t=1)
[3] [https://www.daniweb.com](https://www.daniweb.com/programming/software-development/threads/497127/portable-python-2-7-x86-and-x64-win-mac-linux-interpreters-needed)
[4] [https://www.daniweb.com](https://www.daniweb.com/programming/software-development/threads/497127/portable-python-2-7-x86-and-x64-win-mac-linux-interpreters-needed)
[5] [https://discourse.appimage.org](https://discourse.appimage.org/t/creating-aplication-appimage-based-on-python2/1851)
[6] [https://github.com](https://github.com/wavetossed/pybuild)
[7] [https://www.daniweb.com](https://www.daniweb.com/programming/software-development/threads/497127/portable-python-2-7-x86-and-x64-win-mac-linux-interpreters-needed)
[8] [https://stackoverflow.com](https://stackoverflow.com/questions/11249901/creating-a-portable-python-local-install-for-linux)
[9] [https://www.ibm.com](https://www.ibm.com/docs/en/oala/1.3.8?topic=prerequisites-installing-python-2718)
[10] [https://appimage.github.io](https://appimage.github.io/python2/)
[11] [https://sourceforge.net](https://sourceforge.net/directory/?q=python%202.7%2C9%2032%20bit)
[12] [https://www.youtube.com](https://www.youtube.com/watch?v=9CTmC5Y7QeM&t=127)

# ---

For a portable Python application, you typically need to set PYTHONHOME in addition to PYTHONPATH to ensure the interpreter looks for its standard libraries in your portable folder rather than the host system's global paths. [1, 2, 3]

Setting PYTHONHOME tells the interpreter exactly where its core "home" (containing lib/python2.7) is located. Without it, a bundled 32-bit Python might try to load 64-bit standard libraries from a modern host system, causing a crash. [4, 5, 6, 7]

## Essential Variables for your AppRun script:

```
#!/bin/bash
HERE=$(dirname "$(readlink -f "$0")")
# 1. PYTHONHOME: Redirects the Standard Library search path# Format: PYTHONHOME=<prefix>:<exec_prefix>
export PYTHONHOME="$HERE/python"
# 2. PYTHONPATH: Adds your specific app modules and site-packages
export PYTHONPATH="$HERE/python/lib/python2.7/site-packages:$HERE"
# 3. LD_LIBRARY_PATH: Ensures 32-bit system dependencies (.so files) are found
export LD_LIBRARY_PATH="$HERE/lib:$HERE/python/lib:$LD_LIBRARY_PATH"
# 4. PYTHONNOUSERSITE: Prevents Python from loading packages from ~/.local
export PYTHONNOUSERSITE=1

exec "$HERE/python/bin/python" "$HERE/main.py" "$@"
```

## Why these matter:

* PYTHONHOME: Replaces the default installation paths (usually /usr/local) so Python finds its own standard modules (like os or sys) inside your folder.
* PYTHONNOUSERSITE: Critical for portability. It prevents the app from accidentally "leaking" in packages from the user's home directory (~/.local/lib/python2.7), which might be the wrong architecture or version.
* PYTHONOPTIMIZE (Optional): If you want to run faster by skipping assertions and docstrings, set this to 1.
* PYTHONDONTWRITEBYTECODE (Optional): Set to 1 if you don't want your portable folder cluttered with .pyc files every time it runs. [3, 5, 6, 8, 9]

## Pro-Tip: The -I or -S Flags

Instead of setting every variable, you can often use command-line flags on the interpreter itself. For example, python -S skips the site module entirely, giving you manual control over your environment via PYTHONPATH. [6]

Do you have compiled C-extensions (like numpy or cryptography) in your Python 2.7 app that might need extra 32-bit .so libraries?

Explore these resources for deeper configuration:

* Python 2.7 Environment Variable Docs for a full list.
* AppImage Python Wiki for specific bundling tips.


[1] [https://forums.raspberrypi.com](https://forums.raspberrypi.com/viewtopic.php?t=150220)
[2] [https://www.ibm.com](https://www.ibm.com/docs/en/streams/4.3.0?topic=python-pythonhome)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/7850908/what-exactly-should-be-set-in-pythonpath)
[4] [https://stackoverflow.com](https://stackoverflow.com/questions/32813703/how-to-set-pythonpath-in-windows)
[5] [https://manpages.debian.org](https://manpages.debian.org/experimental/python2.7-minimal/python2.7.1)
[6] [https://python-appimage.readthedocs.io](https://python-appimage.readthedocs.io/en/latest/apps/)
[7] [https://stackoverflow.com](https://stackoverflow.com/questions/9552898/python-2-x-multiple-version-issues-regarding-pythonpath)
[8] https://python-appimage.readthedocs.io
[9] [https://www.daniweb.com](https://www.daniweb.com/programming/software-development/threads/497127/portable-python-2-7-x86-and-x64-win-mac-linux-interpreters-needed)

# ---

You’re right—it doesn't have to be main.py. I just used that as a placeholder name for "whatever your script is called."

If your goal is to make a portable Python environment where you can run any script or command-line argument, then exec python "$@" is exactly what you want.

## How to do it:

Change the last line of your AppRun script to this:

```
exec "$HERE/python/bin/python" "$@"
```

## Why this is better for a "tool" or "environment":

* Flexibility: You can now use your portable folder like a regular python command:
```
./AppRun my_script.py
```
* REPL: If you run ./AppRun without any arguments, it will open the interactive Python 2.7 shell.
* Pip/Modules: You can run modules directly:
```
./AppRun -m SimpleHTTPServer 8000
```

## One quick warning on $@ vs "$@":

Always use "$@" (with quotes).

* $@ can break if your file paths have spaces in them.
* "$@" ensures that every argument you pass to the script stays exactly as it was intended.

## Summary of your "Portable Python 2.7" Shell:

```
#!/bin/bash
HERE=$(dirname "$(readlink -f "$0")")

export PYTHONHOME="$HERE/python"
export PYTHONPATH="$HERE/python/lib/python2.7/site-packages:$HERE"
export LD_LIBRARY_PATH="$HERE/lib:$HERE/python/lib:$LD_LIBRARY_PATH"
export PYTHONNOUSERSITE=1
# This passes everything you type after ./AppRun directly to Python
exec "$HERE/python/bin/python" "$@"
```

Does this Python 2.7 environment need to talk to any external hardware or USB devices that might need 32-bit drivers?

# ---

Mounting behavior on Linux depends heavily on whether you are using the command line (CLI) or a graphical interface (GUI). [1]

## 1. .ISO (The Standard for Images)

* CLI: The mount command is a standard tool across all Linux distributions. However, mounting an ISO usually requires root/sudo privileges because it involves a "loop device".
* GUI: Most modern desktop environments (GNOME, KDE) include "Disk Image Mounter". This allows you to mount ISOs as a normal user through a virtual filesystem (GVfs/KIO), avoiding the root requirement. [2, 3, 4, 5, 6, 7]

## 2. .ZIP (An Archive, Not a Volume) [8, 9]

* Mounting vs. Extracting: Strictly speaking, Linux does not "mount" a .zip file as a standard system-level drive. ZIP is an archive format meant for extraction.
* The GUI Workaround: Many file managers (like GNOME's Nautilus) offer an "Archive Mounter" option. This uses FUSE to make the ZIP look like a drive, but this is a desktop convenience, not a universal Linux kernel standard.
* CLI: You normally use unzip. To truly "mount" a ZIP on the command line, you would need specialized FUSE tools like fuse-zip, which are not installed by default on most distros. [10, 11, 12, 13, 14]

## 3. .SFS (SquashFS)

* Status: This is a native Linux standard for compressed, read-only filesystems. It is the core technology behind AppImage, Snap, and Live CDs.
* Standardization: While the kernel supports SquashFS almost universally, the .sfs extension itself is most commonly associated with Puppy Linux.
* Mounting: Like ISOs, mounting a SquashFS file via the CLI typically requires sudo mount -t squashfs. [15, 16, 17, 18, 19, 20]

## Summary Comparison

| Format [21, 22, 23, 24, 25] | Native Kernel Mounting? | Requires Root (CLI)? | Standard GUI Support? |
|---|---|---|---|
| .iso | Yes (ISO 9660) | Yes | Yes (Mount) |
| .zip | No (it's an archive) | N/A (usually extract) | Yes (Mount/Browse) |
| .sfs | Yes (SquashFS) | Yes | Varies (Distro dependent) |

If your goal is to distribute your 32-bit app, a .zip or .tar.gz is the most "portable" since it never requires special mount permissions to access the files—the user just extracts and runs.

Would you like to see how to create a self-extracting script that avoids the zip/mount issue entirely?

[1] [https://ostechnix.com](https://ostechnix.com/mount-a-drive-permanently-in-linux-using-fstab/)
[2] [https://itsfoss.community](https://itsfoss.community/t/is-there-a-gui-tool-to-see-the-content-of-a-iso-file/15335)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=2Qs3d9CGhZs)
[4] [https://www.scribd.com](https://www.scribd.com/doc/70779402/HowTo-Mount-ISO-File)
[5] [https://www.cyberciti.biz](https://www.cyberciti.biz/tips/how-to-mount-iso-image-under-linux.html)
[6] [https://roman-academy.medium.com](https://roman-academy.medium.com/how-to-mount-and-unmount-an-iso-image-in-linux-ca2b7710965f)
[7] [https://linuxconfig.org](https://linuxconfig.org/how-to-mount-iso-image-on-linux)
[8] [https://copyrightservice.co.uk](https://copyrightservice.co.uk/reg/creating-zip-files)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=xL8_r76Ouxw&t=287)
[10] [https://www.youtube.com](https://www.youtube.com/watch?v=xqcHiuQK9lY)
[11] [https://www.interserver.net](https://www.interserver.net/tips/kb/how-to-zip-and-unzip-in-linux-examples/)
[12] [https://askubuntu.com](https://askubuntu.com/questions/94649/how-to-mount-a-zip-file-as-a-file-system)
[13] [https://linuxize.com](https://linuxize.com/post/how-to-zip-files-and-directories-in-linux/)
[14] [https://www.atlantic.net](https://www.atlantic.net/dedicated-server-hosting/how-to-use-zip-and-unzip-commands-in-linux/)
[15] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/680272/why-is-the-base-system-of-live-isos-for-linux-distros-usually-stored-with-squash)
[16] [https://docs.lumi-supercomputer.eu](https://docs.lumi-supercomputer.eu/storage/formats/FUSE/)
[17] [https://oneuptime.com](https://oneuptime.com/blog/post/2026-03-03-understand-talos-linux-squashfs-root-filesystem/view)
[18] [https://wiki.gentoo.org](https://wiki.gentoo.org/wiki/SquashFS)
[19] [https://www.quora.com](https://www.quora.com/Why-do-we-use-an-ISO-file-to-install-the-Linux-operating-system)
[20] [https://www.youtube.com](https://www.youtube.com/watch?v=Rt6U2gG0ggw&t=14)
[21] [https://www.0xf8.org](https://www.0xf8.org/2020/03/recreating-isos-that-boot-from-both-dvd-and-mass-storage-such-as-usb-sticks-and-in-both-legacy-bios-and-uefi-environments/)
[22] [https://copyrightservice.co.uk](https://copyrightservice.co.uk/reg/creating-zip-files)
[23] [https://dmturner.co.uk](https://dmturner.co.uk/blog/using-the-zip-utility-on-linux/)
[24] [https://decjude.gitbooks.io](https://decjude.gitbooks.io/elinux/content/en/dev_portals/File_Systems.html)
[25] [https://www.terabyteunlimited.com](https://www.terabyteunlimited.com/kb/kb-articles/using-ifl-to-create-restore-images-on-windows-samba-shares/)

# ---

If you're archiving this for your own personal "toolbox," the best format depends on how much you want to deal with mounting vs. extracting years down the line.

Here are the best ways to archive that 32-bit Python 2.7 environment for long-term personal use:

## 1. The .tar.gz (Best for Compatibility)

This is the Linux standard for "cold storage." It preserves all your 32-bit permissions (like the executable bits) perfectly.

* Pros: Every Linux distro for the last 30 years can open it; no root or FUSE needed.
* Cons: You have to extract it to use it.
* Command: tar -czvf my-python-archive.tar.gz My32BitApp/

## 2. SquashFS / .sfs (Best for "Ready-to-Use" Speed)

If you want to keep the archive compressed but still be able to "browse" it like a folder without extracting 500MB of Python files every time, use SquashFS.

* Pros: It’s the same tech AppImages use. It's incredibly fast and read-only (so you won't accidentally delete a file inside).
* Cons: Requires sudo to mount it via CLI, or a file manager that supports it.
* Command to create: mksquashfs My32BitApp/ my-app.sfs -comp xz
* To use it later: sudo mount my-app.sfs /mnt

## 3. The "Unpacked" Folder on a USB/Drive

If you have the disk space, just keep the folder as-is (uncompressed) on a drive formatted with a Linux filesystem (Ext4/XFS).

* Pros: Instant use. Zero overhead.
* Cons: Do NOT do this on a FAT32 or exFAT (Windows) drive. Those filesystems don't support Linux permissions; your AppRun and python binary will lose their "executable" status and the app won't start.

## Why avoid .ISO and .ZIP?

* Avoid .ISO: It's designed for optical discs. It’s clunky for simple app folders and often has strict filename character limits unless you use specific extensions (RockRidge).
* Avoid .ZIP: While it works, unzip sometimes struggles with complex Linux symlinks that Python installations often rely on. tar is the "native" way.

## Recommendation

For a "set it and forget it" archive, go with .tar.gz. It’s the safest bet for ensuring that 10 years from now, you can still get those files out exactly as they were.

Since you're archiving this for the long term, would you like to know how to statically bundle the 32-bit dynamic linker so it doesn't break if a future OS removes 32-bit support entirely?

# ---

SquashFS (.sfs) is generally very safe for long-term archiving, but there are a few compatibility details you should know to avoid future headaches.

## 1. Version 4.0 is the "Forever" Standard

The current major version, SquashFS 4.0, was released in 2009 and has been the industry standard ever since. [1]

* Kernel Support: It was merged into the main Linux kernel (mainline) in 2009.
* Future Proofing: Because massive infrastructures like Snap, Flatpak, and Live USBs (Ubuntu, Arch, etc.) rely entirely on SquashFS 4.0, it is extremely unlikely that support for it will be dropped in a future "Linux 7.x" or beyond.
* Legacy (2.x/3.x): If you find an ancient .sfs file from the early 2000s (Puppy Linux 4 era or older), it might be Version 2 or 3, which are not compatible with modern kernels. However, mksquashfs tools today create 4.0 files by default. [2, 3, 4, 5, 6]

## 2. The Real Risk: Compression Algorithms

While the "SquashFS" format is stable, the compression inside it can vary. If you use a newer algorithm like Zstandard (zstd), an older Linux 3.x kernel might not know how to decompress it. [3, 7, 8]

* Safest Bet: Use gzip (the default) or xz. Every kernel with SquashFS support for the last 15 years supports gzip. [9, 10]

## 3. Archive Safety vs. "Tarball"

| Feature [11, 12, 13, 14, 15] | SquashFS (.sfs) | Tarball (.tar.gz) |
|---|---|---|
| Random Access | Excellent. You can read one file without decompressing the whole archive. | Poor. You must decompress the whole archive to get one file. |
| Safety | Read-Only. You can't accidentally delete or modify files inside while mounted. | Vulnerable. Once extracted, files are easily changed or deleted. |
| Stability | Depends on Kernel/FUSE support. | Universal; requires only the basic tar and gzip tools. |

## Summary Recommendation

* For "Live" Use: Use .sfs. It’s the most efficient way to keep your 32-bit app "ready to run" instantly.
* For "Deep Storage": Keep a .tar.gz copy as a backup. While SquashFS is stable, tar is the "lowest common denominator" that will work on literally any Unix system ever made. [7, 16]

Check out these tools for more on SquashFS:

* SquashFS-Tools GitHub for the latest mksquashfs and unsquashfs changes.
* [Linux Kernel SquashFS Documentation](https://docs.kernel.org/filesystems/squashfs.html) for technical details on block sizes and compression. [3, 5, 17, 18, 19]

Should we look into how to auto-mount that .sfs file whenever you click your .desktop icon?

[1] [https://github.com](https://github.com/ystk/debian-squashfs-tools/blob/master/CHANGES)
[2] [https://forum.snapcraft.io](https://forum.snapcraft.io/t/squashfs-is-a-terrible-storage-format/9466)
[3] [https://www.researchgate.net](https://www.researchgate.net/publication/361584170_Implementation_of_SquashFS_Support_in_U-Boot)
[4] [https://bbs.archlinux.org](https://bbs.archlinux.org/viewtopic.php?id=171657)
[5] [https://sourceforge.net](https://sourceforge.net/p/squashfs/news/)
[6] [https://tldp.org](https://tldp.org/HOWTO/pdf/SquashFS-HOWTO.pdf)
[7] [https://www.infradead.org](https://www.infradead.org/~mchehab/kernel_docs/filesystems/squashfs.html)
[8] [https://mirror.internode.on.net](http://mirror.internode.on.net/pub/ldp/en/SquashFS-HOWTO/SquashFS-HOWTO-single.html)
[9] [https://bbs.archlinux.org](https://bbs.archlinux.org/viewtopic.php?id=83720)
[10] [https://mirror.internode.on.net](http://mirror.internode.on.net/pub/ldp/en/SquashFS-HOWTO/ar01s01.html)
[11] [https://tldp.org](https://tldp.org/HOWTO/html_single/SquashFS-HOWTO/)
[12] [https://stackoverflow.com](https://stackoverflow.com/questions/6147303/archival-filesystem-or-format)
[13] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=39799622)
[14] [https://medium.com](https://medium.com/@akashsainisaini37/how-overlayfs-and-squashfs-power-embedded-linux-storage-75273028ef20)
[15] [https://superuser.com](https://superuser.com/questions/204681/browsable-compressed-filesystem-archive-with-full-attributes)
[16] [https://serverfault.com](https://serverfault.com/questions/688438/effectively-handling-2-million-files)
[17] [https://github.com](https://github.com/plougher/squashfs-tools/blob/master/CHANGES.md)
[18] [https://docs.kernel.org](https://docs.kernel.org/filesystems/squashfs.html)
[19] [https://www.networkworld.com](https://www.networkworld.com/article/844418/software-squashfs-squishes-files-down-to-size.html)

# ---

When you move a SquashFS (.sfs) file to a different computer, the numeric UID (User ID) and GID (Group ID) stored inside the file stay exactly the same, but the usernames you see may change. [1, 2]

Linux doesn't store "usernames" like john inside the filesystem; it stores a number (like 1000). When you mount the SFS on a new machine: [2]

* Case A: If UID 1000 is john on PC1 but sarah on PC2, PC2 will show the files as owned by sarah.
* Case B: If UID 1000 doesn't exist on the new system, it will just show the raw number 1000 as the owner. [1, 2, 3]

## How to handle this for your archive

Since this is for your own use, you have two great ways to prevent permission errors on other computers:

## 1. The "Clean Slate" Method (Recommended)

When you create the SFS, you can force everything inside it to be owned by root (UID 0). Root exists on every single Linux system, so this is the most portable way to archive. [4, 5]

* Command: mksquashfs My32BitApp/ archive.sfs -all-root
* Why: This makes the files owned by root, but because your app is in a portable folder, you (as a normal user) can still read and execute everything as long as the permissions are set to 755. [6]

## 2. The "World-Readable" Method

If you don't want to mess with UIDs, just ensure your files have "read" and "execute" permissions for everyone before you squash them:

* Command: chmod -R a+rX My32BitApp/
* Result: Even if the UID doesn't match on the next computer, you will still be able to run the AppRun script because "Others" have permission to read and execute it. [6]

## Can you "re-map" them during mount?

Unlike some network filesystems, the standard Linux kernel cannot re-map UIDs while mounting a SquashFS (e.g., you can't tell it "make all 1000s look like 1001"). You must either use a third-party tool like [bindfs](https://unix.stackexchange.com/questions/158678/how-can-i-mount-a-filesystem-mapping-userids) to overlay the mount with new IDs, or just stick to the -all-root trick above. [2, 7]

Would you like to know how to verify the internal UIDs of your SFS file without mounting it first?

[1] [https://superuser.com](https://superuser.com/questions/1619057/mount-point-user-group-and-permissions-changed-after-mounting-hard-drive)
[2] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/77020/what-happens-to-uids-upon-mount-of-file-system)
[3] [https://serverfault.com](https://serverfault.com/questions/603987/whats-the-advantage-of-synchronizing-uid-gid-across-linux-machines)
[4] [https://man.cx](https://man.cx/mksquashfs%288%29)
[5] [https://tldp.org](https://tldp.org/HOWTO/pdf/SquashFS-HOWTO.pdf)
[6] [https://manpages.debian.org](https://manpages.debian.org/unstable/squashfs-tools/mksquashfs.1.en.html)
[7] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/158678/how-can-i-mount-a-filesystem-mapping-userids)

# ---

Exactly. When you're building a "time capsule" for an app, the ID mismatch is one of the most common reasons things break when you move them to a new machine or a new OS install.

Beyond just the UIDs, here is a quick "future-proofing" checklist for your 32-bit archive:

## 1. Use the "All-Root" trick

As mentioned, running mksquashfs folder/ archive.sfs -all-root is the gold standard for archives. It ensures that no matter who you are on the new machine, the files have a stable owner (UID 0). Since your app only needs read and execute permissions to run, it won't matter that you don't "own" the files.

## 2. The "No-Root" Mount (FUSE)

Since you had trouble with fusermount earlier, keep in mind that if you use an SFS for your archive, you'll need a way to open it without being root.

* Recommendation: Keep a copy of unsquashfs (from the squashfs-tools package) handy. If a future system blocks you from mounting the SFS, you can always use unsquashfs archive.sfs to just extract the whole thing into a folder and run it.

## 3. The 32-bit "Interpreter" Problem

In 5–10 years, your Linux distro might not even have the 32-bit loader (/lib/ld-linux.so.2) installed.

* The Fix: Copy that file from your current system into your lib/ folder.
* The Script: Update your AppRun to call the linker directly:

```
exec "$HERE/lib/ld-linux.so.2" --library-path "$HERE/lib" "$HERE/python/bin/python" "$@"
```

This makes the app completely independent of the host system's 32-bit setup.

## 4. Hardware/Kernel Changes

If your Python 2.7 app relies on very old kernel features (like old OSS sound drivers instead of ALSA/Pulse/Pipewire), it might fail even if the files are perfect.

* The Fix: Keep a small text file inside your archive noting which Kernel version and Distro it was last known to work on.

Does your app need to save data (like logs or configs) back into its own folder, or does it save to the user's home directory? (This affects whether you should use a read-only SFS or a writable folder).

# ---

Stripping UID/GID metadata is a smart move for "time capsule" archives to ensure they don't show broken ownership when you open them on a different system years from now.

Here is the breakdown of how to strip or "neutralize" ownership for each format:

## 1. Tar-based Archives (.tar, .tar.gz, .tar.xz, .tar.bz2)

GNU tar provides specific flags to overwrite the ownership metadata during creation.

* The "Root" Neutralizer: This forces all files inside the archive to be owned by root:root (UID 0:0), which exists on every Linux system.

```
tar --owner=0 --group=0 -czvf archive.tar.gz folder/
```

* Numeric Only: You can also use --numeric-owner to prevent tar from storing your specific username/groupname strings. [1, 2, 3]

## 2. SquashFS (.sfs)

Like tar, mksquashfs has a built-in flag to strip your personal IDs and set everything to root.

* Command:

```
mksquashfs folder/ archive.sfs -all-root
```

This is the gold standard for portable Linux filesystems.

## 3. ZIP (.zip) [4]

Standard Linux zip actually stores UID/GID by default in "extra fields," though many extractors ignore them. [5, 6]

* Strip during creation: Use the -X (or --no-extra) flag to exclude these extra attributes.

```
zip -r -X archive.zip folder/
```

* Post-processing: Tools like [StripZIP](https://github.com/KittyHawkCorp/stripzip) or specialized metadata removers can clean existing ZIPs. [7, 8]

## 4. 7z (.7z)

You don't need to do anything! The .7z format does not natively store Unix ownership or permissions. [9, 10]

* Behavior: When you extract a .7z file, the files will automatically be owned by whoever is doing the extraction.
* Note: Because it doesn't store permissions, your AppRun might lose its "executable" bit. You may need to chmod +x it after extracting. [6]

## 5. ISO (.iso)

When creating an ISO with mkisofs or genisoimage, you can use the -uid and -gid flags to force a specific ID (usually 0 for root). [2]

```
mkisofs -o archive.iso -uid 0 -gid 0 folder/
```

## 6. RAR (.rar) [11]

RAR behavior varies by version, but the Linux rar tool often ignores Unix ownership unless specifically told to save it. To be safe, avoid using the -ow (save owner) flag, and it will default to a neutral state.

## Summary Checklist for Portability

| Format [2, 5, 7, 9, 11] | Recommended Command | Metadata Status |
|---|---|---|
| Tar | tar --owner=0 --group=0 -cf ... | Strips to root |
| SFS | mksquashfs ... -all-root | Strips to root |
| ZIP | zip -r -X ... | Strips "Extra Fields" |
| 7z | 7z a ... | Native (no owner stored) |
| ISO | mkisofs -uid 0 -gid 0 ... | Strips to root |

Would you like a single "master script" that creates these archives with the correct flags for you?

[1] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/372599/create-as-a-regular-user-a-tar-with-files-owned-by-root)
[2] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/285237/tar-without-preserving-user)
[3] [https://www.gnu.org](https://www.gnu.org/software/tar/manual/html_section/Attributes.html)
[4] [https://www.youtube.com](https://www.youtube.com/watch?v=wmY1uIpRRdg)
[5] [https://unix.stackexchange.com](https://unix.stackexchange.com/questions/313656/preserving-permissions-while-zipping)
[6] [https://discourse.gnome.org](https://discourse.gnome.org/t/feature-request-no-option-to-set-user-and-group-for-tar-format-for-privacy/29276)
[7] [https://apps.microsoft.com](https://apps.microsoft.com/detail/9ndkvwc9srkz?hl=af-ZA&gl=ZA)
[8] [https://github.com](https://github.com/KittyHawkCorp/stripzip)
[9] [https://www.reddit.com](https://www.reddit.com/r/linux/comments/goisw/apparently_7zip_does_not_write_file_ownership_an/)
[10] [https://www.redhat.com](https://www.redhat.com/en/blog/encrypting-decrypting-7zip)
[11] [https://superuser.com](https://superuser.com/questions/170171/file-permission-in-zip-7z-rar)

# ---

Spot on. If you are building a portable app "time capsule," those are the only three things that actually affect whether the app functions or not.

Here is why you can safely strip the rest:

* UID/GID: As we discussed, these just cause "permission denied" or weird username bugs on other machines. Setting them to 0 (root) or stripping them is better.
* Timestamps (mtime/atime): These are just historical data. Your code doesn't care if a .py file was "last modified" in 2010 or today.
* Extended Attributes (xattrs): These usually contain system-specific metadata (like SELinux labels or "file downloaded from internet" tags) that can actually cause security blocks on a new OS.

## The "Golden Rule" for Archive Types

Since you want to preserve symlinks and execute bits, you have to be careful with your choice of format:

   1. Use Tar or SFS: These are "Linux-native." They handle symlinks and execute bits perfectly while allowing you to wipe the UID/GID metadata.
   2. Avoid 7z/RAR: While great for compression, they often destroy symlinks (turning them into copies of the file or empty files) and strip the execute bit, meaning you'd have to manually fix your AppRun and python binary every time you extract.
   3. ZIP is "Okay-ish": It can save the execute bit and symlinks if you use the right flags, but it's much less reliable across different Linux distros than a simple .tar.gz.

## How to do it "The Ultimate Way" (Tar)

If you want to create the cleanest possible archive that only keeps your 32-bit app's "functional" metadata:

```
tar --owner=0 --group=0 --numeric-owner \
    --no-acls --no-selinux --no-xattrs \
    -czvf my-32bit-app.tar.gz MyAppFolder/
```

What this gives you:

* ✅ Filenames preserved.
* ✅ Execute bits preserved (so it actually runs).
* ✅ Symlinks preserved (critical for Python and .so libraries).
* ❌ No personal IDs.
* ❌ No junk metadata from your current OS.

Do you have a lot of symlinks in your current 32-bit folder (common in lib/ folders where libfoo.so points to libfoo.so.1)?

# ---

Since your lib/ folder is full of those .so symlinks, you have to be very careful. If you use a "dumb" archive format, it will either break the links or double the file size by turning every link into a full copy of the library.

For your 32-bit archive, here is the best way to handle those links:

## 1. The Best Option: tar.gz

As we discussed, tar is built for this. It sees the symlink and stores it as a "reference" rather than a file.

Pro-Tip: Use the --sort=name flag along with the UID stripping we talked about. This makes your archive "reproducible" (the same files always result in the same hash).

```
tar --owner=0 --group=0 --numeric-owner --sort=name \
    -czvf my-app-32bit.tar.gz My32BitApp/
```

## 2. The SquashFS (.sfs) Option

SquashFS is excellent with symlinks. It deduplicates data and handles links natively.

* Archival command: mksquashfs My32BitApp/ archive.sfs -all-root -comp xz
* Why it's good: When you mount it, the links work instantly. If you have 50 links pointing to the same file, SquashFS handles that logic efficiently.

## 3. What to AVOID: .zip and .7z

* ZIP: By default, many zip tools convert symlinks into regular files. If libaaa.so.5 (a link) is 1MB and libaaa.so.5.0.1 (the file) is 1MB, your ZIP will store 2MB of data instead of 1MB. On extraction, it might not even be a link anymore.
* 7z: Similar to ZIP, standard 7zip often ignores symlinks or requires the -snl flag, which isn't supported on all versions/platforms.

## 4. A Note on "Broken" Links

Before you pack it up, run this command inside your lib/ folder to make sure you aren't archiving "dead" links that point to your current system:

```
find . -type l -xtype l
```

If it lists anything, those are broken links (they point to a file that doesn't exist inside your portable folder). You need to copy the actual .so files they point to into your lib/ folder before archiving.

## Summary for your 32-bit Python 2.7 archive:

   1. Move all .so and their .so.x links into one lib/ folder.
   2. Set permissions: chmod -R 755 My32BitApp/.
   3. Pack with Tar or SFS using the all-root settings.

Do you want to double-check if your Python binary itself is a symlink? (Commonly python is just a link to python2.7).

# ---

For Windows, the concept of "stripping metadata" is different because Windows doesn't use the same UID/GID system as Linux. Instead, Windows uses NTFS Permissions (ACLs) and Attributes.

When you archive a Windows .exe and its .dll files for the long term, your biggest enemies are Zone Identifiers (the "Mark of the Web") and Hardcoded Paths.

Here are the equivalents for a "clean" Windows archive:

## 1. The Tool: 7-Zip (.7z) or .zip

On Linux, we avoided these. On Windows, 7-Zip is the gold standard.

* Why: Windows ZIP/7z formats do not store your Windows User SID (the equivalent of UID). When someone extracts your archive on a different PC, the files automatically "inherit" the permissions of the folder they are extracted into.
* Permissions: Unlike Linux's "execute bit," Windows determines if a file is executable by its extension (.exe, .bat). You don't need to "save" an execute permission; it’s built into the filename.

## 2. Stripping "Zone Identifiers" (The "Unblock" problem)

If you download a tool and then archive it, Windows attaches a hidden stream (Alternate Data Stream) to the file that says "This came from the Internet." This can cause the "Windows protected your PC" popup years later.

* How to strip: Before archiving, right-click the folder -> Properties -> Check Unblock at the bottom.
* Command line: In PowerShell, you can strip this from everything in your folder:

```
Get-ChildItem -Path ".\MyApp" -Recurse | Unblock-File
```

## 3. The "Portable" Checklist for Windows

Since Windows apps love to hide settings in the Registry or AppData, your "time capsule" needs these three things to work like your Linux portable folder:

* DLL Redirection: Instead of a lib/ folder, Windows looks in the same directory as the .exe first. Keep all .dll files right next to the .exe.
* The .local file trick: If your old app tries to save settings to the Registry, create an empty file named myapp.exe.local (replace myapp.exe with your actual filename) in the folder. This forces some older Windows apps to look for DLLs and configs locally first.
* Manifests: If it's a very old 32-bit app (XP era), it might need a .manifest file to handle modern DPI scaling or admin rights.

## Summary Comparison

| Goal | Linux Method | Windows Equivalent |
|---|---|---|
| Strip Ownership | tar --owner=0 / mksquashfs -all-root | Use .7z (automatically ignores SIDs) |
| Execute Permission | chmod +x | Ensure file ends in .exe |
| Symlinks | Handled by tar / sfs | Avoid. Windows "Shortcuts" (.lnk) are hardcoded paths and will break. |
| Metadata | strip binary / remove xattrs | Unblock-File (removes "Mark of the Web") |

The "Archive" format choice: Use .7z with "Solid Block" enabled. It’s the most efficient for Windows and doesn't carry over any junk from your specific Windows user account.

Are you planning to run this 32-bit Windows archive on Linux via Wine, or is this strictly for a Windows environment?

# ---

That is a solid toolkit to have! You have successfully navigated the "packaging rabbit hole"—from fighting with FUSE and fusermount to mastering the nuances of UID stripping and symlink preservation.

To recap your ultimate "Time Capsule" strategy:

* For Linux (32-bit Python/Apps): Use a portable folder with an AppRun launcher, bundle everything in a lib/ folder, and archive it using tar --owner=0 --group=0 or mksquashfs -all-root to ensure it works on any machine regardless of user IDs.
* For Windows: Use .7z to naturally strip user SIDs, keep your .dll files in the same directory as the .exe for local loading, and remember to Unblock-File to strip those "Mark of the Web" trackers.
* The Golden Rule: Always preserve the Execute Bit (Linux), Filenames, and Symlinks, while nuking ownership and local system metadata.

By doing this, you aren't just saving files; you're ensuring they actually function when you pull them out of the vault years from now.

Do you want to test your 32-bit Python launcher on a different machine to see if we missed any specific library dependencies?
