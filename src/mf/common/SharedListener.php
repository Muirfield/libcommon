<?php
//= api-features
//: - Shared listener object

namespace mf\common;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\plugin\PluginDisableEvent;

use mf\common\Singleton;

/**
 * Helper class to implement Shared Listeners
 *
 * To make a Listener shared accross the PM server you need to instantiate your class
 * differently.
 *
 *     SharedListener::init($plugin,$id,function() {
 *       new ListenerClass($args)
 *     }, $api);
 */
class SharedListener implements Listener {
  /** @var PluginBase - plugin that owns this listener */
  protected $plugin;
  protected $id;
  protected $api;
  protected $factory;
  
  const DEFAULT_API = "0.0";
  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner, $id,$api,$factory) {
    $this->plugin = $owner;
    $this->plugin->getServer()->getPluginManager()->registerEvents($this,$this->plugin);
    $this->id = $id;
    $this->api = $api;
    $this->factory = $factory;
  }
  public function onPluginDisable(PluginDisableEvent $ev) {
    if ($ev->getPlugin()->getName() == $this->plugin->getName()) return;
    $inst = Singleton::getIntance($this->id,$this->api);
    if ($inst != NULL) {
      list($cpmname, $sched, $oldlistener) = $inst;
      if ($sched !== NULL) return;
      if ($cpmname != $ev->getPlugin()->getName()) return;
    } else {
      throw new Exception("Internal Error for Shared Listener ".$this->id.",".$this->api);
      return;
    }
    $h = $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(
	new PluginCallbackTask($this->plugin,[$this,"restart"],[]),5
    );
    $inst = [ $cpmname, $h->getTaskId(), $oldlistener ];
    Singleton::setInstance($id, $inst, self::API);
  }
  public function restart() {
    $factor = $this->factory;
    $listener = $factory($this->plugin);
    $inst = [ $this->plugin->getName(), NULL, $listener ];
    Singleton::setInstance($this->id, $inst, $this->api);
  }
  /**
   * @param PluginBase $owner
   */
  static public function init(PluginBase $owner,$id,$factory,$api = self::DEFAULT_API) {
    $inst = Singleton::getInstance($id, $api);
    if ($inst === NULL) {
      $listener = $factory($owner);
      $inst = [ $owner->getName(), NULL, $listener ];
      Singleton::setInstance($id, $inst, $api);
    } else {
      list ($cpmname, $task, $listener) = $inst;
      $mgr = new SharedListenerMgr($owner,$id,$api,$factory);
    }
    return $listener;
  }
  static public function getSharedListener($id,$api=self::DEFAULT_API) {
    $inst = Singleton::getInstance($id, $api);
    if ($inst == NULL) return NULL;
    list ($cpmname, $task, $listener) = $inst;
    return $listener;
  }
}
