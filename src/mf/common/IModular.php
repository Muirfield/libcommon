<?php
namespace mf\common;

/**
 * Interface for modular objects, objects that contain modules
 */
interface IModular {
  /**
   * @param str $name - module to look-up
   * @return NULL|IModule
   */
  public function getModule($name);
  /**
   * Get array with modules
   * @return array
   */
  public function getModules();
  /**
   * Given some defaults, this will load optional features
   *
   * @param array $mods - module definition
   * @param array $defaults - default options to use for config.yml
   * @param str $ns - namespace used to search for classes to load
   * @return array
   */
  public function modConfig($mods,$defaults);
  /**
   * Add Module
   * @param str $feature - Feature name
   * @param str|IModule $obj - Alias or IModule object
   */
  public function addModule($feature,$obj);
  /**
   * Call a module method
   * @param str $name - module
   * @param str $method - method name
   * @param array $args - arguments to use
   * @param mixed $def - return value if $name or $method are not found
   * @return mixed
   */
  public function callModule($name,$method,$args = [], $def= NULL);
}

