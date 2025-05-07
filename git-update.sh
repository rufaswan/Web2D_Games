#!/bin/bash
function load_repo {
	if [ -f $2 ]; then
		local url=$(cat $2)
		git remote rm  $1
		git remote add $1 "$url"
		echo "$url"
	fi
}
##############################
git=$(load_repo  origin    repo.git )
ups=$(load_repo  upstream  repo.fork)
[ "$git" ] || exit
echo "git=$git  ups=$ups"

cmd="$1"
shift
opt="$@"

[ "$cmd" ] || exit
[ "$opt" ] || exit
echo "cmd=$cmd  opt=$opt"
echo "=========="

#git diff HEAD
#git ls-files --modified
mod=$(git status --short)
echo $mod

case "$cmd" in
	'push')
		if [ "$mod" ]; then
			echo "[$git] git commit = $opt"
			{
				git add .
				git ls-files --deleted -z | xargs -0 git rm -q
				git reflog expire --expire=now --all
				git gc --prune=now
			} &> /dev/null
			git commit -m "$opt"
		fi

		echo "[$git] git push"
		git push origin master
		;;
	'log')
		echo "[$git] git $cmd"
		git log --pretty=oneline -5
		;;

	'pull')
		echo "[$git] git $cmd = $opt"
		git pull origin pull/$opt/head
		git push origin master
		;;
	'tag')
		echo "[$git] git $cmd = $opt"
		git tag "$opt"
		git push origin --tags
		;;
	'rmtag')
		echo "[$git] git $cmd = $opt"
		git push --delete origin "$opt"
		git tag -d "$opt"
		;;
	'force')
		[[ "$opt" == 'i_really_want_to_do_this' ]] || exit
		echo "[$git] git $cmd = $opt"
		git push --force origin master
		;;

	'fetch')
		if [ "$ups" ]; then
			echo "[$ups] git $cmd"
			git fetch upstream
			git checkout master
			git merge upstream/master
		fi
		;;
	*)  echo "[ERROR] unknown $cmd = $opt";;
esac
echo "=========="
