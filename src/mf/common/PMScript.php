<?php
//# ## PMScript
//:
//: The PMScript module implements a simple [PHP](https://secure.php.net/)
//: based scripting engine.  It can be used to enter multiple PocketMine
//: commands while allowing you to add PHP code to control the flow of
//: the script.
//:
//: While you can embed any arbitrary PHP code, for readability purposes
//: it is recommended that you use
//: [PHP's alternative syntax](http://php.net/manual/en/control-structures.alternative-syntax.php)
//:
//: By convention, PMScript's have a file extension of ".pms" and they are
//: just simple text file containing PocketMine console commands (without the "/").
//:
//: To control the execution you can use the following prefixes when
//: entering commands:
//:
//: * **+op:** - will give Op access to the player (temporarily) before executing
//:   a command
//: * **+con:** - execute command as if run from the console with the output sent to the player.
//: * **+syscon:** - run the command as if it was run from the console.
//:
//: Also, before executing a command variable expansion (e.g. {vars}).
//:
//: Available variables depend on installed plugins, pocketmine.yml
//: settings, execution context, etc.
//:
//: It is possible to use PHP functions and variables in command lines by
//: surrounding PHP expressions with:
//:
//:      '.(php expression).'
//:
//: For example:
//:
//:      echo MaxPlayers: '.$interp->getServer()->getMaxPlayers().'
//:
//: ### Adding logic flow to PMScripts
//:
//: Arbitrary PHP code can be added to your pmscripts.  Lines that start
//: with "@" are treated as PHP code.  For your convenience,
//: you can ommit ";" at the end of the line.
//:
//: Any valid PHP code can be used, but for readability, the use of
//: alternative syntax is recommended.
//:
//: The execution context for this PHP code has the following variables
//: available:
//:
//: * **$interp** - reference to the running PMSCript object.
//: * **$ctx** - This is the CommandSender that is executing the script
//: * **$server** - PocketMine server instance
//: * **$player** - If **$ctx** refers to a player, **$player** is defined, otherwise it is NULL.
//: * **$args** - Script's command line arguments
//:
//: `{varnames}` are also available directly.
//:
//: Example:
//:
//:     # Sample PMScript
//:     #
//:     ; You can use ";" or "#" as comments
//:     #
//:     # Just place your commands as you would enter them on the console
//:     # on your .pms file.
//:     echo You have the following plugins:
//:     plugins
//:     echo {GOLD}Variable {RED}Expansions {BLUE}are {GREEN}possible
//:     echo TPS: {tps} MOTD: {MOTD}
//:     #
//:     # You can include in there PHP expressions...
//:     say '.$ctx->getName().' is AWESOME!
//:     ;
//:     # Adding PHP control code is possible:
//:     @if ({tps} > 10):
//:       echo Your TPS {tps} is greater than 10
//:     @else:
//:       echo Your TPS {tps} is less or equal to 10
//:     @endif
//:     ;
//:     ;
//:     echo You passed '.count($args).' arguments to this script.
//:	echo Arguments: '.print_r($args,TRUE).'

namespace mf\common;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use mf\common\Singleton;
use mf\common\ExpandVars;
use mf\common\MPMU;
use mf\common\Cmd;

class PMScript {
  /** @var str Tagged API for singleton use... i.e. in case of multiple versions of this class */
  const API = '1.0';
  /** @var str Tagged name for singleton use */
  const INSTANCE_ID = 'mf\common\ExpandVars';

  /** @var callable[] - prepared scripts cache */
  public $cache;
  /** @var ExpandVars - defined variables */
  public $vars;
  /** @var bool - If TRUE, process Priviledge escalation options */
  public $opcmds;
  /** @var mixed[] - hive variables */
  public $hive;
  
