<?php
namespace mf\common;

use mf\common\BaseModule;
use mf\common\IDispatchable;
use mf\common\ModularPlugin;

use pocketmine\command\CommandExecutor;

/**
 * Base Cmd Module Object implementation
 */
abstract class BaseCmdModule extends BaseModule implements IDispatchable {
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
    return mc::_("Generic command");
  }
  /** Return usage text
   *  @return str
   */
  public function getUsage() {
    return mc::_("%1% [options]",$this->getName());
  }
  public function __construct(ModularPlugin $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    $plugin->registerCmd($this);
  }
}

