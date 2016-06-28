<?php
//= syntax
//: # Embedded documentation syntax
//:
//: Embedded documentation is a "//" comment in PHP with the following text:
//:
//:     //=
//:
//: This introduces a new snippet definition.
//:
//:     //#
//:
//: This introduces a new snippet definition, but the text is also used
//:
//:     //:
//:
//: This adds body text to the snippet definition.
//:
//:     //>
//:
//: This adds body text but html entities are escaped.
//:
//: If the "=" or "#" tags are not used, the current file name is used.
//:

/**
 * Analyze PHP source and extract markup.
 *
 * The following mark-up is detected:
 *
 * - PermUtils::add - these lines are added to "rtperms".
 * - "# key" => "description" - these lines are added to the last
 *   snippet section found.
 * - //= - this lines indicate a new snippet with the provided section name
 * - //> - add a line to the current snippet (escaping HTML entitites)
 * - //# - define a section but also adds the text to the snippet.
 * - //: - add text to the current snippet.
 *
 * @param str $src - path to files
 * @param array &$snippets - array that will received markup.
 * @return none - but $snippets is modified
 */
function analyze_php($src,array &$snippets) {
  $scode = basename($src);
  foreach(file($src,FILE_IGNORE_NEW_LINES) as $lni) {
    if (!preg_match('/^\s*\/\/(.) ?(.*)\s*$/',$lni,$mv)) {
      if (($lno = preg_replace('/^\s*PermUtils::add/',"",$lni)) != $lni) {
	$lno = preg_replace('/^[^,]+,\s*/',"",$lno);
	$lno = preg_replace('/\s*[^"]+$/',"",$lno);
	if (trim($lno) == "") continue;
	$lno = eval("return  [ $lno ];");
	if ($lno === false) continue;
	list($name,$desc,$def) = $lno;
	switch (strtolower($def)) {
	  case "true":
	    $deftx = "";
	    break;
	  case "false":
	    $deftx = " (disabled)";
	    break;
	  case "op":
	  case "notop":
	  default:
	    $deftx = " ($def)";
	}

	if (!isset($snippets["rtperms"])) $snippets["rtperms"] = [];
	$snippets["rtperms"][] = "* ".$name.$deftx.": ".$desc;
	continue;
      }

      if (preg_match('/^(\s*)"#(.*)"\s*=>\s*"(.*)"\s*,?\s*$/',$lni,$mv) ||
		preg_match('/^(\s*)"#(.*)"\s*=>\s*"(.*)"\s*,?\s*\/\/\s*(.*)$/',$lni,$mv)) {
	// Probably a config doc line...
	if (count($mv) == 4) {
	  list(,$indent,$setting,$descr) = $mv;
	} else {
	  list(,$indent,$setting,$descr,$more) = $mv;
	  $descr .= " ".$more;
	}
	if (!isset($snippets[$scode])) $snippets[$scode] = [];
	switch (substr($setting,0,1)) {
	  case "|":
	    $snippets[$scode][] = substr($setting,1).": ".$descr;
	    break;
	  default:
	    // Figure out indentation...
	    $indent = "";
	    for ($i = count($snippets[$scode])-1;$i >= 0;$i--) {
	      if ($snippets[$scode][$i] == "") continue;
	      if (preg_match('/^(\s*)/',$i,$mv)) $indent = $mv[1];
	      break;
	    }
	    $snippets[$scode][] = $indent."* ".$setting.": ".$descr;
	  }
	}
	continue;
      }
      list (,$sel,$ln) = $mv;
      if ($sel == ">") {
	$ln = htmlentities($ln);
      } elseif ($sel == "=") {
	$id = strtolower(trim($ln));
	if ($id == "") continue;
	$scode = $id;
	continue;
      } elseif ($sel == "#") {
	$id = strtolower(strtr(preg_replace('/^\s*[^a-zA-Z0-9]+\s*/',"",$ln),[" " =>""]));

	if ($id == "") continue;
	$scode = $id;
      } elseif ($sel != ":") continue;

      if (!isset($snippets[$scode])) $snippets[$scode] = [];
      $lc = count($snippets[$scode]);
      if ($ln == "" && $lc > 1 && $snippets[$scode][$lc-1] ==  "") continue;
      $snippets[$scode][] = $ln;
    }
}

/**
 * Walks a directory tree looking for PHP files and extracts markups
 *
 * @param str $dir - path to directory
 * @param array &$snippets - array that will received markup.
 */
function analyze_tree($dir,&$snippets) {
  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $s){
    if (!is_file($s)) continue;
    $d = substr($s,strlen($dir));
    if (!preg_match('/\.php$/',$d)) continue;
    $doc = analyze_php($s,$snippets);
  }
}

