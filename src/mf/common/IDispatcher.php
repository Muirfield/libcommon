<?php
namespace mf\common;
use pocketmine\command\CommandExecutor;

/**
 * Interface for command dispatchers
 */
interface IDispatcher extends CommandExecutor {
  /* From CommandExecutor: onCommand(CommandSender $sender, Command $command, $label, array $args);
   * ... this is the dispatcher */
  
  /**
   * Register a Dispatchable cmd
   * @param CommandExecutor $scmd|IDispatchable $scmd - Sub command to register
   * @param str $name - command name
   */
  public function register(CommandExecutor $scmd, $name = NULL);
  /**
   * Returns an array with dispatchable objects
   * @return IDispatchable[]
   */
  public function getCommands();
}
