#!/bin/bash

# Common initialization for scripts

if [ -z "$BASH" ] ; then
  echo "This doesn't look like BASH" 1>&2
  exit 3
fi

export utlib=$(dirname "$(readlink -f "$BASH_SOURCE")")
export scriptdir=$(dirname "$utlib")
export testdata="$scriptdir/test-data"

#scriptdir="$(dirname "$(readlink -f "$0")")"
#testdata="$scriptdir/test-data"
#utlib="$scriptdir/utils"

if [ -z "$phpcmd" ] ; then
  if type php70 >/dev/null 2>&1 ; then
    phpcmd=php70
  else
    phpcmd=$(which php 2>/dev/null) || phpcmd=php
  fi
fi

export gd3tool="$phpcmd $scriptdir/gd3tool.php"
export mcgen="$phpcmd $scriptdir/mcgen.php"
export mkver="$phpcmd $scriptdir/mkver.php"
export mkplugin="$phpcmd $scriptdir/mkplugin.php"
export mkpp="$scriptdir/mkpp"

warn() {
  echo "$@" 1>&2
}
retcode=0
fatal() {
  echo "$@" 1>&2
  retcode=2
  exit $retcode
}

phpversion() {
  $phpcmd -r 'exit((float)PHP_VERSION <((float)"'$1'") ? 1 : 0);'
  return $?
}

phplint() {
  if ! phpversion "7.0" ; then
    echo "Skipping lint as PHP version is to low ($phpcmd $($phpcmd -r 'echo PHP_VERSION;'))"
    echo "override with phpcmd"
    return 0
  fi
  
  local retval=0
  local cnt=0
  for php in $(find "$@" -type f -name '*.php')
  do
    cnt=$(expr $cnt + 1)
    output="$($phpcmd -l $php 2>&1)" && continue
    echo "$output"
    retval=1
  done
  echo "Linted files: $cnt"
  return $retval
}

check_tags() {
  if [ -n "${TRAVIS_TAG}" ] ; then
    ymlver=$($mkver -y plugin.yml)
    if [ x"${TRAVIS_TAG}" != x"${ymlver}" ] ; then
      warn "plugin.yml version needs to be updated!"
      return 1
    fi
  else
    describe=$(git describe)
    ymlver=$($mkver -y plugin.yml)
    if [ x"${describe}" != x"${ymlver}" ] ; then
      warn "${describe} != ${ymlver}"
    fi
  fi
  return 0
}


if type $phpcmd ; then
  $phpcmd -v
else
  fatal "No valid php command"
fi

if [ ! -d .git ] ; then
  echo "You should run this script from a git working repo"
  exit 1
fi

