#!/bin/sh
#

pre() {
  for i in $(seq 1 3)
  do
    cp -r $MPDIR/worlds/world $MPDIR/worlds/ww$i
  done
}

post() {
  rm -rf $MPDIR/worlds/ww?
}

case "$1" in 
  pre)
    pre "$@"
    ls $MPDIR/worlds
    ;;
  post)
    post "$@"
    ;;
esac
  