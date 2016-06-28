<?php
//= api-features
//: - Variable expansions
namespace mf\common;

use mf\common\Singleton;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Basic prepared variable string
 *
 * Implements constant strings
 */
class BaseStrVar {
  /** @var $str - string constant */
  public $str;
  public function __construct($str) {
    $this->str = $str;
  }
  /**
   * Returns the string value of this object
   *
   * @param Server $server - kept for compatibility (unused)
   * @param NULL|Player $player - kept for compatibility (unused)
   */
  public function asString(Server $server, $player = NULL) {
    return $this->str;
  }
}
/**
 * Dynamic prepared variable strings
 *
 * Handles on the fly variable expansions.
 *
 * Note that it repurposes $str from parent as a PHP callable.
 */
class DynamicStrVar extends BaseStrVar {
  /**
   * Returns the string value of this object
   *
   * @param Server $server - server instance
   * @param NULL|Player $player - current player instance
   */
  public function asString(Server $server, $player = NULL) {
    $callable = $this->str;
    return $callable($server,$player,$server->getPluginManager());
  }
}

/**
 * Common variable expansion.  You can use this for custom messages and/or
 * custom commands.
 *
 * Plugins can extend this infrastructure calling 
 *
 * - $vars = ExpandVars::getVars($server);
 * - $vars->define("name","definition");
 * - $vars->assign("name","PHP expression")
 *
 * For variables, the following variables are available in the PHP expression context:
 * - $server : Server object
 * - $player : Player object
 * - $plugins : PluginManager object
 */
class ExpandVars {
  /** @var str Tagged API for singleton use... i.e. in case of multiple versions of this class */
  const API = '1.0';
  /** @var str Tagged name for singleton use */
  const INSTANCE_ID = 'mf\common\ExpandVars';
  
  /** @var str[] List of constant definitions strings */
  protected $constStr;
  /** @var callable[] List of subst as PHP callables */
  protected $varPhp;
  /**
   * @param Server $server - server context
   */
  public function __construct(Server $owner) {
    $this->constStr = [];
    $this->varPhp = [];

    $plugins = $owner->getPluginManager();

    $this->assign('player',self::class.'::getPlayerName($player)');
    $this->assign('displayName',self::class.'::getPlayerDisplayName($player)');
    $this->assign('world',self::class.'::getPlayerWorld($player)');
    $this->assign('x', self::class.'::getPlayerX($player)');
    $this->assign('y', self::class.'::getPlayerY($player)');
    $this->assign('z', self::class.'::getPlayerZ($player)');
    $this->assign('yaw',self::class.'::getPlayerYaw($player)');
    $this->assign('pitch',self::class.'::getPlayerPitch($player)');
    $this->assign('bearing',self::class.'::getPlayerBearing($player)');

    if ($plugins->getPlugin("PurePerms")) $this->assign('group',self::class.'::getPurePermsGroup($player)');
    if ($plugins->getPlugin("GoldStd")) {
      $this->assign("money", self::class.'::getGoldStdMoney($player)');
    } elseif ($plugins->getPlugin("PocketMoney")) {
      $this->assign("money", self::class.'::getGenericPluginMoney($player,"PocketMoney")');
    } elseif ($plugins->getPlugin("MassiveEconomy")) {
      $this->assign("money", self::class.'::getGenericPluginMoney($player,"MassiveEconomy")');
    } elseif ($plugins->getPlugin("EconomyAPI")) {
      $this->assign("money", self::class.'::getEconomyAPIMoney($player)');
    }
    if ($plugins->getPlugin("RankUp"))  $this->assign("rank", self::class.'::getRankUpRank($player)');
    $this->assign('tps','$server->getTicksPerSecond()');
    $this->assign('tickUsage','$server->getTickUsage()');
    $this->assign('numPlayers','count($server->getOnelinePlayers())');
    $this->assign('uptimeSecs','floor(microtime(true) - \pocketmine\START_TIME)');
    $this->assign('uptime',self::class.'::duration_format(floor(microtime(true)-\pocketmine\START_TIME))');
    $this->assign('netup','round($server->getNetwork()->getUpload()/1024,2)');
    $this->assign('netdown','round($server->getNetwork()->getDownload()/1024,2)');
    $this->assign('mainmem',self::class.'::memUsage(0)');
    $this->assign('usedmem',self::class.'::memUsage(1)');
    $this->assign('maxmem',self::class.'::memUsage(2)');
  
    $this->define("NL", "\n");
    $this->define("BLACK",TextFormat::BLACK);
    $this->define("DARK_BLUE",TextFormat::DARK_BLUE);
    $this->define("DARK_GREEN",TextFormat::DARK_GREEN);
    $this->define("DARK_AQUA",TextFormat::DARK_AQUA);
    $this->define("DARK_RED", TextFormat::DARK_RED);
    $this->define("DARK_PURPLE", TextFormat::DARK_PURPLE);
    $this->define("GOLD", TextFormat::GOLD);
    $this->define("GRAY", TextFormat::GRAY);
    $this->define("DARK_GRAY", TextFormat::DARK_GRAY);
    $this->define("BLUE", TextFormat::BLUE);
    $this->define("GREEN", TextFormat::GREEN);
    $this->define("AQUA", TextFormat::AQUA);
    $this->define("RED", TextFormat::RED);
    $this->define("LIGHT_PURPLE", TextFormat::LIGHT_PURPLE);
    $this->define("YELLOW", TextFormat::YELLOW);
    $this->define("WHITE", TextFormat::WHITE);
    $this->define("OBFUSCATED", TextFormat::OBFUSCATED);
    $this->define("BOLD", TextFormat::BOLD);
    $this->define("STRIKETHROUGH", TextFormat::STRIKETHROUGH);
    $this->define("UNDERLINE", TextFormat::UNDERLINE);
    $this->define("ITALIC", TextFormat::ITALIC);
    $this->define("RESET", TextFormat::RESET);
    $this->define("10SPACE", str_repeat(" ",10));
    $this->define("20SPACE", str_repeat(" ",20));
    $this->define("30SPACE", str_repeat(" ",30));
    $this->define("40SPACE", str_repeat(" ",40));
    $this->define("50SPACE", str_repeat(" ",50));
  }

