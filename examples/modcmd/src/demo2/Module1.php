<?php
namespace demo2;
use mf\common\BaseCmdModule;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


class Module1 extends BaseCmdModule {
  public static function defaults() {
    return [ "one" => 1, "two" => 2 ];
  }  
  public function __construct(PluginBase $plugin, array $cfg = []) {
    parent::__construct($plugin,$cfg);
    $plugin->getLogger()->info("Enabling cmd module1");
    $plugin->getLogger()->info(get_class($this));
    $plugin->getLogger()->info($this->getName());
  }
  public function getName() { return "cmd1"; }
  public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
    $sender->sendMessage("Command 1 being executed");
    return TRUE;
  }
}

