<?php
namespace mf\common;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\Player;

use pocketmine\plugin\Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\PluginCommand;
/**
 * Utility class to execute commands|chat's as player or console
 */
abstract class Cmd {
  /**
   * Execute a command as a given player
   *
   * @param CommandSender $sender - Entity to impersonate
   * @param str $cmd - command to execute
   * @param bool $show - show commands being executed
   */
  static public function execAs(CommandSender $sender,$cmd,$show=TRUE) {
    if($show)$sender->sendMessage("CMD> $cmd");
    $sender->getServer()->dispatchCommand($sender,$cmd);
  }
  /**
   * Execute a console command
   *
   * @param Server $server - Server object
   * @param str $cmd - Command to execute
   * @param array $opts - Options, 'show', 'capture'
   * @return none|str - If 'capture' set, it will return the output of command
   */
  static public function console(Server $server,$cmd, array $opts = [ ]) {
    $show = in_array("show",$opts);
    $capture = in_array("capture",$opts);
    $sender = $capture ? new RemoteConsoleCommandSender : new ConsoleCommandSender;
    if ($show) $sender->sendMessage("CMD> $cmd");
    $server->dispatchCommand($sender,$cmd);
    if ($capture) return $sender->getMessage();
  }
  /**
   * Execute a command as a player with temp OP priviledges
   *
   * @param Player $sender - Entity to impersonate
   * @param str $cmd - command to execute
   * @param bool $show - show commands being executed
   */
  static public function sysExec(Player $sender, $cmd, $show = TRUE) {
    if ($show) $sender->sendMessage("CMD> $cmd");
    if ($sender->isOp()) {
      $sender->getServer()->dispatchCommand($sender,$cmd);
    } else {
      $sender->setOp(true);
      $sender->getServer()->dispatchCommand($sender,$cmd);
      $sender->setOp(false);
    }
  }
  /**
   * Chat a message as a given player
   *
   * @param CommandSender $sender - Entity to impersonate
   * @param str $msg - message to send
   */
  static public function chat(CommandSender $sender,$msg) {
    $server = $sender->getServer();
    $server->getPluginManager()->callEvent($ev = new PlayerChatEvent($sender,$msg));
    if ($ev->isCancelled()) return;
    $server->broadcastMessage($server->getLanguage()->translateString(
				$ev->getFormat(),
				[$ev->getPlayer()->getDisplayName(), $ev->getMessage()]),
			$ev->getRecipients());
  }

  /**
   * Register a command
   *
   * @param Plugin $plugin - plugin that "owns" the command
   * @param CommandExecutor $executor - object that will be called onCommand
   * @param str $cmd - Command name
   * @param array $yaml - Additional settings for this command.
   */
  static public function add(Plugin $plugin, CommandExecutor $executor, $cmd, array $yaml) {
    $newCmd = new PluginCommand($cmd,$plugin);
    if (isset($yaml["description"]))
      $newCmd->setDescription($yaml["description"]);
    if (isset($yaml["usage"]))
      $newCmd->setUsage($yaml["usage"]);
    if(isset($yaml["aliases"]) and is_array($yaml["aliases"])) {
      $aliasList = [];
      foreach($yaml["aliases"] as $alias) {
	if(strpos($alias,":")!== false) {
	  $plugin->getLogger()->info("Unable to load alias $alias");
	  continue;
	}
	$aliasList[] = $alias;
      }
      $newCmd->setAliases($aliasList);
    }
    if(isset($yaml["permission"]))
      $newCmd->setPermission($yaml["permission"]);
    if(isset($yaml["permission-message"]))
      $newCmd->setPermissionMessage($yaml["permission-message"]);
    $newCmd->setExecutor($executor);
    $cmdMap = $plugin->getServer()->getCommandMap();
    $cmdMap->register($plugin->getDescription()->getName(),$newCmd);
  }
  /**
   * Unregisters a command
   * @param Server $srv - Access path to server instance
   * @param str $cmd - Command name to remove
   */
  static public function remove(Server $srv, $cmd) {
    $cmdMap = $srv->getCommandMap();
    $oldCmd = $cmdMap->getCommand($cmd);
    if ($oldCmd === null) return FALSE;
    $oldCmd->setLabel($cmd."_disabled");
    $oldCmd->unregister($cmdMap);
    return TRUE;
  }
}
