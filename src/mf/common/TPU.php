<?php
//= api-features
//: - Teleport wrappers

namespace mf\common;

use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\math\Vector3;

use mf\common\Perms;
use mf\common\mc;
use mf\common\Ver;

/**
 * Teleport Utilities
 */
abstract class TPU {
  /**
   * Teleport a player near a location
   * @param Player $player - player to be teleported
   * @param Position $target - location to teleport nearby
   * @param int $rand - how far to randomize positions
   * @param int|null $dist - if not null it will make sure that new location is upto $dist
   * @return bool - true on success, false on failure
   */
  static public function nearBy(Player $player,Position $target,$rand = 3,$dist = null) {
    // Already close enough
    if ($player->getLevel() == $target->getLevel() && $player->distance($target) < $rand) return TRUE;
    $mv = new Vector3($target->getX()+mt_rand(-$rand,$rand),$target->getY(),$target->getZ()+mt_rand(-$rand,$rand));
    $pos = $target->getLevel()->getSafeSpawn($mv);
    if ($dist !== NULL) {
      $newdist = $pos->distance($target);
      if ($newdist > $dist) return FALSE;// Will not get close enough!
    }
    $player->teleport($pos);
    return TRUE;
  }
  /**
   * Get a world name and return a level object.  Loads levels as needed
   *
   * @param Server $server
   * @param str $world
   * @param array $autoloadperms - Array containing [ CommandSender, permission string ]
   * @return list(Level|null, error message)
   */
  static public function getLevelByName(Server $server, $world, $autoloadperms = NULL) {
    if (!$server->isLevelGenerated($world)) return [NULL, mc::_("World %1% does not exist", $world)];
    if (!$server->isLevelLoaded($world)) {
      if ($autoloadperms !== NULL) {
        list($player,$perm) = $autoloadperms;
	if ($player !== NULL && !Perms::access($player,$perm)) return [NULL, mc::_("%1% is not a loaded world", $world)];
      }
      $server->loadLevel($world);
    }
    return [$server->getLevelByName($world), mc::_("%1% did not load properly", $world)];
  }
  /**
   * Teleport player to the top
   */
  static public function top(Player $pl) {
    $y = $pl->getLevel()->getHighestBlockAt($pl->getX(),$pl->getZ())+1;
    $pl->teleport(new Vector3($pl->getX(),$y,$pl->getZ()));
  }
  /**
   * Throw player to the air
   */
  static public function throwPlayer(Player $pl) {
    $pl->teleport(new Vector3($pl->getX(),128,$pl->getZ()));
  }
  /**
   * Determine the max number of players
   * @param Server $server
   * @param str|Level $world
   * @return int|NULL - returns integer or NULL
   */
  static public function getMaxPlayers(Server $server,$world) {
    $wp = $server->getPluginManager()->getPlugin("WorldProtect");
    if ($wp == NULL) return NULL;
    if (Ver::apiCheck($wp->getVersion(),"2.1.0")) return $wp->getMaxPlayers($world);
    return NULL;
  }     
}
