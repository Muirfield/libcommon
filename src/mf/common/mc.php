<?php
//= api-features
//: - Translations
namespace mf\common;

use mf\common\Singleton;
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
 * * mc::init($plugin,$plugin->getFile());
 * * mc::_("string to translate\n");
 * * mc::_("string to translate %1% %2%\n",$arg1,$arg2);
 * * mc::n(mc::_("singular form"),mc::_("Plural form"),$count);
 *
 */
class mc {
  /** @var str - Tagged API for singleton use... i.e. in case of multiple versions of this class */
  const API = '1.0';
  /** @var str - Tagged name for singleton use */
  const INSTANCE_ID = 'mf\common\mc';
  /** @var str - used to trigger a nag message */
  const CANARY = "DELETE THIS LINE";
  /** @var str[] - Translatable strings */
  protected $txt;

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
  public static function _(...$args) {
    $mc = self::get_mc();
    return $mc->translate($args);
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
   * @return int - number of loaded strings
   */
  public static function init(PluginBase $plugin, $path, $devlang="eng") {
    $mc = self::get_mc();
    return $mc->plugin_init($plugin, $path, $devlang);
  }
  /**
   * Return a server wide instance...
   * @param Server $owner - Server instance
   */
  public static function get_mc() {
    $inst = Singleton::getInstance(self::INSTANCE_ID,self::API);
    if ($inst === NULL) {
      $inst = new mc();
      Singleton::setInstance(self::INSTANCE_ID,$inst,self::API);
    }
    return $inst;
  }
  
  public function __construct() {
    $this->txt = [];
  }
  /**
   * This is the actual implementation for `mc::_`.
   */
  public function translate(array $args) {
    $fmt = array_shift($args);
    if (isset($this->txt[$fmt])) $fmt = $this->txt[$fmt];
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
   * Actual implementation for `self::init`
   */
  public function plugin_init(PluginBase $plugin,$path,$devlang = "eng") {
    $lang = $plugin->getServer()->getProperty("settings.language");
    $cnt = 0;
    if (file_exists($plugin->getDataFolder()."messages.ini")) {
      $cnt = self::load($this->txt, $plugin->getDataFolder()."messages.ini");
      $nagme = isset($this->txt[CANARY]);
      if (!$nagme) return $cnt;
      $plugin->getLogger()->error("Selected language \"".$lang."\" NOT available");
      $loaded = $this->txt[CANARY];
      unset($this->txt[CANARY]);
      if ($lang != $loaded) {
        $plugin->getLogger()->error("Language has been changed since");
        $plugin->getLogger()->error("\"messages.ini\" was created.");
        $plugin->getLogger()->error("You should delete this file!");
	return $cnt;
      }
    } else {
      if ($lang == $devlang) return 0; // Shortcut this!
      $msgs = $path."resources/messages/".$lang.".ini";
      if (file_exists($msgs)) return self::load($this->txt, $msgs);
      $plugin->getLogger()->error("Selected language \"".$lang."\" NOT available");
      $fp = $plugin->getResource("messages/messages.ini");
      if ($fp === NULL) return 0;
      if (!is_dir($plugin->getDataFolder())) mkdir($plugin->getDataFolder());
      file_put_contents($plugin->getDataFolder()."messages.ini",
				'"'.CANARY."\"=\"".$lang."\"\n".
				stream_get_contents($fp));
      $plugin->getLogger()->error("Creating empty \"messages.ini\"");
      $plugin->getLogger()->error("You may need to delete this file");
      $plugin->getLogger()->error("when upgrading this plugin or when");
      $plugin->getLogger()->error("switching langagues in \"pocketmine.yml\".");
    }
    $plugin->getLogger()->error("Please consider translating and submitting the");
    $plugin->getLogger()->error("translation to the plugin developer!");
    return $cnt;
  }
  /**
   * load override file
   * @param str $path - Path to ini file
   * @return int - loaded strings
   */
  public function load_file($path) {
    return self::load($this->txt,$path);
  }
  public function setmsg($msg,$txt) {
    $this->txt[$msg]= $txt;
  }
  /**
   * Load the specified message catalogue.
   * Can read .ini or .po files.
   * @param &str[] - reference to message catalogue
   * @param str $f - Filename to load
   * @return array - returns an array with strings, or NULL on error
   */
  public static function load(array &$txt, $f) {
    $potxt = "\n".file_get_contents($f)."\n";
    $k = 0;

    if (preg_match('/\nmsgid\s/',$potxt))
      $potxt = preg_replace('/\\\\n"\n"/',"\\n", preg_replace('/\s+""\s*\n\s*"/'," \"", $potxt));

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
	  ++$k;
	}
	break;
      }
    }
    return $k;
  }

  /**
   * Returns a localized string for the gamemode
   *
   * @param int mode
   * @return str
   */
  static public function gamemodeStr($mode) {
    switch ($mode) {
      case 0: return self::_("Survival");
      case 1: return self::_("Creative");
      case 2: return self::_("Adventure");
      case 3: return self::_("Spectator");
    }
    return self::_("%1%-mode",$mode);
  }
}

