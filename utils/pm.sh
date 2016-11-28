#!/bin/bash
#
# PocketMine-MP setup test environment
#
installer_url="https://raw.githubusercontent.com/pmmp/php-build-scripts/master/installer.sh"
jenkins_url="https://jenkins.pmmp.io/job/PocketMine-MP"

[ -z "$MPDIR" ] && export MPDIR=PocketMine-MP

install_pocketmine() {
  pm_install || fatal "Error install pocketmine"
  if [ ! -f $MPDIR/server.properties ] ; then
    pm_setup || fatal "Unable to set-up pocketmine"
    pm_plugins || fatal "Unable to intialize plugins"
  fi
}

jenkins_build() {
  local j_url="$1"
  wget -nv -O- ${j_url}/rssAll \
      | tr ' ' '\n' \
      | grep href= \
      | sed 's/href="//' \
      | cut -d'"' -f1 \
      | (while read x
  do
    [ -z "$x" ] && continue
    [ "$x" = "$j_url/" ] && continue
    echo "$x"
    break
  done)
}

jenkins_bin() {
  local build_url=$(jenkins_build "$jenkins_url")
  [ -z "$build_url" ] && return 1
  echo "BUILD_URL: $build_url" 1>&2
  local artifact=$(wget -nv -O- "$build_url" \
      | tr ' ' '\n' \
      | grep 'href="artifact/' \
      | grep 'phar"' \
      | sed 's/href="//' | cut -d'"' -f1 )
  [ -z "$artifact" ] && return 1
  echo "PHAR: $artifact" 1>&2
  echo "$build_url/$artifact"
}

pm_install() {
  [ ! -d "$MPDIR" ] && mkdir "$MPDIR"
  installer=$MPDIR/installer.sh
  if [ ! -f $installer ] ; then
    wget  -nv -O $installer ${installer_url} || return 1
  fi
  if [ ! -f $MPDIR/PocketMine-MP.phar ] ; then
    # Figure out what is the latest jenkins build...
    if phar_url="$(jenkins_bin)" ; then
      ( cd $MPDIR && bash installer.sh -v custom -t "$phar_url" || return 1)
    else
      ( cd $MPDIR && bash installer.sh ) || return 1
    fi
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
