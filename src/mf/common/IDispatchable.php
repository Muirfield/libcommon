<?php
namespace mf\common;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;

/**
 * Interface for dispatchable command executors
 */
interface IDispatchable extends CommandExecutor {
  /** Return command's name
   *  @return str
   */
  public function getName();
  /** Return aliases
   *  @return str[]
   */
  public function getAliases();
  /** Return permission
   *  @return str
   */
  public function getPermission();
  /** Return description
   *  @return str|NULL
   */
  public function getHelp();
  /** Return usage text
   *  @return str
   */
  public function getUsage();
}
