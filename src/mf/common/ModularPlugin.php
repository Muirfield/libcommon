<?php
//= api-features
//: - Modular plugins
namespace mf\common;
use mf\common\BasicPlugin;
use mf\common\IModular;
use mf\common\IModule;
use mf\common\IDispatchable;
use mf\common\ISubDispatchable;
use mf\common\CmdDispatcher;
use mf\common\SubCmdDispatcher;
use mf\common\mc;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\Config;
/**
 * Modular plugin class
 */
abstract class ModularPlugin extends BasicPlugin implements IModular {
  /** @var str - Marks as a module that is being loaded */
  const ST_LOADING = "is_loading...";
  /** @var array - Module array */
  protected $modules = NULL;
  /** @var IDispatcher - commmand dispatcher */
  protected $cmds = NULL;
  /**
   * @param str $name - module to look-up
   * @return NULL|IModule
   */
  public function getModule($name) {
    if (is_array($this->modules)) {
      while (isset($this->modules[$name])) {
	if ($this->modules[$name] instanceof IModule) return $this->modules[$name];
	$name = $this->modules[$name];
      }
    }
    return NULL;
  }
  /**
   * Get array with modules
   * @return array
   */
  public function getModules() {
    return $this->modules;
  }
  /**
   * Given some defaults, this will load optional modules
   *
   * @param array $mods - module definition
   * @param array $defaults - default options to use for config.yml
   * @return array
   */
  public function modConfig($mods,$defaults) {
    if (!isset($defaults["features"])) $defaults["features"] = [];
    foreach ($mods as $i => $j) {
      $defaults["features"][$i] = $j[1];
    }
    if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
    $cfg=(new Config($this->getDataFolder()."config.yml",Config::YAML,$defaults))->getAll();
			    
    $this->modules = [];
    
    foreach ($cfg["features"] as $i=>$j) {
      if (!$j) continue;
      $this->enableModule($i,$mods,$cfg);
    }
    $c = count($this->modules);
    if ($c == 0) {
      $this->getLogger()->info(mc::_("NO features enabled"));
    } else {
      $c /= 2;
      $this->getLogger()->info(mc::n(mc::_("Enabled one feature"),mc::_("Enabled %1% features",$c),$c));
    }
    return $cfg;
  }

  protected function enableModule($feature,$mods,$cfg) {
    if (!is_array($this->modules)) return;
    if (!isset($mods[$feature])) {
      $this->getLogger()->info(mc::_("Unknown feature \"%1%\" ignored.",$feature));
      return;
    }
    $features = $mods[$feature][0];
    if (!is_array($features)) $features = [ $features ];
    $this->modules[$feature] = self::ST_LOADING;

    $i = count($features);
    $class = NULL;
    foreach ($features as $class) {
      --$i;
      if (isset($this->modules[$class])) continue; // Already loaded...
      if (isset($mods[$class])) {
	// This is a feature reference...
	$this->enableModule($class,$mods,$cfg);
      } else {
	// Load class...
	$obj = new $class($this,$i ? [] : (isset($cfg[$feature]) ? $cfg[$feature] : []));
	if ($obj instanceof IModule) {
	  $this->modules[$class] = $obj;
	} else {
	  $this->getLogger()->error(mc::_("%1% is not an instance of %2%", $class, IModule::class));
	  return;
	}
      }
    }
    if ($class == NULL) {
      $this->getLogger()->error(mc::_("Error activating feature \"%1%\"",$feature));
      return;
    }
    $this->modules[$feature] = $class;
    return;
  }
  /**
   * Add Module
   * @param str $feature|NULL - Feature name
   * @param str|IModule $obj - Alias or IModule object
   */
  public function addModule($feature,$obj) {
    if ($obj instanceof IModule) {
      if ($feature == NULL) {
	$class =get_class($obj);
      } else {
	$class = $this->modules[$feature ] = get_class($obj);
      }
      $this->modules[$class] = $obj;
    } else // Guess this is an alias...
      $this->modules[$feature] = $obj;
  }

  /**
   * Call a module method
   * @param str $name - module
   * @param str $method - method name
   * @param array $args - arguments to use
   * @param mixed $def - return value if $name or $method are not found
   * @return mixed
   */
  public function callModule($name,$method,$args = [], $def= NULL) {
    $module = $this->getModule($name);
    if ($module === NULL) return $def;
    if (!is_callable($call = [$module,$method])) return $def;
    return $call(...$args);
  }
  /**
   * Register a command for the command dispatcher
   * @param CommandExecutor|IDispatchable $executor - command executor
   * @param str $name - name for this command, if $executor is IDispatchable, it can be omitted
   */
  public function registerCmd(CommandExecutor $executor, $name = NULL) {
    if ($this->cmds == NULL) $this->cmds = new CmdDispatcher($this);
    $this->cmds->register($executor,$name);
  }
  /**
   * Register a sub command
   * @param ISubDispatchable $executor - subcommand executor
   */
  public function registerSubCmd(ISubDispatchable $executor) {
    if ($this->cmds == NULL) $this->cmds = new CmdDispatcher($this);
    $cmd = $executor->getMainCmd();
    $table = $this->cmds->getCommands();
    if (isset($table[$cmd])) {
      $sub = $table[$cmd];
    } else {
      $sub = new SubCmdDispatcher($this, $cmd);
      $this->cmds->register($sub,$cmd);
    }
    $sub->register($executor);
  }
  /**
   * Get the command dispatcher object
   * @return IDispatcher
   */
  public function getCmds() {
    return $this->cmds;
  }
  
  /**
   * Entry point for a command executor.
   *
   * @param CommandSender $sender
   * @param Command       $command
   * @param string        $subcommand
   * @param string[]      $args
   *
   * @return boolean
   */
  public function onCommand(CommandSender $sender, Command $command, $sub, array $args) {
    if ($this->cmds == NULL) return FALSE;
    return $this->cmds->onCommand($sender,$command,$sub,$args);
  }

}
