<?php
namespace mf\common;

use mf\common\BaseSubCmd;


use mf\common\BaseModule;
use mf\common\ISubDispatchable;
use mf\common\ModularPlugin;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use mf\common\mc;

/**
 * Base Module that implement a subcommand
 */
abstract class BaseSubCmd extends BaseModule implements ISubDispatchable {
  /** Return aliases
   *  @return str[]
   */
  public function getAliases() {
    return [];
  }
  /** Return permission
   *  @return str
   */
  public function getPermission() {
    return NULL;
  }
  /** Return description
   *  @return str
   */
  public function getHelp() {
    return mc::_("Generic sub command");
  }
  /** Return usage text
   *  @return str
   */
  public function getUsage() {
    return mc::_("[options]");
  }
  public function __construct(ModularPlugin $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    //echo "REGSIGER SUBCMD: ".$this->getMainCmd().", ".$this->getName()."\n";
    $plugin->registerSubCmd($this);
  }
}

