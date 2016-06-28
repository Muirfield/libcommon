<?php
if (ini_get('phar.readonly')) {
  $cmd = escapeshellarg(PHP_BINARY);
  $cmd .= ' -d phar.readonly=0';
  foreach ($argv as $i) {
    $cmd .= ' '.escapeshellarg($i);
  }
  passthru($cmd,$rv);
  exit($rv);
}

define('CMD',array_shift($argv));
error_reporting(E_ALL);

function usage() {
  die("Usage:\n\t".CMD." [-o outdir]  <src_directory> [extra files]\n");
}
$path = ".";

if (isset($argv[0]) && $argv[0] == '-o') {
  array_shift($argv);
  $path = array_shift($argv);
  if (!isset($path)) die("Must specify output path\n");
  if (!is_dir($path)) die("$path: output directory not found\n");
}
$path = preg_replace('/\/*$/',"",$path).'/';

$plug = array_shift($argv);
if (!isset($plug)) usage();
$plug = preg_replace('/\/*$/',"",$plug).'/';

if (!is_dir($plug)) die("$plug: directory doesn't exist!\n");
if (!is_file($pluginYml = $plug."plugin.yml")) die("missing plugin manifest\n");
if (!is_dir($srcDir = $plug."src/")) die("Source folder not found\n");

/*
 * Read manifest...
 */
$fp = fopen($pluginYml,"r");
if (!$fp) die("Unable to open $pluginYml\n");
$manifest = [];
while (($ln = fgets($fp)) !== false && !(isset($manifest["name"]) && isset($manifest["version"]))) {
  if (preg_match('/^\s*(name|version):\s*(.*)\s*$/',$ln,$mv)) {
    $manifest[$mv[1]] = $mv[2];
  }
}
fclose($fp);
if (!isset($manifest["name"]) || !isset($manifest["version"])) die("Incomplete plugin manifest\n");

$ignore = [];

if (is_file($plug."ignore.txt")) {
  $ignore["ignore.txt"] = "ignore.txt";
  foreach (file($plug."ignore.txt") as $ln) {
    $ln = trim(preg_replace('/^#.$/',"",$ln));
    if ($ln === "") continue;
    $ignore[$ln] = $ln;
  }
} else {
  foreach([".gitignore"] as $i) {
    $ignore[$i] = $i;
  }
}

$pharname = $manifest["name"]."_v".$manifest["version"].".phar";
$phar = new Phar($path.$pharname);
$phar->setStub('<?php __HALT_COMPILER();');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

echo("Adding sources...\n");
$cnt = 0;
$cc1 = 0;
$cc2 = 0;

$argv[] = 'src';
$argv[] = 'resources';
$argv[] = 'plugin.yml';
$skip[] = [];

foreach ($argv as $top) {
  $ftop = $plug.$top;
  if (isset($skip[$ftop])) continue;
  $skip[$ftop] = $top;
  if (is_dir($ftop)) {
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ftop)) as $s){
      if (!is_file($s)) continue;
      $cnt++;
      $d = substr($s,strlen($plug));
      if (isset($ignore[basename($d)]) || isset($ignore[$d])) continue;
      echo("  [$cnt] $d\n");
      $phar->addFile(realpath($s),$d);
    }
  } elseif (is_file($ftop)) {
    $cnt++;
    echo("  [$cnt] $top\n");
    $phar->addFile(realpath($ftop),$top);
  } else {
    continue;
  }
}
echo("Compressing files...\n");
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
echo ("Created: $path$pharname\n");
