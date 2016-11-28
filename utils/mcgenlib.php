<?php
//
// Generate message catalogues
//
require_once(__DIR__."/mcutils.php");

function file_encode($f) {
  if (file_exists($f)) {
    $itxt = file_get_contents($f);
    $otxt = html_entity_decode($itxt,ENT_NOQUOTES|ENT_HTML5,"UTF-8");
    if ($itxt != $otxt) {
      file_put_contents($f,$otxt);
      return TRUE;
    }
  }
  return FALSE;
}

function get_includes($srcfile,$incpath,&$queue) {
  if (!preg_match('/\.php$/',$srcfile)) return;
  /* First read the file to add includes... */
  $phpsrc = file_get_contents($srcfile);
  if (!preg_match_all('/use\s+([^\s;]+)/',$phpsrc,$mv)) return;

  //echo "ANALYZING: $srcfile\n";
  foreach ($mv[1] as $used) {
    $phpinc = str_replace("\\","/",$used).'.php';
    //echo "  INC: $phpinc\n";
    
    if (isset($queue[$phpinc])) continue;
    foreach ($incpath as $incdir) {
      if (is_file($incdir.$phpinc)) {
        //echo "  FOUDN: $incdir$phpinc\n";
	$queue[$phpinc] = $incdir.$phpinc;
	get_includes($incdir.$phpinc, $incpath, $queue);
	break;
      }
    }
  }
}

function scandirs($dirs,$incpath) {
  $queue = [];
  foreach ($dirs as $srcdir) {
    $srcdir = preg_replace('/\/*$/','/',$srcdir);
    $srclen = strlen($srcdir);
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir)) as $s){
      if (!is_file($s)) continue;
      if (!preg_match('/\.php$/',$s)) continue;
      $srcname = substr($s,$srclen);
      $queue[$srcname] = $s;
      get_includes($s,$incpath,$queue);
    }
  }
  return $queue;
}

function xgettext_file($po, $srcfile) {
  if (!is_file($srcfile)) return;
  $cmd = 'xgettext --no-wrap -o '.$po;
  if (file_exists($po)) {
    $cmd .= ' -j';
    $new = FALSE;
  } else {
    $new = TRUE;
  }
  $cmd .= ' '.$srcfile;
  //echo ($cmd."\n");
  system($cmd);

  // Make sure the CHARSET is defined properly...
  if ($new && file_exists($po)) {
    $potxt = file_get_contents($po);
    if (preg_match('/Content-Type:\s+text\/plain;\s+charset=CHARSET/',$potxt)) {
      file_put_contents($po,preg_replace('/\s+charset=CHARSET/',' charset=utf-8', $potxt));
    }
    unset($potxt);
  }
}

function xgettext_all($po,$queue) {
  foreach ($queue as $f) {
    //echo "PROCESSING: $f\n";
    xgettext_file($po, $f);
  }
}

function mcgen($mcdir,array $dirs, array $incpath) {
  if (!is_dir($mcdir)) return;

  foreach ($dirs as $srcdir) {
    if (!is_dir($srcdir)) die("$srcdir: Source not found\n");
  }
  $queue = scandirs($dirs,$incpath);
  if (count($queue) == 0) die("No files found!");
  echo "Source files: ".count($queue).PHP_EOL;

  $templ = "$mcdir/messages.ini";
  if (!file_exists($templ)) file_put_contents($templ,"");

  foreach (glob("$mcdir/*.ini") as $mc) {
    $po = preg_replace('/\.ini$/','.po',$mc);

    if (file_exists($po)) unlink($po);
    xgettext_all($po,$queue);
    if (!file_exists($po)) {
      echo ("xgettext_r error\n");
      return;
    }

    $nmsgs = mcutils::po_get(file_get_contents($po));
    // Add meta data tags...
    foreach (["lang","version"] as $tt) {
      $nmsgs["mc.".$tt] = "";
    }
    unlink($po);
    if ($nmsgs === null) {
      echo("Error reading $po\n");
      continue;
    }
    $in_ini = file_get_contents($mc);
    $omsgs = mcutils::ini_get($in_ini);
    if ($omsgs !== null) {
      // merge old messages -- tagging un-used translations
      foreach ($omsgs as $k=>$v) {
	if (substr($k,0,1) == "#" && !isset($nmsgs[$k])) $k = substr($k,1);
	if (isset($nmsgs[$k]))
	  $nmsgs[$k] = $v;
	else
	  $nmsgs["#$k"] = $v;
      }
    }
    $out_ini = "; ".basename($mc)."\n".mcutils::ini_set($nmsgs);

    if ($in_ini != $out_ini) {
      file_put_contents($mc,$out_ini);
      echo "Updated ".basename($mc)."\n";
    }
  }
}
