<?php
define('CMD',array_shift($argv));
error_reporting(E_ALL);

function usage() {
  die("Usage:\n\t".CMD."[-y yaml] <src_directory>\n");
}

$yaml = 'plugin.yml';
if (isset($argv[0]) && $argv[0] == '-y') {
  array_shift($argv);
  $yaml = array_shift($argv);
  if (!isset($yaml)) die("Must specify YAML file\n");
}
if (!is_file($yaml)) die("$yaml: YAML not found\n");

/*
 * Read manifest...
 */
$fp = fopen($yaml,"r");
if (!$fp) die("Unable to open $yaml\n");
$manifest = [];
while (($ln = fgets($fp)) !== false && !(isset($manifest["name"]) && isset($manifest["version"]))) {
  if (preg_match('/^\s*(name|version):\s*(.*)\s*$/',$ln,$mv)) {
    $manifest[$mv[1]] = $mv[2];
  }
}
fclose($fp);
if (!isset($manifest["name"]) || !isset($manifest["version"])) die("Incomplete plugin manifest\n");

echo $manifest['version'].PHP_EOL;

$cnt = 0;
foreach ($argv as $dir) {
  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $s){
    if (!is_file($s)) continue;
    if (preg_match('/\.[pP][hH][pP]$/' ,$s)) {
      $otxt = file_get_contents($s);
      if (preg_match('/\n\s*const\s+VERSION\s*=\s*"([^"]+)";/',$otxt,$mv,PREG_OFFSET_CAPTURE)) {
	$ntxt = substr($otxt,0,$mv[1][1]).$manifest['version'].substr($otxt,$mv[1][1]+strlen($mv[1][0]));
	if ($ntxt != $otxt) {
	  echo "Updating $s\n";
	  file_put_contents($s,$ntxt);
	  ++$cnt;
	}
      }
    }
  }
}
echo "Files changed: $cnt\n";
