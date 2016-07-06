<?php
namespace mf\common;

use mf\common\IDispatchable;
use mf\common\IDispatcher;
use mf\common\Cmd;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Command dispatcher implementation
 */
class CmdDispatcher implements IDispatcher {
  /** @var PluginBase - plugin that owns this */
  protected $owner;
  /** @var array - command table */
  protected $executors;

  /**
   * Returns the plugin that owns this module
   */
  public function getPlugin() {
    return $this->owner;
  }
  public function __construct(PluginBase $plugin) {
    $this->owner = $plugin;
    $this->executors = [];
  }
  
  /**
   * Register a Dispatchable cmd.
   *
   * The $name can be omitted if the $scmd is an IDispatchable instance.
   * Otherwise, the $name is mandatory.
   *
   * @param CommandExecutor $scmd|IDispatchable $scmd - Sub command to register
   * @param str $name - command name
   */
  public function register(CommandExecutor $scmd, $name = NULL) {
    if ($name == NULL) {
      if ($scmd instanceof IDispatchable) {
	$name = $scmd->getName();
	$yaml = [
		"description" => $scmd->getHelp(),
		"usage" => $scmd->getUsage(),
	];
	$aliases = $scmd->getAliases();
	if (is_array($aliases)) {
	  if (count($aliases)) $yaml["aliases"] = $aliases;
	} elseif ($aliases) {
	  $yaml["aliases"] = [$aliases];
	}
	if ($scmd->getPermission()) $yaml["permission"] = $scmd->getPermission();
	Cmd::add($this->getPlugin(), $scmd, $name, $yaml);
      } else {
        throw new \RunTimeException("Invalid class ".__FILE__.",".__LINE__);
        return;
      }
    }
    $this->executors[$name] = $scmd;
  }
  /**
   * Returns an array with dispatchable objects
   * @return IDispatchable[]
   */
  public function getCommands() {
    return $this->executors;
  }
  /**
   * This is the dispatcher main entry point.
   */
  public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
    if (!isset($this->executors[$command->getName()])) return FALSE;
    return $this->executors[$command->getName()]->onCommand($sender,$command,$label,$args);
  }
}
