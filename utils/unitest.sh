#!/bin/sh
#
default_tests() {
  local testdir="$1" ; shift
  [ ! -d "$testdir" ] && fatal "Missing test suite directory"
  if [ $# -eq 0 ] ; then
    for dir in $testdir/*/
    do
      [ -d $dir ] && echo $dir
    done
  else
    for dir in "$@"
    do
      if [ -d "$dir" ] ; then
        echo $dir
      elif [ -d "$testdir/$dir" ] ; then
	echo "$testdir/$dir"
      else
        warn "Unknown test: $(basename $dir)"
      fi
    done
  fi
}


unitest_prepare() {
  local unit="$1" test name
  # Prepare test environment
  [ -d $MPDIR/autostart ] && rm -rf $MPDIR/autostart
  mkdir -p $MPDIR/autostart
  for test in $unit/*.pms
  do
    [ ! -f $test ] && continue
    name=$(basename $test .pms)
    (
      echo "unitest begin $name"
      cat $test
      echo "unitest end $name"
    ) > $MPDIR/autostart/$name.pms
  done
  echo 'stop' > $MPDIR/autostart/z_done.pms
  rm -rf $MPDIR/t
}

unitest_sh_script() {
  # Run pre/post scripts
  local unit="$1" op="$2" tst
  for tst in $unit/*.sh
  do
    [ ! -x $tst ] && continue
    sh $tst $op $unit
  done
}

unitest_results() {
  local unit="$1" res
  # Gather results
  for res in $MPDIR/t/*
  do
    if [ -f "$res" ] ; then
      fail "Failed $(basename $unit)/$(basename $res)"
    fi
  done
}

runtests() {
  for unit in "$@"
  do
    [ ! -d $unit ] && continue
    echo "###############################################################"
    echo "# $(basename $unit)"
    echo "###############################################################"

    # Prepare environment
    unitest_prepare $unit
    unitest_sh_script $unit pre

    # Execute in test environment
    ( cd $MPDIR && ./start.sh )

    # Clean-up and gather results
    unitest_sh_script $unit post
    unitest_results $unit
  done
}

cnt=0
rm -f failure.log
fail() {
  echo "$@" 1>&2
  echo "$@" >>failure.log
  cnt=$(expr $cnt + 1)
  retcode=1
}

show_results() {
  if [ $cnt -eq 0 ] ; then
    echo "No failed tests"
    return
  fi
  echo "Failed Tests: $cnt"
  [ -f failure.log ] && cat failure.log
}


