#!/bin/bash

git=$(cat repo.git)
ups=$(cat repo.fork)
[ "$git" ] || exit

msg="
please type a comment to commit and push
  type -retry  to retry git push [no commit]
  type -pull   to do a git pull
  type -update to update your forked repo
  type -force  to overwrite the repo
  type -last   to view the last 5 commits
"
[ $# = 0 ] && { echo "$msg"; exit; }

git remote rm  origin
git remote add origin "$git"
if [ "$ups" ]; then
	git remote rm  upstream
	git remote add upstream "$ups"
fi

case "$1" in
	"-pull")
		echo "git pull $git : master"
		git pull origin master
		;;
	"-force")
		[[ "$2" == "i_really_want_to_do_this" ]] || exit
		echo "git push --force $git : master"
		git push --force origin master
		;;
	"-retry")
		echo "git push $git"
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
		echo "git push $git : $@"
		git add .
		git ls-files --deleted -z | xargs -0 git rm
		git reflog expire --expire=now --all
		git gc --prune=now
		git commit -m "$@"
		git push origin master
		;;
esac
