<?php
//= api-features
//: - Misc shorcuts and pre-canned routines

namespace mf\common;

use mf\common\Ver;
use mf\common\mcc;

use pocketmine\Server;
use pocketmine\command\CommandSender;

abstract class MPMU {
  /**
   * Lile file_get_contents but for a Plugin resource
   *
   * @param Plugin $plugin
   * @param str $filename
   * @return str|null
   */
  static public function getResourceContents($plugin,$filename) {
    $fp = $plugin->getResource($filename);
    if($fp === NULL) return NULL;
    $contents = stream_get_contents($fp);
    fclose($fp);
    return $contents;
  }
  /**
   * Call a plugin's function.
   *
   * If the $plug parameter is given a string, it will simply look for that
   * plugin.  If an array is provided, it is assumed to be of the form:
   *
   *   [ "plugin", "version" ]
   *
   * So then it will check that the plugin exists, and the version number
   * matches according to the rules from **apiCheck**.
   *
   * Also, if plugin contains an **api** property, it will use that as
   * the class for method calling instead.
   *
   * @param Server $server - pocketmine server instance
   * @param str|array $plug - plugin to call
   * @param str $method - method to call
   * @param mixed $default - If the plugin does not exist or it is not enable, this value is returned
   * @return mixed
   */
  static public function callPlugin(Server $server,$plug,$method,$args,$default = NULL) {
    $v = NULL;
    if (is_array($plug)) list($plug,$v) = $plug;
    if (($plugin = $server->getPluginManager()->getPlugin($plug)) === NULL
	     || !$plugin->isEnabled()) return $default;

    if ($v !== NULL && !Ver::apiCheck($plugin->getDescription()->getVersion(),$v)) return $default;
    if (property_exists($plugin,"api")) {
      $fn = [ $plugin->api , $method ];
    } else {
      $fn = [ $plugin, $method ];
    }
    if (!is_callable($fn)) return $default;
    return $fn(...$args);
  }
  /**
   * Look-up player
   * @param CommandSender|Server $c
   * @param str $n
   */
  static public function getPlayer($c,$n) {
    if ($c instanceof Server) {
      $server = $c;
      $c = NULL;
    } elseif ($c instanceof CommandServer) {
      $server = $c->getServer();
    } else
      return NULL;
    
    $pl = $server->getServer()->getPlayer($n);
    if ($pl === NULL) {
      if ($c == NULL)
	$server->getLogger()->error(mcc::_("%1% not found", $n));
      else
	$c->sendMessage(mc::_("%1% not found", $n));
    }
    return $pl;
  }
  /**
   * Check prefixes
   * @param str $txt - input text
   * @param str $tok - keyword to test
   * @return str|null
   */
  static public function startsWith($txt,$tok) {
    $ln = strlen($tok);
    if (strtolower(substr($txt,0,$ln)) != $tok) return null;
    return trim(substr($txt,$ln));
  }
}




/**
 * My PocketMine Utils class
 */
abstract class XPMU {

	/**
	 * Takes a player and creates a string suitable for indexing
	 *
	 * @param Player|str $player - Player to index
	 * @return str
	 */
	static public function iName($player) {
		if ($player instanceof CommandSender) {
			$player = strtolower($player->getName());
		}
		return $player;
	}
	/**
	 * Send a PopUp, but takes care of checking if there are some
	 * plugins that might cause issues.
	 *
	 * Currently only supports SimpleAuth and BasicHUD.
	 *
	 * @param Player $player
	 * @param str $msg
	 */
	static public function sendPopup($player,$msg) {
		$pm = $player->getServer()->getPluginManager();
		if (($sa = $pm->getPlugin("SimpleAuth")) !== null) {
			// SimpleAuth also has a HUD when not logged in...
			if ($sa->isEnabled() && !$sa->isPlayerAuthenticated($player)) return;
		}
		if (($hud = $pm->getPlugin("BasicHUD")) !== null) {
			// Send pop-ups through BasicHUD
			$hud->sendPopup($player,$msg);
			return;
		}
		$player->sendPopup($msg);
	}

}
