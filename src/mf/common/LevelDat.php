<?php
//= api-features
//: - Level.dat file access

namespace mf\common;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;


/**
 * Read/Write a world's level.dat information
 */
abstract class LevelDat {
  /**
   * Read level.dat data
   *
   * @param Level $level - Level to get the data from
   * @return array - An array with key/value pairs of relevant (parsed) data
   */
  static public function getDat(Level $level) {
    $provider = $level->getProvider();
    $dat = [];
    $dat['spawn'] = $provider->getSpawn();
    $dat['seed'] = $provider->getSeed();
    $dat['name'] = $provider->getName();
    $dat['generator'] = $provider->getLevelData()->generatorName->getValue();
    $dat['preset'] = $provider->getLevelData()->generatorOptions->getValue();
    return $dat;
  }
  /**
   * Modify level.dat data
   *
   * @param Level $level - Level to get the data from
   * @param array $attrs - An array with key/value pairs of attributes to set.
   * @return bool - TRUE if data changed, FALSE if none
   */
  static public function setDat(Level $level, $attrs) {
    $provider = $level->getProvider();
    $changed = FALSE;
    foreach ($attrs as $k => $v) {
      switch ($k) {
	case 'spawn':
	  if (!($v instanceof Vector3)) {
	    if (is_array($v)) {
	      if (count($v) != 3) {
		throw new Exception("Invalid spawn array");
		return;
	      }
	      $v = new Vector3($v[0],$v[1],$v[2]);
	    } else {
	      if (!preg_match('/^(-?\d+),(-?\d+),(-?\d+)$/',$v,$mv)) {
		throw new Exception("Invalid spawn string: ".$v);
		return;
	      }
	      $v = new Vector3($mv[1],$mv[2],$mv[3]);
	    }
	  }
	  $ov = $provider->getSpawn();
	  if ($ov->getX() == $v->getX() && $ov->getY() == $v->getY() && $ov->getZ() == $v->getZ()) continue;
	  $changed = TRUE;
	  $provider->setSpawn($v);
	  break;
	case 'seed':
	  $v = intval($v);
	  if ($provider->getSeed() == $v) continue;
	  $changed = TRUE;
	  $provider->setSeed($v);
	  break;
	case 'name':
	  if ($provider->getName() == $v) continue;
	  $changed = TRUE;
	  $provider->getLevelData()->LevelName = new StringTag("LevelName",$v);
	  break;
	case 'generator':
	  if ($provider->getLevelData()->generatorName == $v) continue;
	  $changed = TRUE;
	  $provider->getLevelData()->generatorName=new StringTag("generatorName",$v);
	  break;
	case 'preset':
	  if ($provider->getLevelData()->generatorOptions == $v) continue;
	  $changed = TRUE;
	  $provider->getLevelData()->generatorOptions=new StringTag("generatorOptions",$v);
	  break;
	default:
	  throw new Exception("Unknown attribute: ".$k);
	  return;
      }
    }
    if ($changed) $provider->saveLevelData();
    return $changed;
  }
}    


