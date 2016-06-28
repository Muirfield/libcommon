<?php
//= api-features
//: - Permission checks and utilities
namespace mf\common;
use mf\common\mcc;

use pocketmine\Player;
use pocketmine\command\CommandSender;

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
    if ($msg) $sender->sendMessage(mcc::_("You do not have permission to do that."));
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
    if ($msg) $sender->sendMessage(mcc::_("You can only do this in-game"));
    return FALSE;
  }
}

