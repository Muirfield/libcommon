<?php
namespace mf\loader;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use mf\common\Cmd;
use mf\common\PluginCallbackTask;
use mf\common\ExpandVars;


//use aliuly\common\MPMU;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends PluginBase implements CommandExecutor{
  public function onEnable() {
    $pm = $this->getServer()->getPluginManager();
    if (($gb = $pm->getPlugin("GrabBag")) !== null) {
      $this->getLogger()->info("Running with GrabBag...");
    } else {
      // Register commands...
      Cmd::add($this,$this,"echo",[
	"description" => "Repeat stuff",
	"usage" => "/echo [text]"
      ]);
      $this->getServer()->getScheduler()->scheduleDelayedTask(
	new PluginCallbackTask($this,[$this,"autostart"],[]),20
      );
    }
  }
  public function autostart() {
    $this->getLogger()->info("libcommon: autostart");
    Cmd::console($this->getServer(),"echo {RED}yeah {tps}");
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Command dispatcher
  //
  //////////////////////////////////////////////////////////////////////
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "echo":
      $vars = ExpandVars::getVars($this->getServer());
      $sender->sendMessage($vars->expand(implode(" ",$args),$this->getServer(),NULL));
      return TRUE;
    default:
      $sender->sendMessage("Unimplemented command: ".$cmd->getName());
      return FALSE;
    }
  }
}
