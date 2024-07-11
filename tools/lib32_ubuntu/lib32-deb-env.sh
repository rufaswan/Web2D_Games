[ "$ROOT" ] || exit

so="/usr/lib32:/lib32"
so="$so:$PWD/lib32:$libex"
so="$so:$PWD/lib"
so="$so:$PWD/lib/i386-linux-gnu"
so="$so:$PWD/usr/lib"
so="$so:$PWD/usr/lib/i386-linux-gnu"
export LD_LIBRARY_PATH="$so"
echo "== LD_LIBRARY_PATH = $so"

### GTK engine murrine,clearlook
so="/usr/lib32/gtk-2.0:/usr/lib/i386-linux-gnu/gtk-2.0"
so="$so:$PWD/lib32/gtk-2.0"
so="$so:$PWD/usr/lib/gtk-2.0"
so="$so:$PWD/usr/lib/i386-linux-gnu/gtk-2.0"
export GTK_PATH="$so"
echo "== GTK_PATH = $so"

### libGL / libGLX / mesa drivers
# DO NOT set as i386 only , use both x86_64 + i386
# when 1024x768 desktop become 300x200
#   'wmreboot'   to reboot
#   'wmpoweroff' to shut down
#   'restartwm'  to restart graphical server
#   'wmexit'     to exit to prompt
so="/usr/lib64/dri:/usr/lib/x86_64-linux-gnu/dri"
so="$so:/usr/lib32/dri:/usr/lib/i386-linux-gnu/dri"
so="$so:$PWD/lib32/dri"
so="$so:$PWD/usr/lib/dri"
so="$so:$PWD/usr/lib/i386-linux-gnu/dri"
export LIBGL_DRIVERS_PATH="$so"
echo "== LIBGL_DRIVERS_PATH = $so"

# loop all *.deb files
mkdir -p "$PWD/deb"
for deb in *.deb; do
	[ -f "$deb" ] || continue

	lst="$PWD/deb/$deb.lst"
	echo           >  "$lst"
	dpkg -I "$deb" >> "$lst"
	dpkg -c "$deb" >> "$lst"

	dpkg-deb -x "$deb"  .
done

<<'////'
lib32 deb
	_wine
		winehq 4.0
			+ /bin/*
			+ /lib/*.so

	_file
		[repo] [mesa-utils] glxgears
		[repo] mame
		[repo] mednafen
		[repo] tetzle
		madedit 2.9
		psxfin 1.13

	_dir
		jre 8u202
			+ /bin/*
			+ /lib/i386/*.so , /lib/i386/*/*.so
		ida 5.2
			+ *.so
		opera 45.0.2552.898
			+ *.so
		palemoon 28.9.3
			+ *.so
		pcsx2 1.2.2
			+ plugins/*.so

	[repo] libncurse
	[repo] libsdl 1.2 + 2.0
	[repo] libsfml 2.x



export LD_DEBUG=files
export LD_BIND_NOW=1

ldd -v
	file=[so2]; needed by [so1]
dlopen() dlsym() dlclose()
	file=[so2]; dynamically loaded by [so1]


xorg_dri
=> /usr/bin/glxgears
=> libdl == dlopen() , dlsym() , dlclose()
tahr
xenial
bionic
fossa
	libGL.so
		libGLdispatch.so
		libGLX.so
			[libGLX_mesa.so]
			[libGLX_indirect.so]

////
