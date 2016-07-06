<?php
namespace mf\common;

use mf\common\IModule;
use mf\common\ModularPlugin;
use mf\common\Pager;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\TextFormat;

/**
 * Implements sub command help
 */
class HelpSubCmd extends BaseSubCmd {
  /** @var str - main command name */
  protected $maincmd;  
  /** @var SubCmdDispatcher - dispatcher object */
  protected $dispatcher;

  public function __construct(ModularPlugin $plugin, $mcmd) {
    $this->maincmd = $mcmd;
    parent::__construct($plugin,[]);
    $cmd1 = $plugin->getCmds();
    $tab = $cmd1->getCommands();
    $this->dispatcher = $tab[$mcmd];
  }
  public function getMainCmd() { return $this->maincmd; }
  public function getName() { return "help"; }
  public function getAliases() { return ["hlp","-h","-?"]; }
  public function getHelp() {
    return mc::_("Show help for %1%", $this->getMainCmd());
  }
  /** Return usage text
   *  @return str
   */
  public function getUsage() {
    return mc::_("%1% %2% [subcmd] [page]",$this->getMainCmd(),$this->getName());
  }
  /**
   * Main entry point
   */
  public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
    $pageNumber = Pager::getPageNumber($args);
    $txt = [];
    if (count($args) == 0) {
      // List of commands...
      $txt[] = [ mc::_("Available"), mc::_("Sub-commands for %1%", $this->getMainCmd()) ];
      $cmds = $this->dispatcher->getCommands();
      ksort($cmds);
      foreach ($cmds as $i=>$j) {
        $ln = [ $i , $j->getHelp() ];
	$aliases = $j->getAliases();
	if (!is_array($aliases)) $aliases = [ $aliases ];
	foreach ($aliases as $k) {
	  $ln[0] .= "|".$k;
	}
	$txt[] = $ln;
      }
      return Pager::paginateTable($sender,$pageNumber, $txt);
    }
    if (count($args) == 1) {
      // Look-up command
      foreach ($this->dispatcher->getCommands() as $i => $j) {
        if ($i != $args[0]) {
	  $aliases = $j->getAliases();
	  if (!is_array($aliases)) $aliases = [ $aliases ];
	  if (!in_array($args[0],$aliases)) continue;
	}
	// Found it!
	$txt[] = mc::_("Help for %1%", $this->getMainCmd());
	$txt[] = sprintf("%s %s: %s", $this->getMainCmd(), $j->getName(), $j->getHelp());
	$txt[] = sprintf("%s %s %s", $this->getMainCmd(), $j->getName(), $j->getUsage());
	return Pager::paginateText($sender, $pageNumber, $txt);
      }
      // Not found
      $sender->sendMessage(TextFormat::RED.mc::_("Command %1% does not exist",$args[1]));
      return TRUE;
    }
    $sender->sendMessage(TextFormat::RED.mc::_("Usage: %1%", $this->getUsage()));
    return TRUE;
  }

}
