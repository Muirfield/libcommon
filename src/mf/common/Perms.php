<?php
//= api-features
//: - Permission checks and utilities
namespace mf\common;
use mf\common\mc;

use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\permission\Permission;

abstract class Perms {
  /**
   * Check's player or sender's permissions and shows a message if appropriate
   *
   * @param CommandSender $sender
   * @param str $permission
   * @param bool $msg If false, no message is shown
   * @return bool
   */
  static public function access(CommandSender $sender, $permission,$msg=true) {
    if($sender->hasPermission($permission)) return TRUE;
    if ($msg) $sender->sendMessage(mc::_("You do not have permission to do that."));
    return FALSE;
  }
  /**
   * Check's if $sender is a player in game
   *
   * @param CommandSender $sender
   * @param bool $msg If false, no message is shown
   * @return bool
   */
  static public function inGame(CommandSender $sender,$msg = true) {
    if ($sender instanceof Player) return TRUE;
    if ($msg) $sender->sendMessage(mc::_("You can only do this in-game"));
    return FALSE;
  }
  /**
   * Register a permission on the fly...
   * @param Plugin $plugin - owning plugin
   * @param str $name - permission name
   * @param str $desc - permission description
   * @param str $default - one of true,false,op,notop
   */
  static public function add(Plugin $plugin, $name, $desc, $default) {
    $perm = new Permission($name,$desc,$default);
    $plugin->getServer()->getPluginManager()->addPermission($perm);
  }

}

