<?php
namespace demo;
use mf\common\BaseModule;
use pocketmine\plugin\PluginBase;

class Module5 extends BaseModule {
  public function __construct(PluginBase $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    $plugin->getLogger()->info("Enabling module5");
  }
}

