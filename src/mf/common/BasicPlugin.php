<?php
namespace mf\common;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Simple extension to the PocketMine PluginBase class
 */
abstract class BasicPlugin extends PluginBase {
  /**
   * Save a config section to the plugins' config.yml
   *
   * @param str $key - section to save
   * @param mixed $settings - settings to save
   */
  public function cfgSave($key,$settings) {
    $cfg=new Config($this->getDataFolder()."config.yml",Config::YAML);
    $dat = $cfg->getAll();
    $dat[$key] = $settings;
    $cfg->setAll($dat);
    $cfg->save();
  }
  /**
   * Gets the contents of an embedded resource on the plugin file.
   *
   * @param string $filename
   * @return string|null
   */
  public function getResourceContents($filename){
    $fp = $this->getResource($filename);
    if($fp === null) return NULL;
    $contents = stream_get_contents($fp);
    fclose($fp);
    return $contents;
  }
}
