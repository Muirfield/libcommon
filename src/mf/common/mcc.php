<?php
namespace mf\common;
use aliuly\common\mcbase;

/**
 * Localizable strings for libcommon
 */
abstract class mcc extends mcbase {
  static $txt = NULL;
  public static function _(...$args) {
    return self::translate(self::$txt,$args);
  }
  public static function init($newtxts) {
    if (self::$txt === NULL) self:$txt = [];
    foreach ($newtxts as $a=>$b) {
      if ($b) self::$txt[$a] = $b;
    }
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

