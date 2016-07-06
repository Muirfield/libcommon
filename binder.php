#!/usr/bin/env php
<?php
define('SRCDIR',dirname(realpath(__FILE__)));
define('CMD',array_shift($argv));
error_reporting(E_ALL);

$output = NULL;
$libpaths = [];
$srcpaths = [];

while (count($argv)) {
  switch ($argv[0]) {
    case "-o":
      if (!isset($argv[1])) die("Must specify output for -d\n");
      $output = dirpath($argv[1]);
      array_shift($argv);array_shift($argv);
      break;	
    case "-l":
      if (!isset($argv[1])) die("Must specify libdir for -l\n");
      $libpaths[] = parselibinfo($argv[1]);
      array_shift($argv);array_shift($argv);
      break;
    default:
      $srcpaths[] = dirpath(array_shift($argv));
  }
}

if ($output === NULL) die("No output specified\n");
if (count($libpaths) == 0) die("No library folders specified\n");
if (count($srcpaths) == 0) die("No source folders specified\n");

process_src($output,$srcpaths,$libpaths);

function process_src($output,$srcpaths,$libpaths) {
  if (!is_dir($output)) chkdir($output);
  
  foreach ($srcpaths as $srcdir) {
    $dp = explode(':',$srcdir,2);
    if (count($dp) == 1) {
      list($dp,$dpath) = [ $dp[0], "" ];
    } else {
      list($dp,$dpath) = $dp;
    }
    $dp = dirpath($dp);
    $dplen = strlen($dp);
    
    $queue = [
      'mods' => [],
      'list' => [],
    ];

    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dp.$dpath)) as $s){
      if (!is_file($s)) continue;
      $spath = substr($s,$dplen);
      chkdir(dirname($output.$spath));
      if (preg_match('/\.php$/',$s)) {
	fix_file($s,$output,$spath,$libpaths,$queue);
      } else {
        echo "Copying $s to $spath\n";
        copy($s,$output.$spath);
      }
    }
  }
  while (count($queue["list"])) {
    list($src, $fpath) = array_shift($queue["list"]);
    fix_file($src, $output, $fpath, $libpaths, $queue);
  }
}

function fix_file($src,$outdir,$fpath, &$libpaths, &$queue) {
  $tr = [];
  foreach ($libpaths as $j) {
    list($dirpath,$srcpath,$equivpath) = $j;
    $srcpath = str_replace("/","\\", $srcpath);
    $equivpath = str_replace("/","\\", $equivpath);
    $tr["namespace ".$srcpath.";"] = "namespace ".$equivpath.";";
    $tr["namespace ".$srcpath."\\"] = "namespace ".$equivpath."\\";
    $tr["use ".$srcpath."\\"] = "use ".$equivpath."\\";
  }
  $phptxt = file_get_contents($src);
  
  // Look for dependencies...
  if (preg_match_all('/use\s+([^\s;]+)/',$phptxt,$mv)) {
    foreach ($mv[1] as $used) {
      if (isset($queue["mods"][$used])) continue;

      $phpinc = str_replace("\\","/",$used).'.php';
      $ipath = NULL;
      foreach ($libpaths as $lib) {
	list($dirpath,$srcpath,$equivpath) = $lib;
	if (!file_exists($dirpath.$phpinc)) continue;
	if (substr($phpinc,0,strlen($srcpath)+1) != $srcpath.'/') continue;
	
	$queue["mods"][$used] = $srcpath;
	$queue["list"][$used] = [ $dirpath.$phpinc, $equivpath. substr($phpinc,strlen($srcpath)) ];
     }
    }
  }
  
  $phpnew =  implode("\n",preg_split('/[^\n]*\/\/##DEBUG[^\n]*/',strtr($phptxt,$tr)));
  echo ($phpnew != $phptxt ? "Converting" : "Copying")." ".$fpath."\n";
  chkdir(dirname($outdir.$fpath));
  file_put_contents($outdir.$fpath,$phpnew); 
}
  
function chkdir($d) {
  if (is_dir($d)) return;
  chkdir(dirname($d));
  if (!mkdir($d)) die("Unable to create path: $d\n");
}
function dirpath($p) {
  return preg_replace('/\/*$/','/',$p);
}
function parselibinfo($inp) {
  $inp = explode(":",$inp,2);
  if (count($inp) == 1) {
    $dirpath = '.';
    $xnp = $inp[0];
  } else {
    list($dirpath,$xnp) = $inp;
  }
  $inp = explode("=",$xnp,2);
  if (count($inp) == 1) {
    $srcpath = $equivpath = $inp[0];
  } else {
    list($srcpath,$equivpath) = $inp;
  }
  $dirpath = preg_replace('/\/*$/','/',$dirpath);
  $srcpath = preg_replace('/\/*$/','',$srcpath);
  $equivpath = preg_replace('/\/*$/','',$equivpath);
  return [ $dirpath,$srcpath,$equivpath ];  
}

  