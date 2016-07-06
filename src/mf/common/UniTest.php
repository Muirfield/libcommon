<?php
//= api-features
//: - Unit Testing functionality

namespace mf\common;
use pocketmine\Server;


/**
 * Unit testing harness
 */
abstract class UniTest {
  /**
   * Start a test
   * @param Server $server - server object
   * @param str $name - test name
   */
  static public function begin(Server $server, $name) {
    if (!is_dir($server->getDataPath().'t')) mkdir($server->getDataPath().'t');
    file_put_contents($server->getDataPath().'t/'.$name,time());
  }
  /**
   * Finish a test
   * @param Server $server - server object
   * @param str $name - test name
   */
  static public function end(Server $server, $name) {
    if (!is_file($server->getDataPath().'t/'.$name)) return;
    unlink($server->getDataPath().'t/'.$name);
  }
}
