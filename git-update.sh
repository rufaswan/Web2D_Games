#!/bin/bash

git=$(cat repo.git)
ups=$(cat repo.fork)
[ "$git" ] || exit

git remote rm  origin
git remote add origin "$git"
if [ "$ups" ]; then
	git remote rm  upstream
	git remote add upstream "$ups"
fi

msg="
usage: ${0##*/}  COMMAND  [files]...

command
  -push  COMMENT  commit + push updates to repo
  -force CONFIRM  overwrite the repo
  -pull    pull updates from repo
  -push2   retry push updates to repo [SKIP commit]
  -update  fetch updates from upstream
  -last    view the last 5 commits

if no command, list changes for upcoming update
"
##############################
if [ $# = 0 ]; then
	git diff HEAD
	exit
fi
##############################
git add .
git ls-files --deleted -z | xargs -0 git rm
git reflog expire --expire=now --all
git gc --prune=now

cmd="$1"
shift

case "$cmd" in
	'-push')
		echo "git push $git : $@"
		git commit -m "$@"
		git push origin master
		;;
	"-force")
		[[ "$2" == "i_really_want_to_do_this" ]] || exit
		echo "git push --force $git : master"
		git push --force origin master
		;;

	"-pull")
		echo "git pull $git : master"
		git pull origin master
		;;
	"-push2")
		echo "git push/retry $git"
		git push origin master
		;;

	"-update")
		if [ "$ups" ]; then
			echo "git fetch $ups"
			git fetch upstream
			git checkout master
			git merge upstream/master
		fi
		;;
	"-last")
		git log --pretty=oneline -5
		;;
	*)
		echo "$msg"
		;;
esac
