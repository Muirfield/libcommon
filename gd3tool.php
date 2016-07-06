#!/usr/bin/env php
<?php
define('SRCDIR',dirname(realpath(__FILE__)));
require_once(SRCDIR."/utils/gendoc3.php");
require_once(SRCDIR."/utils/Spyc.php");
define('CMD',array_shift($argv));
error_reporting(E_ALL);

if (!count($argv)) die("Must specify a sub-command\n");
define('SUBCMD',array_shift($argv));

function subcmd_strip(array $args) {
    $mode = 'view';
    while (count($args)) {
      switch ($args[0]) {
	case "-v":
	  $mode = "view";
	  array_shift($args);
	  continue;
	case "-w":
	  $mode = "write";
	  array_shift($args);
	  continue;
      }
      break;
    }
    //echo __FILE__.",".__LINE__.PHP_EOL;
    foreach ($args as $f) {
      if (($txt = file_get_contents($f)) === false)
	die("$f: error reading file\n");
      $txt = analyze_doc(explode("\n",$txt),true);
      while (count($txt)>1 && $txt[count($txt)-1] == "") array_pop($txt);
      $txt[] = "";
      switch($mode) {
	case "view":
	  if (count($args) > 1) echo "$f =====\n";
	  echo implode("\n",$txt);
	  break;
	case "write":
	  file_put_contents($f,implode("\n",$txt));
	  break;
	default:
	  die("Invalid mode: $mode\n");
      }
    }
}

function subcmd_analyze(array $args) {
  foreach ($args as $d) {
    $data = [];
    if (is_file($d)) {
      echo "SOURCE: $d\n";
      analyze_php($d,$data);
    } elseif (is_dir($d)) {
      echo "DIRECTORY: $d\n";
      analyze_tree($d,$data);
    }
    print_r($data);
  }
}

function subcmd_gen(array $args) {
  $save = FALSE;
  $doc = 'README.md';
  $path = SRCDIR . PATH_SEPARATOR . SRCDIR . '/templates' . PATH_SEPARATOR . '{dirname}';
  $ymlfile = 'plugin.yml';
  
  while (count($args)) {
    switch ($args[0]) {
      case "-w":
	$save = TRUE;
	array_shift($args);
	continue;
      case "-d":
	if (!isset($args[1])) die("Must specify document for -d\n");
	$doc = $args[1];
	array_shift($args);array_shift($args);
	continue;	
      case "-I":
	if (!isset($args[1])) die("Must specify document for -I\n");
	if ($path == "")
	  $path = $args[1];
	else
	  $path .= PATH_SEPARATOR . $args[1];
	array_shift($args);array_shift($args);
	continue;
      case "-P":
	if (!isset($args[1])) die("Must specify document for -P\n");
	$path = $args[1];
	array_shift($args);array_shift($args);
	continue;	
      case "-y":
	if (!isset($args[1])) die("Must specify document for -y\n");
	$ymlfile = $args[1];
	array_shift($args);array_shift($args);
	continue;	
    }
    break;
  }
  if (!count($args)) $args = [ "src" ];
  $path = str_replace('{dirname}',dirname(realpath($doc)),$path);
  
  fwrite(STDERR,"MODE:    ".($save ? "SAVE" : "PREVIEW").PHP_EOL);
  fwrite(STDERR,"DOC:     ".$doc.PHP_EOL);
  fwrite(STDERR,"INCPATH: ".$path.PHP_EOL);
  fwrite(STDERR,"YMLFILE: ".$ymlfile.PHP_EOL);

  if (!is_file($ymlfile)) die($ymlfile.": Not found\n");
  $yaml = Spyc::YAMLLoad($ymlfile);
  if (!is_file($doc)) die($doc.": Not found\n");
  set_include_path(get_include_path().PATH_SEPARATOR.$path);

  $snippets = [];
  foreach ($args as $d) {
    if (is_file($d)) {
      fwrite(STDERR,"Analyzing ".$d."...");
      analyze_php($d,$snippets);
    } elseif (is_dir($d)) {
      fwrite(STDERR,"Analyze tree ".$d."...");
      analyze_tree($d,$snippets);
    } else continue;
    fwrite(STDERR,"\n");
  }

  $otxt = file_get_contents($doc);
  $ntxt = analyze_doc(explode("\n",$otxt));
  $ntxt = expand_tags($ntxt,$snippets,$yaml);
  $ntxt = implode("\n",$ntxt);
  if ($save) {
    if ($otxt != $ntxt) {
      file_put_contents($doc,$ntxt);
      fwrite(STDERR,"Updated ".$doc.PHP_EOL);
    }
  } else {
    echo $ntxt;
  }
}

switch (SUBCMD) {
  case 'analyze':
    subcmd_analyze($argv);
    break;
  case 'strip':
    subcmd_strip($argv);
    break;
  case 'gen':
    subcmd_gen($argv);
    break;
  case 'help':
    ?>
Usage:
- <?=CMD?> analyze <src> [...]
- <?=CMD?> strip <docfile>
- <?=CMD?> gen [-w] [-d <README.md>] [-I <dir>] [-P <path>] <src> [...]
    <?php
    echo PHP_EOL;
    break;
  default:
    die("Unknown subcommand ".SUBCMD.PHP_EOL);
}




