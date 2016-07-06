<?php
namespace demo;
use mf\common\BaseModule;
use pocketmine\plugin\PluginBase;


class Module2 extends BaseModule {
  
  public static function defaults() {
    return [ "one" => 1, "two" => 2 ];
  }
  public function __construct(PluginBase $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    $plugin->getLogger()->info("Enabling module2");
  }
}

