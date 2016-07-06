<?php
namespace mf\common;

use mf\common\IDistpatchable;

/**
 * Interface for dispatchable objects
 */
interface ISubDispatchable extends IDispatchable {
  /** Return main command's name
   *  @return str
   */
  public function getMainCmd();
}
