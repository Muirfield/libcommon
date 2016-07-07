<?php
namespace mf\loader;

use pocketmine\plugin\PluginBase;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

use mf\common\Cmd;
use mf\common\PluginCallbackTask;
use mf\common\ExpandVars;
use mf\common\PMScript;
use mf\common\UniTest;
use mf\common\mc;

/**
 * This class is used for the PocketMine PluginManager
 */
class Main extends PluginBase implements CommandExecutor{
  public $autostart = NULL;
  public function onEnable() {
    $pm = $this->getServer()->getPluginManager();
    if (($gb = $pm->getPlugin("GrabBag")) !== null) {
      $this->getLogger()->info("Running with GrabBag...");
    } else {
      mc::init($this,$this->getFile());
      
      // Register commands...
      Cmd::add($this,$this,"echo",[
	"description" => "Repeat stuff",
	"usage" => "/echo [text]"
      ]);
      Cmd::add($this,$this,"unitest",[
        "description" => "Used to run unit tests",
	"usage" => "/unitest begin|end NAME"
      ]);
      $this->getServer()->getScheduler()->scheduleDelayedTask(
	new PluginCallbackTask($this,[$this,"autostart"],[]),20
      );
    }
  }
  public function getFilePath() { return $this->getFile(); }
  public function runscripts() {
    if (!is_array($this->autostart)) return;
    if (count($this->autostart) == 0) {
      $this->autostart = NULL;
      return;
    }
    $this->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this,[$this,"runscripts"],[]),20
    );
    $script = array_shift($this->autostart);
    $interp = PMScript::getInterp($this);
    $ctx = new ConsoleCommandSender;
    $this->getLogger()->info("Running: ".basename($script));
    $interp->runFile($ctx, $script, [], FALSE);
  }
  public function autostart() {
    $this->autostart = [];
    $this->getLogger()->info("libcommon: autostart");
    $this->getLogger()->info("DATA: ".$this->getServer()->getDataPath());
    foreach (glob($this->getServer()->getDataPath().'autostart/*.pms') as $s) {
      $this->autostart[] = $s;
    }
    natsort($this->autostart);
    $this->runscripts();
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
      $sender->sendMessage($vars->expand(implode(" ",$args),$this->getServer(),
			    ($sender instanceof Player) ? $sender : NULL));
      return TRUE;
    case "unitest":
      if (count($args) == 0) return FALSE;
      $op = array_shift($args);
      return $this->unitest($sender, $op, $args);
    default:
      $sender->sendMessage("Unimplemented command: ".$cmd->getName());
      return FALSE;
    }
  }
  
  public function unitest(CommandSender $sender, $op,array $args) {
    if (count($args) == 0) {
      $sender->sendMessage("Must specify a test name");
      return FALSE;
    }
    switch ($op) {
      case "begin":
      case "b":
	UniTest::begin($this->getServer(), implode(" ",$args));
	return TRUE;
      case "end":
      case "e":
	UniTest::end($this->getServer(), implode(" ",$args));
	return TRUE;
      default:
        $sender->sendMessage("Invalid op: $op");
	return FALSE;
    }
    return TRUE;
  }
}
