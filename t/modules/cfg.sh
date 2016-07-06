#!/bin/sh
#
phars="modular modcmd subcmd"

pre() {
  for i in $phars
  do
    $mkplugin -o $MPDIR/plugins examples/$i
  done
}

post() {
  for i in $phars
  do
    rm -f $MPDIR/plugins/${i}_v*.phar
  done
}

case "$1" in 
  pre)
    pre "$@"
    ;;
  post)
    post "$@"
    ;;
esac
  