  /**
   * Declare a constant string
   *
   * @param str $name - constant to define
   * @param str $value - value to set to (NULL to delete)
   * @param bool $replace - Set to true if overriding existin definitions
   * @return bool - TRUE if succesful, FALSE if failure.
   */
  public function define($name,$value,$replace = FALSE) {
    $key = '{'.$name.'}';
    if ($value === NULL) {
      if (isset($this->constStr[$key])) unset($this->constStr[$key]);
      return TRUE;
    }
    if (isset($this->constStr[$key])) {
      if (!$replace) return FALSE;
    } elseif (isset($this->varPhp[$key])) {
      if (!$replace) return FALSE;
      unset($this->varPhp[$key]);
    }
    
    $this->constStr[$key] = $value;
    return TRUE;
  }
  /**
   * Assign a value to a variable
   * @param str $name - constant to define
   * @param str $value - value to set to (NULL to delete)
   * @param bool $replace- Set to true if overriding existin definitions
   * @return bool - TRUE if succesful, FALSE if failure.
   */
  public function assign($name,$value,$replace = FALSE) {
    $key = '{'.$name.'}';
    if ($value === NULL) {
      if (isset($this->varPhp[$key])) unset($this->varPhp[$key]);
      return TRUE;
    }
    if (isset($this->varPhp[$key])) {
      if (!$replace) return FALSE;
    } elseif (isset($this->constStr[$key])) {
      if (!$replace) return FALSE;
      unset($this->constStr[$key]);
    }    
    $this->varPhp[$key] = '('.$value.')';
    return TRUE;    
  }
  /**
   * Get value
   * @param str $name - value to retrieve
   * @param Server $server - server pointer
   * @param Player $Player|NULL - player pointer
   * @return NULL - in case of error
   */
  public function get($name, $player = NULL) {
    $key = '{'.$name.'}';
    if (isset($this->constStr[$key])) return $this->constStr[$key];
    if (isset($this->varPhp[$key])) return eval('return '.$this->varPHP[$key].';');
    return NULL;
  }
  /**
   * Converts $str into a string value suitable for evaluation or use in a PHP script.
   * @param str $str - string to convert
   * @return str - converted string
   */
  public function phpfy($str) {
    $merged = [];
    foreach ($this->varPhp as $a => $b) {
      $merged[$a] = "'.".$b.".'";
    }
    foreach ($this->constStr as $a=>$b) {
      $merged[$a] = str_replace("'","\\'",$b);
    }
    //echo 'PHPIFY'."'".strtr($str,$merged).'"'.PHP_EOL;//##DEBUG
    return "'".strtr($str,$merged)."'";
  }
  /**
   * Prepares a $str converting into an object that can be used for variable expansion
   * @param str $str - string to prepare
   * @param BaseStrVar|DynamicStrVar - string object
   */
  public function prepare($intxt) {
    // Check if it contains function calls...
    //echo "INTXT=$intxt\n";//##DEBUG
    //echo "CHECKED=".strtr($intxt,$this->varPhp).PHP_EOL;//##DEBUG
    if (strtr($intxt,$this->varPhp) == $intxt) {
      // No, Just constants...
      $ctxt = strtr($intxt,$this->constStr);
      return new BaseStrVar($ctxt);
    }
    $fn = '';
    $fn .= 'return function ($server,$player,$plugins) { return ';
    $fn .= $this->phpfy($intxt);
    $fn .= ';};';
    //echo "CODE: $fn\n";//##DEBUG
    return new DynamicStrVar(eval($fn));
  }
  /**
   * Given $str, it will perform any variable substitutions.
   * @param str $str - string to process
   * @param Server $server - server instance
   * @param Player $player|NULL - player instance
   * @return str - expanded string
   */
  public function expand($str,Server $server,$player = NULL) {
    $obj = $this->prepare($str);
    return $obj->asString($server,$player);
  }
  /**
   * Return a server wide instance...
   */
  static public function getVars(Server $owner) {
    $inst = Singleton::getInstance(self::INSTANCE_ID,self::API);
    if ($inst === NULL) {
      $inst = new ExpandVars($owner);
      Singleton::setInstance(self::INSTANCE_ID,$inst,self::API);
    }
    return $inst;
  }
   
