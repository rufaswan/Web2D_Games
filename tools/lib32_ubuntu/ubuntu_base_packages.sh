#!/bin/bash
# LTS = "pqrs tuvw xyza "
# LTS = "bcde fghi jklm nopq rstu vwxy zabc"
dist="dapper  hardy  lucid  precise  trusty  xenial  bionic  focal"
comp="main  multiverse  restricted  universe"
arch="i386  amd64"
zip="xz  bz2  gz"
loc="archive  old-releases"

for d in $dist; do
	echo "dist $d"

	for a in $arch; do
		echo "arch $a"
		pool="pool_${d}_${a}.lst"
		[ -f "$pool"     ] && continue
		[ -f "$pool".zip ] && continue

		pwd="$PWD"
		mkdir -p "/tmp/$d"

		for c in $comp; do
			echo "comp $c"

			for z in $zip; do
				echo "zip $z"
				deb="${d}_${c}_${a}_deb"
				[ -f "$deb".* ] && continue

				tmp="/tmp/ubuntu.${z}"
				echo "DOWNLOAD $deb.${z}"

				for l in $loc; do
					echo "loc $l"
					wget -O "$tmp" \
						"http://${l}.ubuntu.com/ubuntu/dists/$d/$c/binary-$a/Packages.${z}" \
						&& mv -vf "$tmp" "$deb".$z
				done # end loc

			done # end zip

			cp -v "$deb".*  "/tmp/$d"

		done # end comp

		cd "/tmp/$d"

		unzipfile.sh -q *
		cat *main*  *multiverse*  *restricted*  *universe* | grep -i "filename: pool" | sort > $pool
		zip $pwd/$pool.zip  $pool

		cd "$pwd"
		rm -vfr "/tmp/$d"

	done # end arch

done # end dist
