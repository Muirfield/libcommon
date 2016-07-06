<?php
//
// Sample plugin testing Modular support
//
namespace demo2;
use mf\common\ModularPlugin;
use mf\common\mc;

use demo2\Module1;
use demo2\Module2;

class Main extends ModularPlugin {
  public function onEnable() {
    mc::init($this,$this->getFile());
    $this->addModule("cmd1",new Module1($this,[]));
    $this->addModule("cmd2",new Module2($this,[]));
  }
}
