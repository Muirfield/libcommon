#!/usr/bin/env php
<?php
define('SRCDIR',dirname(realpath(__FILE__)));
require_once(SRCDIR."/utils/mcgenlib.php");
define('CMD',array_shift($argv));
error_reporting(E_ALL);

if (!count($argv)) die("Must specify a sub-command\n");
define('SUBCMD',array_shift($argv));

function subcmd_gen(array $args) {
  $incpath = [];
  while (count($args)) {
    if (substr($args[0],0,2) == '-I') {
      $incpath[] = preg_replace('/\/*$/','/',substr(array_shift($args),2));
    } else {
      break;
    }
  }
  if (count($args) < 2) die("Usage:\n\tcmd gen [-Idir] mcfile dir ...\n");
  $mcdir = array_shift($args);
  mcgen($mcdir,$args,$incpath);
}

function subcmd_enc(array $args) {
  foreach ($args as $srcdir) {
    if (!is_dir($srcdir)) continue;

    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir)) as $s){
      if (!is_file($s)) continue;
      if (!preg_match('/\.ini$/',$s)) continue;
      if (file_encode($s)) echo "$s: updated\n";
    }
  }
}


switch (SUBCMD) {
  case 'gen':
    subcmd_gen($argv);
    break;
  case 'enc':
    subcmd_enc($argv);
    break;
  case 'help':
    ?>
Usage:
- <?=CMD?> gen <messages_dir> <srcdirs>
- <?=CMD?> enc <srcdirs>
    <?php
    echo PHP_EOL;
    break;
  default:
    die("Unknown subcommand ".SUBCMD.PHP_EOL);
}