  ///////////////////////////////////////////////////////////////////////////
  // Misc Support functions
  ///////////////////////////////////////////////////////////////////////////
  
  /**
   * Support function: Given a duration in seconds, create a "Human readable" version
   * @param int $time - value in seconds to convert
   * @return str - human readable version of seconds
   */
  static public function duration_format($time) {
    $q = "";
    $uptime =  "";
    foreach ([
      [ "sec", 60, "secs"],
      [ "min", 60,  "mins"],
      [ "hour", 24, "hours"],
      [ "day", 0, "days"],
    ] as $f) {
      if ($f[1]) {
	$e = floor($time % $f[1]);
	$time = floor($time / $f[1]);
      } else {
	$e = $time;
	$time = 0;
      }
      if ($e) {
	$r = $e == 1 ? $f[0] : $f[2];
	$uptime = $e." ".$r . $q . $uptime;
	$q = ", ";
      }
      if ($time == 0) break;
    }
    return $uptime;
  }
  /**
   * Support Function: return a value from Utils::getMemoryUsage
   * @param int $value - value to retrieve
   * @return int - value in bytes
   */
  static public function memUsageBytes($value) {
    $mUsage = Utils::getMemoryUsage(true);
    return $mUsage[$value];
  }
  /**
   * Support Function: return a value from Utils::getMemoryUsage in MBs and formatted
   * @param int $value - value to retrieve
   * @return int - value in MBs
   */
  static public function memUsage($value) {
    $mUsage = Utils::getMemoryUsage(true);
    return number_format(round($mUsage[$value]/1024)/1024,2);
  }

