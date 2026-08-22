#!/bin/bash
[ $(which ffmpeg) ] || exit
renice   --priority 19  --pid $$
taskset  --pid  --cpu-list 0  $$

ifps=60
while [ "$1" ]; do
	t1=./"${1%/}"
	tit="${t1%.*}"
	ext="${t1##*.}"
	shift

	if [ -d "$tit" ]; then
		# input png @ 60 FPS , output animation @ 10 FPS
		case "$ext" in
			'gif')
				ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-vf "split[s0][s1];\
					[s0]palettegen=\
						stats_mode=full\
						:max_colors=255\
						:reserve_transparent=1\
						:transparency_color=magenta[p];\
					[s1][p]paletteuse=dither=none\
						:diff_mode=rectangle,\
					fps=10"  "$tit".gif;;
			'apng')
				ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-f apng  -pix_fmt rgba       \
					-plays 0                     \
					-vf "fps=10"  "$tit".apng;;
			'webp')
				ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-vcodec libwebp_anim  -pix_fmt bgra \
					-lossless 1  -loop 0         \
					-vf "fps=10"  "$tit".webp;;
			'mov')
				ffmpeg -y  -v 0           \
					-r $ifps  -i "$tit"/%06d.png \
					-vcodec qtrle  -pix_fmt argb \
					-vf "fps=10"  "$tit".mov;;
		esac
	else
		let ifps=${t1:2}*1
		if (( $ifps < 1 )); then
			ifps=60
		fi
	fi
done
