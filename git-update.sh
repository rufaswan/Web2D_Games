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
  -push   COMMENT  commit + push updates to repo
  -pull   ID       accept pull request to repo
  -force  CONFIRM  overwrite the repo
  -repush  retry push updates to repo [SKIP commit]
  -tag    VERSION  (after -push) set current progress as release

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
	'push'|'-push')
		echo "git push $git : $@"
		git commit -m "$@"
		git push origin master
		;;
	'pull'|'-pull')
		[ "$1" ] || echo "pull request has no ID"
		echo "git pull $git : # $1"
		git pull origin pull/$1/head
		git push origin master
		;;

	'-force')
		[[ "$2" == 'i_really_want_to_do_this' ]] || exit
		echo "git push --force $git : master"
		git push --force origin master
		;;
	'repush'|'-repush')
		echo "git push/retry $git"
		git push origin master
		;;
	'tag'|'-tag')
		echo "git tag $git : $@"
		git tag "$@"
		git push origin --tags
		;;

	'update'|'-update')
		if [ "$ups" ]; then
			echo "git fetch $ups"
			git fetch upstream
			git checkout master
			git merge upstream/master
		fi
		;;
	'last'|'-last')
		git log --pretty=oneline -5
		;;
	*)
		echo "$msg"
		;;
esac
