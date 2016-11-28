<?php
//= api-features
//: - API version checking

namespace mf\common;

/**
 * My PocketMine Utils class
 */
abstract class Ver {
  /** @const str VERSION plugin version string */
  const VERSION = "2.0.0dev3";

  /**
   * Checks API compatibility from $api against $version.  $version is a
   * string containing the version.  It can contain the following operators:
   *
   * >=, <=, <> or !=, =, !|~, <, >
   *
   * @param str api Installed API version
   * @param str version API version to compare against
   *
   * @return bool
   */
  static public function apiCheck($api,$version) {
    switch (substr($version,0,2)) {
      case ">=":
	return version_compare($api,trim(substr($version,2))) >= 0;
      case "<=":
	return version_compare($api,trim(substr($version,2))) <= 0;
      case "<>":
      case "!=":
	return version_compare($api,trim(substr($version,2))) != 0;
    }
    switch (substr($version,0,1)) {
      case "=":
	return version_compare($api,trim(substr($version,1))) == 0;
      case "!":
      case "~":
	return version_compare($api,trim(substr($version,1))) != 0;
      case "<":
	return version_compare($api,trim(substr($version,1))) < 0;
      case ">":
	return version_compare($api,trim(substr($version,1))) > 0;
    }
    if (intval($api) != intval($version)) return 0;
    return version_compare($api,$version) >= 0;
  }
  /**
   * libcommon library version.  If a version is provided it will check
   * the version using apiCheck.
   *
   * @param str version Version to check
   *
   * @return str|bool
   */
  static public function libcommon($version = "") {
    if ($version == "") return self::VERSION;
    return self::apiCheck(self::VERSION,$version);
  }

  /**
   * Used to check the PocketMine API version
   *
   * @param str version Version to check
   *
   * @return str|bool
   */
  static public function PM($version = "") {
    if ($version == "") return \pocketmine\API_VERSION;
    return self::apiCheck(\pocketmine\API_VERSION,$version);
  }
}
