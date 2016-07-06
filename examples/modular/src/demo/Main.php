<?php
//
// Sample plugin testing Modular support
//
namespace demo;
use mf\common\ModularPlugin;
use mf\common\mc;

use demo\Module1;
use demo\Module2;
use demo\Module3;
use demo\Module4;
use demo\Module5;

class Main extends ModularPlugin {
  public function onEnable() {
    mc::init($this,$this->getFile());
    $this->modConfig([
      "mod1" => [Module1::class, true],
      "mod2" => [Module2::class, false],
      "mod3" => [["mod2",Module3::class], true],
      "mod4" => [[Module4::class,Module5::class], true],
    ], [
      "version" => $this->getDescription()->getVersion(),
      "mod1" => Module1::defaults(),
      "mod2" => Module2::defaults(),
    ]);
  }
}