  /**
   * @param ExpandVars $vars|NULL - allow for standard variable expansion
   */
  public function __construct(Server $srv,$opcmd = TRUE, $vars = NULL) {
    $this->cache = [];
    if ($vars == NULL) {
      $this->vars = ExpandVars::getVars($srv);
    } else {
      $this->vars = $vars;
    }
    $this->opcmds = $opcmd;
    $this->hive = [];
  }
  /**
   * Return a server wide instance...
   * @param Server $owner - Server instance
   * @param bool $opcmd - allow for OP commands
   */
  static public function getInterp(Server $owner, $opcmd = TRUE) {
    $id = self::INSTANCE_ID . ($opcmd ? ',op' : ',no');
    $inst = Singleton::getInstance($id, self::API);
    if ($inst === NULL) {
      $inst = new PMScript($owner, $opcmd);
      Singleton::setInstance($id, $inst, self::API);
    }
    return $inst;
  }
  /**
   * Run and execute a given set of commands
   *
   * @param CommandSender $ctx - execution context
   * @param str $cmds - text of PMScript
   * @param array $args - command-line arguments (if any)
   * @param bool $cache - use script cache
   */
  public function runScript(CommandSender $ctx,$cmds, array $args, $cache = FALSE) {
    if ($cache) {
      if (!isset($this->cache['SCRIPT:'.$cmds]))
	$this->cache['SCRIPT:'.$cmds] = $this->prepareScript($cmds);
      $code = $this->cache['SCRIPT:'.$cmds];
    } else {
      $code = $this->prepareScript($cmds);
    }
    $this->runCode($ctx,$code,$args);
  }
  /**
   * Run and execute a script from file
   *
   * @param CommandSender $ctx - execution context
   * @param str $fname - name of a script
   * @param array $args - command-line arguments (if any)
   * @param bool $cache - use script cache
   */
  public function runFile(CommandSender $ctx,$fname, array $args, $cache = TRUE) {
    if ($cache) {
      if (!isset($this->cache['FILE:'.$fname]))
	$this->cache['FILE:'.$fname] = $this->prepareFile($fname);
      $code = $this->cache['FILE:'.$fname];
    } else {
      $code = $this->prepareFile($fname);
    }
    $this->runCode($ctx,$code,$args);
  }
  /**
   * Prepare a script from file
   *
   * @param str $fname - name of a script
   */
  public function prepareFile($fname) {
    $cmds = file_get_contents($fname);
    return $this->prepareScript($cmds);
  }
  /**
   * Run and execute a callable from a prepared PMScript
   *
   * @param CommandSender $ctx - execution context
   * @param callable $code - code from prepared script
   * @param array $args - command-line arguments (if any)
   */
  public function runCode(CommandSender $ctx, $code, array $args) {
    try {
      $code($this,$ctx,$ctx->getServer(),($ctx instanceof Player) ? $ctx : NULL, $args);
    } catch (\Exception $e) {
      $ctx->sendMessage("Exception: ".$e->getMessage());
    }
  }
  /**
   * Prepare a script from a string
   *
   * @param str $cmds - PMScript commands
   */
  public function prepareScript($cmds) {
    $php = '';
    $php .= ' return function($interp,$ctx,$server,$player,$args) {'.PHP_EOL;
    
    foreach (explode("\n",$cmds) as $ln) {
      $ln = trim($ln);
      if ($ln == '' || $ln{0} == '#' || $ln{0} == ';') continue; // Skip comments and empty lines
      if ($ln{0} == '@') {
        $c = substr($ln,-1);
	$q = ($c == ':' || $c == ';') ? PHP_EOL : ";\n";
	$php .= $vars->phpexpand($ln).$q;
      } else {
        $php .= '  $interp->exec($ctx,'.$vars->phpfy($ln).');'.PHP_EOL;
      }
    }
    $php .= '};';
    echo "PHP: $php\n";//##DEBUG
    return eval($php);
  }
  /**
   * Execute a command
   * @param CommandSender $ctx - Command context
   * @param str $cmdline - Command to execute
   */
  public function exec(CommandSender $ctx, $cmdline) {
    $re = '/^\s*+(op|con|syscon):\s*/';
    if ($this->opcmds && ($ctx instanceof Player)) {
      if (preg_match($re,$cmdline,$mv)) {
        $cmdline = preg_replace($re,'',$cmdline);
	switch ($mv[1]) {
	  case 'op':
	    Cmd::sysExec($ctx, $cmdline, FALSE);
	    return;
	  case 'con':
	    $msg = Cmd::console($ctx->getServer(),$cmdline,['capture']);
	    $ctx->sendMessage($msg);
	    return;
	  case 'syscon':
	    Cmd::console($ctx->getServer(),$cmdline);
	    return;
	}
      }
      Cmd::execAs($ctx, $cmdline, FALSE);
    } else {
      // No OP commands allowed
      $cmdline = preg_replace($re,'',$cmdline);
      Cmd::execAs($ctx, $cmdline, FALSE);
    }
  }
  //
  // Support and utility functions
  //
  /**
   * Declare a constant string
   *
   * @param str $name - constant to define
   * @param str $value - value to set to (NULL to delete)
   * @param bool $replace - Set to true if overriding existin definitions
   * @return bool - TRUE if succesful, FALSE if failure.
   */
  public function define($name, $value, $replace = FALSE) {
    return $this->vars->define($name, $value, $replace);
  }
  /**
   * Assign a value to a variable
   * @param str $name - constant to define
   * @param str $value - value to set to (NULL to delete)
   * @param bool $replace- Set to true if overriding existin definitions
   * @return bool - TRUE if succesful, FALSE if failure.
   */
  public function assign($name, $value, $replace = FALSE) {
    return $this->vars->assign($name, $value, $replace);
  }
  /**
   * Get value
   * @param str $name - value to retrieve
   * @param Server $server - server pointer
   * @param Player $Player|NULL - player pointer
   * @return NULL - in case of error
   */
  public function getvar($name, $player = NULL) {
    return $this->vars->get($name, $player);
  }
  /**
   * Set a hive variable
   * @param str $key - key variable
   * @param mixed $val - value
   * @return reference to value;
   */
  public function set($key, $val) {
    $this->hive[$key] =  $val;
    return $this->hive[$key];
  }
  /**
   * Get a hive variable
   * @param str $key - key variable
   * @param mixed $def - default value
   * @return reference to value;
   */
  public function get($key, $def = NULL) {
    if (!isset($this->hive[$key])) return $def;
    return $this->hive[$key];
  }
  /**
   * Unset a hive variable
   * @param str $key - key variable
   * @return previous value;
   */
  public function unset($key) {
    $ret = isset($this->hive[$key]) ? $this->hive[$key] : NULL;
    unset($this->hive[$key]);
    return $ret;
  }
}




