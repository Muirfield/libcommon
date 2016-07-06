<?php
//= api-features
//: - Sub command support
namespace mf\common;

use mf\common\IDispatcher;
use mf\common\IDispatchable;
use mf\common\Cmd;
use mf\common\mc;
use mf\common\Perms;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\TextFormat;

/**
 * Bae Module Object implementation
 */
class SubCmdDispatcher implements IDispatcher {
  /** @var PluginBase - plugin that owns this */
  protected $owner = NULL;
  /** @var array - sub command table */
  protected $executors = NULL;
  /** @var array - command map */
  protected $cmdmap = NULL;
  /** @var str - command name */
  protected $name;
  /**
   * Returns the plugin that owns this module
   */
  public function getPlugin() {
    return $this->owner;
  }
  /**
   * @param PluginBase $plugin - owner of this command
   * @param str $name - Main command name
   * @param array $yaml - Configuration for command (see Cmd::add)
   */
  public function __construct(PluginBase $plugin, $name, $yaml = []) {
    $this->owner = $plugin;
    $this->executors = [];
    $this->cmdmap = [];
    $this->name = $name;
    /* Figure out the current situation... */
    $current = $plugin->getServer()->getCommandMap()->getCommand($name);
    if ($current) return;
    
    if (!isset($yaml["description"])) $yaml["description"] = mc::_("%1% command set", $name);
    if (!isset($yaml["usage"])) $yaml["usage"] = mc::_("Usage: %1% <subcmd> [options]", $name);
  
    Cmd::add($plugin, $this, $name, $yaml);
  }
  /**
   * This method is called when no sub-command is specified
   *
   * @param CommandSender $sender - command sender
   */
  public function defaultSub(CommandSender $sender) {
    $sender->sendMessage(TextFormat::RED.mc::_("No sub command specified"));
    return TRUE;
  }
  /**
   * This method is called when a unknown sub-command is used
   *
   * @param CommandSender $sender - command sender
   * @param str $op - Sub command requested
   */
  public function notfound(CommandSender $sender, $op) {
    $sender->sendMessage(TextFormat::RED.mc::_("Sub command \"%1%\" not recognized",$op));
    return TRUE;
  }
  /**
   * Main entry point
   *
   * @param CommandSender $sender - command sender
   * @param Command $command - command object
   * @param str $label
   * @param str[] $args - command line arguments
   */
  public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
    if (count($args) == 0) return $this->defaultSub($sender);
    $subcmd = array_shift($args);
    if (!isset($this->cmdmap[$subcmd])) return $this->notfound($sender,$subcmd);
    $subcmd = $this->cmdmap[$subcmd];
    $execer = $this->executors[$subcmd];
    $perm = $execer->getPermission();
    if ($perm && !Perms::access($sender, $perm)) return TRUE;
    return $this->executors[$subcmd]->onCommand($sender,$command,$subcmd, $args);
  }
  /**
   * Register a Dispatchable cmd
   * @param IDispatchable $scmd - Sub command to register
   * @param str $name - sub command to register (if $scmd is not IDispatchable)
   */
  public function register(CommandExecutor $scmd, $name = NULL) {
    if ($name == NULL) {
      if ($scmd instanceof IDispatchable) {
	$name = $scmd->getName();
	$this->executors[$name] = $scmd;
	$this->cmdmap[$name] = $name;
	$aliases = $scmd->getAliases();
	if (!is_array($aliases)) $aliases = [$aliases];
	foreach ($aliases as $alias) {
	  $this->cmdmap[$alias] = $name;
	}
      } else {
        throw new \RunTimeException("Invalid class ".__FILE__.",".__LINE__);
      }
    } else {
      $this->executors[$name] = $scmd;
      $this->cmdmap[$name] = $name;
    }
  }
  /**
   * Returns an array with dispatchable objects
   * @return IDispatchable[]
   */
  public function getCommands() {
    return $this->executors;
  }
}