  /**
   * Support Function: Convert bearings in degrees into points in compass
   * @param float $deg - yaw
   * @return str
   */
  static public function bearing($deg) {
    // Determine bearing
    if (22.5 <= $deg && $deg < 67.5) {
      return "NW";
    } elseif (67.5 <= $deg && $deg < 112.5) {
      return "N";
    } elseif (112.5 <= $deg && $deg < 157.5) {
      return "NE";
    } elseif (157.5 <= $deg && $deg < 202.5) {
      return "E";
    } elseif (202.5 <= $deg && $deg < 247.5) {
      return "SE";
    } elseif (247.5 <= $deg && $deg < 292.5) {
      return "S";
    } elseif (292.5 <= $deg && $deg < 337.5) {
      return "SW";
    } else {
      return "W";
    }
    return (int)$deg;
  }
  /**
   * Support Function: Get the PurePerms Group of Player
   * @param Player $player
   * @return str - value
   */
  static public function getPurePermsGroup($player) {
    if (!($player instanceof Player)) return "";
    $pperms = $player->getServer()->getPluginManager()->getPlugin("PurePerms");
    if ($pperms == NULL) return "";
    if (!$pperms->isEnabled()) return "";
    return $pperms->getUser($player)->getGroup();
  }
  /**
   * Support Function: Get the GoldStd balance of player
   * @param Player $player
   * @return str - value
   */
  static public function getGoldStdMoney($player) {
    if (!($player instanceof Player)) return "";
    $econ = $player->getServer()->getPluginManager()->getPlugin("GoldStd");
    if ($econ == NULL) return "";
    if (!$econ>isEnabled()) return "";
    return number_format($econ->getMoney($player));
  }
  /**
   * Support Function: Get the PocketMoney|MassiveEconomy balance of player
   * @param Player $player
   * @param str $plugname - Should be PocketMoney or MassiveEconomy
   * @return str - value
   */
  static public function getGenericPluginMoney($player,$plugname) {
    if (!($player instanceof Player)) return "";
    $econ = $player->getServer()->getPluginManager()->getPlugin($plugname);
    if ($econ == NULL) return "";
    if (!$econ>isEnabled()) return "";
    return number_format($econ->getMoney($player->getName()));
  }
  /**
   * Support Function: Get the EconomyAPI balance of player
   * @param Player $player
   * @return str - value
   */
  static public function getEconomyAPIMoney($player) {
    if (!($player instanceof Player)) return "";
    $econ = $player->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    if ($econ == NULL) return "";
    if (!$econ>isEnabled()) return "";
    return number_format($econ->mymoney($player->getName()),2);
  }
  /**
   * Support Function: Get the Rank of player
   * @param Player $player
   * @return str - value
   */
  static public function getRankUpRank($player) {
    if (!($player instanceof Player)) return "";
    $rank = $player->getServer()->getPluginManager()->getPlugin("RankUp");
    if ($rank == NULL) return "";
    if (!$rank->isEnabled()) return "";
    return $rank->getPermManager()->getGroup($player);
  }
  /**
   * Support Function: Get player's name
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerName($player) {
    if (!($player instanceof Player)) return "";
    return $player->getName();
  }
  /**
   * Support Function: Get player's **display** name
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerDisplayName($player) {
    if (!($player instanceof Player)) return "";
    return $player->getDisplayName();
  }
  /**
   * Support Function: Get player's world name
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerWorld($player) {
    if (!($player instanceof Player)) return "";
    return $player->getLevel()->getName();
  }
  /**
   * Support Function: Get player's X coordinate
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerX($player) {
    if (!($player instanceof Player)) return "";
    return (int)$player->getX();
  }
  /**
   * Support Function: Get player's Y coordinate
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerY($player) {
    if (!($player instanceof Player)) return "";
    return (int)$player->getY();
  }
  /**
   * Support Function: Get player's Z coordinate
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerZ($player) {
    if (!($player instanceof Player)) return "";
    return (int)$player->getZ();
  }
  /**
   * Support Function: Get player's Yaw
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerYaw($player) {
    if (!($player instanceof Player)) return "";
    return (int)$player->getYaw();
  }
  /**
   * Support Function: Get player's Pitch
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerPitch($player) {
    if (!($player instanceof Player)) return "";
    return (int)$player->getPitch();
  }
  /**
   * Support Function: Get player's Bearing as point in compass
   * @param Player $player
   * @return str - value
   */
  static public function getPlayerBearing($player) {
    if (!($player instanceof Player)) return "";
    return self::bearing($player->getYaw());
  }

}
