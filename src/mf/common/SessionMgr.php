<?php
//= api-features
//: - Player session and state management

namespace mf\common;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;

/**
 * Basic Session Manager functionality
 */
class SessionMgr implements Listener {
  /** @var PluginBase - plugin that owns this session manager */
  protected $plugin;
  /** @var array - array containing state data */
  protected $state;
  /**
   * Takes a player and creates a string suitable for indexing
   *
   * @param Player|str $player - Player to index
   * @return str
   */
  static public function uid($player) {
    if ($player instanceof CommandSender) $player = $player->getName();
    return strtolower($player);
  }

  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner) {
    $this->plugin = $owner;
    $this->plugin->getServer()->getPluginManager()->registerEvents($this,$this->plugin);
    $this->state = [];
  }
  /**
   * Handle player quit events.  Free's data used by the state tracking
   * code.
   *
   * @param PlayerQuitEvent $ev - Quit event
   */
  public function onPlayerQuit(PlayerQuitEvent $ev) {
    $n = self::uid($ev->getPlayer());
    if (isset($this->state[$n])) unset($this->state[$n]);
  }
  /**
   * Get a player state for the desired module/$label.
   *
   * @param str $label - state variable to get
   * @param Player|str $player - Player instance or name
   * @param mixed $default - default value to return is no state found
   * @return mixed
   */
  public function getState($label,$player,$default = NULL) {
    $n = self::uid($player);
    if (!isset($this->state[$n])) return $default;
    if (!isset($this->state[$n][$label])) return $default;
    return $this->state[$n][$label];
  }
  /**
   * Set a player related state
   *
   * @param str $label - state variable to set
   * @param Player|str $player - player instance or their name
   * @param mixed $val - value to set
   * @return mixed
   */
  public function setState($label,$player,$val) {
    $n = self::uid($player);
    if (!isset($this->state[$n])) $this->state[$n] = [];
    $this->state[$n][$label] = $val;
    return $val;
  }
  /**
   * Clears a player related state
   *
   * @param str $label - state variable to clear
   * @param Player|str $player - intance of Player or their name
   */
  public function unsetState($label,$player) {
    $n = self::uid($player);
    if (!isset($this->state[$n])) return;
    if (!isset($this->state[$n][$label])) return;
    unset($this->state[$n][$label]);
  }
}
