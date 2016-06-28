<?php
//= api-features
//: - Translations

namespace mf\common;
use pocketmine\plugin\PluginBase;

/**
 * Base translation class in the style of **gettext**.
 *
 * You can actually use **gettext** tools to process these files.
 * For example, to create/update a message catalogue use:
 *
 * `xgettext --no-wrap [-j] [-o file]`
 *
 * Where -j is used to join an existing catalague.
 * -o file is the output file.
 *
 * Example Usage:
 *
 * Create a container class:
 *
 * * use mf\common\mcbase;
 * * use mf\common\mcc; // OPTIONAL
 * * abstract class mc extends mcbase {
 * *  static $txt = NULL;
 * *  public static function _(...$args) {
 * *   return self::translate(self::$txt,$args);
 * *  }
 * *  public static function init(PluginBase $plugin,$path) {
 * *    self::$txt = self::plugin_init($plugin,$path);
 * *    mcc::init(self::$txt); // OPTIONAL
 * *    return count(self::$txt);
 * *  }
 *
 * Example calls:
 *
 * * mc::plugin_init($plugin,$plugin->getFile());
 * * mc::_("string to translate\n")
 * * mc::_("string to translate %1% %2%\n",$arg1,$arg2)
 * * mc::n(mc::\_("singular form"),mc::\_("Plural form"),$count)
 *
 */

abstract class mcbase {
  /**
   * Load the specified message catalogue.
   * Can read .ini or .po files.
   * @param str $f - Filename to load
   * @return array - returns an array with strings, or NULL on error
   */
  public static function load($f) {
    $potxt = "\n".file_get_contents($f)."\n";

    if (preg_match('/\nmsgid\s/',$potxt))
      $potxt = preg_replace('/\\\\n"\n"/',"\\n", preg_replace('/\s+""\s*\n\s*"/'," \"", $potxt));

    $txt = [];
    foreach ([
	'/\nmsgid "(.+)"\nmsgstr "(.+)"\n/','/^\s*"(.+)"\s*=\s*"(.+)"\s*$/m'
    ] as $re) {
      $c = preg_match_all($re,$potxt,$mm);
      if ($c) {
	for ($i=0;$i<$c;++$i) {
	  if ($mm[2][$i] == "") continue;
	  eval('$a = "'.$mm[1][$i].'";');
	  eval('$b = "'.$mm[2][$i].'";');
	  $txt[$a] = $b;
	}
	return $txt;
      }
    }
    return [];
  }
  /** Main translation function
   *
   * This translates strings.  The naming of "_" is to make it compatible
   * with gettext utilities.  The string can contain "%1%", "%2%, etc...
   * These are inserted from the following arguments.  Use "%%" to insert
   * a single "%".
   *
   * @param str[] $texts - translation table
   * @param str[] $args - messages
   * @return str translated string
   */
  public static function translate(array $texts,array $args) {
    $fmt = array_shift($args);
    if (isset($texts[$fmt])) $fmt = $texts[$fmt];
    if (count($args)) {
      $vars = [ "%%" => "%" ];
      $i = 1;
      foreach ($args as $j) {
	$vars["%$i%"] = $j;
	++$i;
      }
      $fmt = strtr($fmt,$vars);
    }
    return $fmt;
  }
  /**
   * Plural and singular forms.
   *
   * @param str $a - Singular form
   * @param str $b - Plural form
   * @param int $c - the number to test to select between $a or $b
   * @return str - Either plural or singular forms depending on the value of $c
   */
  public static function n($a,$b,$c) {
    return $c == 1 ? $a : $b;
  }
  /**
   * Initialize plugin translation texts...
   *
   * @param PluginBase $plugin - Plugin object
   * @param str $path - path to plugin files
   * @param str $devlang - Developer's native language.  Strings for this language do NOT need to be translated.
   * #return str[] - array of translation strings
   */
  public static function plugin_init(PluginBase $plugin,$path,$devlang = "eng") {
    $lang = $plugin->getServer()->getProperty("settings.language");
    if (file_exists($plugin->getDataFolder()."messages.ini")) {
      $texts =  self::load($plugin->getDataFolder()."messages.ini");
      $nagme = isset($texts["DELETE THIS LINE"]);
      if (!$nagme) return $texts;
      $plugin->getLogger()->error("Selected language \"".$lang."\" NOT available");
      if ($lang != $texts["DELETE THIS LINE"]) {
        $plugin->getLogger()->error("Language has been changed since");
        $plugin->getLogger()->error("\"messages.ini\" was created.");
        $plugin->getLogger()->error("You should delete this file!");
	return $texts;
      }
    } else {
      if ($lang == $devlang) return []; // Shortcut this!
      $msgs = $path."resources/messages/".$lang.".ini";
      if (file_exists($msgs)) return self::load($msgs);
      $plugin->getLogger()->error("Selected language \"".$lang."\" NOT available");
      $fp = $plugin->getResource("messages/messages.ini");
      if ($fp === NULL) return [];
	if (!is_dir($plugin->getDataFolder())) mkdir($plugin->getDataFolder());
	file_put_contents($plugin->getDataFolder()."messages.ini",
				"\"DELETE THIS LINE\"=\"".$lang."\"\n".
				stream_get_contents($fp));
	$plugin->getLogger()->error("Creating empty \"messages.ini\"");
	$plugin->getLogger()->error("You may need to delete this file");
	$plugin->getLogger()->error("when upgrading this plugin or when");
	$plugin->getLogger()->error("switching langagues in \"pocketmine.yml\".");
      }
      $texts = [];
    }
    $plugin->getLogger()->error("Please consider translating and submitting it");
    $plugin->getLogger()->error("to the plugin developer!");
    return $texts;
  }
}