/**
 * Analyze a template document and break-it to parts.
 *
 * Looks for lines with:
 *
 * - <!-- snippet: SNIPPET -->' : Tagged with "<SNIPPET>\nsnippet-id"
 * - <!-- template: template -->' : Tagged with "<TEMPLATE>\ntemplate name"
 * - <!-- end-include --> : signas the end of a template/snippet
 * 
 *
 * @param str $otxt - text to analyze
 * @param bool $donttag - do not mark results with tags
 */
function analyze_doc($otxt,$donttag = FALSE) {
  $txt = [""];
  $stateq = [ "body" ];

  foreach($otxt as $ln) {
    $x = strtolower(trim($ln));
    if (preg_match('/<!--\s*snippet:\s*(\S+)\s*-->/',$x,$mv)) {
      array_unshift($stateq,"find-eot");
      $txt[] = $ln;
      if (!$donttag) $txt[] = "\n<SNIPPET>\n".$mv[1];
      continue;
    }
    if (preg_match('/<!--\s*template:\s*(\S+)\s*-->/',$x,$mv)) {
      array_unshift($stateq,"find-eot");
      $txt[] = $ln;
      if (!$donttag) $txt[] = "\n<TEMPLATE>\n".$mv[1];
      continue;
    }
    if ($stateq[0] == "find-eot") {
      if (preg_match('/<!--\s*end-include\s*-->/',$x)) {
	$txt[] = $ln;
	array_shift($stateq);
      }
      continue;
    }
    if ($ln == "" && count($txt) > 1 && $txt[count($txt)-1] ==  "") continue;
    $txt[] = $ln;
  }
  return $txt;
}

/**
 * Check if a string starts with a certain text.
 * If it does not match, a NULL is returned, otherwise
 * returns the text **AFTER** the matched text.
 *
 * @param str $txt - text to look in (haystack)
 * @param str $tok - text to look for (needle)
 * @return str|NULL - returns the text after the match or NULL.
 */
function startsWith($txt,$tok) {
  $ln = strlen($tok);
  if (substr($txt,0,$ln) != $tok) return NULL;
  return trim(substr($txt,$ln));
}

/**
 * Looks into the output of `analyze_doc` and does the tag substitutions
 *
 * @param str[] $txt - output of `analyze_doc`
 * @param str[] $snippets - snippet definitions
 * @param mixed $yaml - Contents of YAML plugin definition
 * @return str[] - Returns an array of strings
 */
function expand_tags($txt,$snippets,$yaml) {
  $meta = [];
  $out = [""];
  foreach ($txt as $ln) {
    if (($cm = startsWith($ln,"\n<SNIPPET>\n")) !== null) {
      if (isset($snippets[$cm])) {
	foreach ($snippets[$cm] as $i) {
	  $out[] = $i;
	}
      } else {
	$out[] = "<!-- MISSING SNIPPET: $cm -->";
      }
    } elseif (($cm = startsWith($ln,"\n<TEMPLATE>\n")) !== null) {
      // Insert a template...
      ob_start();
      $result = include($cm);
      if ($result === FALSE) echo "<!-- MISSING TEMPLATE: $cm -->\n";
      foreach (explode("\n",ob_get_clean()) as $i) {
	$out[] = $i;
      }
    } elseif (preg_match('/<!--\s*php:(.*)\s*-->/',$ln,$mv)) {
      eval($mv[1]);
      $out[] = $ln;
    } elseif (preg_match('/<!--\s*meta:\s*(\S+)\s*[:=]\s*(.*)\s*-->/',$ln,$mv)) {
      $meta[$mv[1]] = $mv[2];
      $out[] = $ln;
    } else {
      // Handle embedded text expansions...
      foreach ([
	'/<!--\s*\$(\S+)\s*-->([^<]*)<!--\s*\$\s*-->/'=>'<!--$%s-->%s<!--$-->',
	'/\[(\S+)\]\(([^\)]*)\)/'=>'[%s](%s)'
      ] as $re=>$fmt) {
	if (preg_match_all($re,$ln,$mv,PREG_OFFSET_CAPTURE|PREG_SET_ORDER)) {
	  $ntxt = '';
	  $start = 0;
	  foreach ($mv as $mm) {
	    $ntxt .= substr($ln,$start,$mm[0][1] - $start);
	    $vname=$mm[1][0];
	    $strv=$mm[2][0];
	    if (isset($meta[$vname])) {
	      $strv=$meta[$vname];
	    } elseif (isset($yaml[$vname])) {
	      $strv=$yaml[$vname];
	    }
	    $ntxt .= sprintf($fmt,$vname,$strv);
	    $start = $mm[0][1]+strlen($mm[0][0]);
	  }
	  $ntxt .= substr($ln,$start);
	  $ln = $ntxt;
	}
      }
      $out[] = $ln;
    }
  }
  while (count($out) && $out[0] == "") array_shift($out);
  while (count($out)>1 && $out[count($out)-1] == "") array_pop($out);
  $out[] = "";
  $out[] = "";
  return $out;
}
