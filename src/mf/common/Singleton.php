<?php
namespace mf\common;

abstract class Singleton {
  static public $inst = NULL;
  static public function initInstance() {
    if (self::$inst !== NULL) return; 
    if (!class_exists('\mf\any\common\SingletonContainer',false)) {
      eval('
	namespace mf\any\common;
	  abstract class SingletonContainer {
	    static public $inst = NULL;
	    static public function &getContainer() {
	      if (self::$inst === NULL) self::$inst = [];
	      return self::$inst;
	    }
	  }
	
	');
    }
    self::$inst = \mf\any\common\SingletonContainer::getContainer();
  }
  static public function &getInstance($name,$api = '') {
    self::initInstance();
    $key = $name.':'.$api;
    if (!isset(self::$inst[$key])) self::$inst[$key] = NULL;
    return self::$inst[$key];
  }
  static public function setInstance($name,&$value, $api = '') {
    self::initInstance();
    $key = $name.':'.$api;
    self::$inst[$key] = &$value;
  }
}
