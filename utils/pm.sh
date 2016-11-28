#!/bin/bash
#
# PocketMine-MP setup test environment
#
installer_url="https://raw.githubusercontent.com/PocketMine/php-build-scripts/master/installer.sh"
[ -z "$MPDIR" ] && export MPDIR=PocketMine-MP

install_pocketmine() {
  pm_install || fatal "Error install pocketmine"
  if [ ! -f $MPDIR/server.properties ] ; then
    pm_setup || fatal "Unable to set-up pocketmine"
    pm_plugins || fatal "Unable to intialize plugins"
  fi
}

pm_install() {
  [ ! -d "$MPDIR" ] && mkdir "$MPDIR"
  installer=$MPDIR/installer.sh
  if [ ! -f $installer ] ; then
    wget  -nv -O $installer ${installer_url} || return 1
  fi
  if [ ! -f $MPDIR/PocketMine-MP.phar ] ; then
    (cd $MPDIR && bash installer.sh ) || return 1
  fi
  return 0
}

pm_setup() {
  unzip -d "$MPDIR" -q -o $scriptdir/test-data/pocketmine-files.zip
  return $?
}

pm_plugins() {
  [ ! -d "$MPDIR/plugins" ] && return 1
  cp $scriptdir/test-data/*.phar "$MPDIR/plugins"
  return 0
}
