<?php
//
// Sample plugin testing Modular support
//
namespace demo3;
use mf\common\ModularPlugin;
use mf\common\HelpSubCmd;
use mf\common\mc;

use demo3\Cmd1;
use demo3\Sub1;
use demo3\Sub2;
use demo3\Sub3;
use demo3\Sub4;

class Main extends ModularPlugin {
  public function onEnable() {
    mc::init($this,$this->getFile());
    $this->addModule("mnd1",new Cmd1($this,[]));
    $this->addModule("mnd2",new Sub1($this,[]));
    $this->addModule("mnd3",new Sub2($this,[]));
    $this->addModule("mnd4",new Sub3($this,[]));
    $this->addModule("mnd5",new Sub4($this,[]));
    $this->addModule("xl1hlp",new HelpSubCmd($this,"xl1"));
    $this->addModule("xl2hlp",new HelpSubCmd($this,"xl2"));

  }
}
