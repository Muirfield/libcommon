<?php
//= api-features
//: - Teleport Warps and Player Homes

namespace mf\common;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\IPlayer;

use mf\common\Singleton;
use mf\common\Perms;

/**
 * Teleport Warps
 *
 */
class Warps {
  /** @var str Tagged API for singleton use... i.e. in case of multiple versions of this class */
  const API = '1.0';
  /** @var str Tagged name for singleton use */
  const INSTANCE_ID = 'mf\common\Warps';
  const WARP_FILE = 'warps.yml';
  const DEFAULT_WARP_PERM = "true";
  const HOME_PREFIX = 'home::';
  const INCLUDE_HOMES = 0x01;
  const ONLY_HOMES = 0x03;

  /** @var warps[] - warp table */
  public $warps;
  /** @var $owner - plugin that owns this resource */
  protected $owner;
  /**
   * Initialize a global Warps object
   * @param PluginBase $plugin - plugin requesting initialization...
   * @return Warps object
   */
  static public function init(PluginBase $plugin) {
    $inst = Singleton::getInstance(self::INSTANCE_ID, self::API);
    if ($inst === NULL) {
      $inst = new Warps($plugin);
      Singleton::setInstance(self::INSTANCE_ID, $inst, self::API);
    }
    return $inst;
  }
  /**
   * @param PluginBase $plugin - plugin that will own this resource (needed for callbacks)
   */
  public function __construct(PluginBase $plugin) {
    $this->owner = $plugin;
    $this->warps = [];
  }
  public function getPlugin() {
    return $this->owner;
  }
  /**
   * Load warps
   */
  public function load() {
    $this->warps = [];
    $dir = $this->getPlugin()->getServer()->getDataPath()."worlds";
    $dh = opendir($dir);
    while (($world = readdir($dh)) !== FALSE) {
      if (!$this->getPlugin()->getServer()->isLevelGenerated($world)) continue;
      $warpfile = $dir.'/'.$world.'/'.self::WARP_FILE;
      if (!is_file($warpfile)) continue;
      $warpdata = (new Config($warpfile,Config::YAML,[]))->getAll();
      if (!isset($warpdata['points'])) continue;
      foreach ($warpdata['points'] as $warpname => $warpxyzp) {
	$warpxyzp = preg_split('/\s*,\s*/',$warpxyzp,5);
	if ($warpxyzp[3] != "") {
	  Perms::add($this->getPlugin(), // Break-it in two lines so GD won't pick it up
	      $warpxyzp[3], $warpxyzp[4]);
	}
	$warpxyzp[] = $world;
	$warpxyzp[] = $warpname;
	$this->warps[strtolower($warpname)] =$warpxyzp;
      }
    }
    closedir($dh);
  }
  /**
   * Save warps.
   * @param str $sworld - if specified, only save warps for that world
   */
  public function save($sworld = NULL) {
    $arrays = [];
    if ($sworld !== NULL) $sworld = strtolower($sworld);
    foreach ($this->warps as $warpdat) {
      list ($x,$y,$z,$perms,$defperm,$world,$warpname) = $warpdat;
      $world = strtolower($world);
      if ($sworld !== NULL && $sworld != $world) continue;
      if (!isset($arrays[$world])) $arrays[$world] = [ 'points' => [], 'version' => self::API ];
      $arrays[$world]['points'][$warpname] = implode(',',[$x,$y,$z,$perms,$defperm]);
    }
    $dir = $this->getPlugin()->getServer()->getDataPath()."worlds";
    $dh = opendir($dir);
    while (($world = readdir($dh)) !== FALSE) {
      if (!$this->getPlugin()->getServer()->isLevelGenerated($world)) continue;
      $warpfile = $dir.'/'.$world.'/'.self::WARP_FILE;
      $world = strtolower($world);
      if ($sworld !== NULL && $sworld != $world) continue;     
      if (is_file($warpfile)) unlink($warpfile);
      if (!isset($arrays[$world])) continue;

      $cfg = new Config($warpfile, Config::YAML,[]);
      $cfg->setAll($arrays[$world]);
      $cfg->save();
    }
    closedir($dh);
  }
  /**
   * Create/modify a warp position
   *
   * @param str $name
   * @param Position $pos
   * @param str $perm - Permission to assign to this warp
   * @param str $defperm - Default permission (when registering it)
   */
  public function set($name,Position $pos,$perm = "", $defperm = self::DEFAULT_WARP_PERM) {
    $this->del($name); // Delete any previous definition...
    $n = strtolower($name);
    $world = $pos->getLevel()->getName();
    $this->warps[$n] = [ $pos->getX(), $pos->getY(), $pos->getZ(),
			  $perm, $defperm, $world, $name ];
    if ($perm != "") {
      Perms::add($this->getPlugin(), // Break-it in two lines so GD won't pick it up
	      $perm, $defperm);
    }
    $this->save($world);
  }
  /**
   * Lookup a warp position
   *
   * @param str $name
   * @return list([$x,$y,$z,$world],$perm) or list(NULL,NULL)
   */
  public function get($name) {
    $n = strtolower($name);
    if (!isset($this->warps[$n])) return [NULL,NULL];
    list($x,$y,$z,$perm,,$world,) = $this->warps[$n];
    return [[$x,$y,$z,$world],$perm == "" ? NULL : NULL];
  }
  /**
   * Delete a warp
   * @param str $name
   */
  public function del($name) {
    $n = strtolower($name);
    if (!isset($this->warps[$n])) return;
    list($x,$y,$z,,,$world,) = $this->warps[$n];
    unset($this->warps[$n]);
    $this->save($world);
  }
  /**
   * Reatrieve an array with all warps...
   */
  public function lst($flags = 0) {
    $results = [];
    foreach ($this->warps as $warpdat) {
      list ($x,$y,$z,$perms,$defperm,$world,$warpname) = $warpdat;
      if (($flags & self::INCLUDE_HOMES) == 0) {
	if (preg_match('/^'.self::HOME_PREFIX.'/',$warpname)) continue;
      } else {
        if (($flags & self::ONLY_HOMES) == self::ONLY_HOMES) {
	  if (!preg_match('/^'.self::HOME_PREFIX.'/',$warpname)) continue;
	  $warpname = preg_replace('/^'.self::HOME_PREFIX.'/','',$warpname);
	}
      }
      $results[$warpname] = [$x,$y,$z,$world,$perms];
    }
    return $results;
  }
  /**
   * Used to implement home functionality on the basis of Warps...
   */
  static public function getHomeWarpName(IPlayer $player) {
    return self::HOME_PREFIX . $player->getName();
  }
  public function getHome(IPlayer $player) {
    return $this->get(self::getHomeWarpName($player));
  }
  public function setHome(IPlayer $player, Position $pos) {
    $this->set(self::getHomeWarpName($player),$pos);
  }
  public function delHome(IPlayer $player) {
    $this->del(self::getHomeWarpName($player));
  }
}
