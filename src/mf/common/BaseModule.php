<?php
namespace mf\common;

use mf\common\IModule;
use pocketmine\plugin\PluginBase;

/**
 * Base Module Object implementation
 */
class BaseModule implements IModule {
  /** @var PluginBase - plugin that owns this module */
  protected $owner = NULL;
  /** @var array - Configuration for this module */
  protected $cfg = NULL;
  /**
   * Returns the plugin that owns this module
   */
  public function getPlugin() {
    return $this->owner;
  }
  public function __construct(PluginBase $plugin, array $cfg = []) {
    $this->owner = $plugin;
    $this->cfg = $cfg;
  }
}

