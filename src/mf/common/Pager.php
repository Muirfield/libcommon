<?php
//= api-features
//: - Paginated output
namespace mf\common;
use mf\common\mc;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

abstract class Pager {
  /** @var int - Do not paginate output */
  const DONT_PAGINATE = 0;
  /** @var int - Automatically determine page height */
  const AUTO_PAGINATE = -1;
  /** @var int - The default page length */
  const DEFAULT_PAGEHEIGHT = 5;
  /**
   * Use for paginaged output implementation.
   * This gets the player specified page number that we want to Display
   *
   * @param str[] $args - Passed arguments
   * @return int page number
   */
  static public function getPageNumber(array &$args) {
    $pageNumber = 1;
    if (count($args) && is_numeric($args[count($args)-1])) {
      $pageNumber = (int)array_pop($args);
      if($pageNumber <= 0) $pageNumber = 1;
    }
    return $pageNumber;
  }
  /**
   * Use for paginaged output implementation.
   * Shows a bunch of line in paginated output.
   *
   * @param CommandSender $sender - entity that we need to display text to
   * @param int $pageNumber - page that we need to display
   * @param str[] $txt - Array containing one element per output line
   * @param int $pageHeight - number of lines to display in a page, 0 unlimited, -1 autoconfig
   * @return bool true
   */
  static public function paginateText(CommandSender $sender,$pageNumber,array $txt, $pageHeight = self::AUTO_PAGINATE) {
    $hdr = array_shift($txt);
    $lineCount = count($txt);
    if ($pageHeight == self::AUTO_PAGINATE)
      $pageHeight = ($sender instanceof ConsoleCommandSender) ? self::DONT_PAGINATE : self::DEFAULT_PAGEHEIGHT;
    if ($pageHeight == self::DONT_PAGINATE) {
      $sender->sendMessage( TextFormat::GREEN.$hdr.TextFormat::RESET);
      foreach ($txt as $ln) $sender->sendMessage($ln);
      return TRUE;
    }

    $pageCount = intval($lineCount/$pageHeight) + ($lineCount % $pageHeight ? 1 : 0);
    $hdr = TextFormat::GREEN.$hdr. TextFormat::RESET;
    if ($pageNumber > $pageCount) {
      $sender->sendMessage($hdr);
      $sender->sendMessage(TextFormat::RED.mc::_("Only %1% pages available",$pageCount).TextFormat::RESET);
      return TRUE;
    }
    $hdr .= TextFormat::RED.mc::_(" (%1% of %2%)", $pageNumber, $pageCount);
    $sender->sendMessage($hdr);
    for ($ln = ($pageNumber-1)*$pageHeight;$ln < $lineCount && $pageHeight--;++$ln) {
      $sender->sendMessage($txt[$ln]);
    }
    return TRUE;
  }
  /**
   * Use for paginaged output implementation.
   * Formats and paginates a table
   *
   * @param CommandSender $sender - entity that we need to display text to
   * @param int $pageNumber - page that we need to display
   * @param str[][] $txt - Array containing one element per cell
   * @param int $pageHeight - number of lines to display in a page
   * @return bool true
   */
  static public function paginateTable(CommandSender $sender,$pageNumber,array $tab, $pageHeight = self::AUTO_PAGINATE) {
    $cols = [];
    for($i=0;$i < count($tab[0]);$i++) $cols[$i] = strlen($tab[0][$i]);
    foreach ($tab as $row) {
      for($i=0;$i < count($row);$i++) {
	if (($l=strlen($row[$i])) > $cols[$i]) $cols[$i] = $l;
      }
    }
    $txt = [];
    $fmt = "";
    foreach ($cols as $c) {
      if (strlen($fmt) > 0) $fmt .= " ";
      $fmt .= "%-".$c."s";
    }
    //echo "$fmt ".count($cols)."\n";//##DEBUG
    
    foreach ($tab as $row) {
      //echo "$fmt ".count($row)."\n";//##DEBUG
      $txt[] = sprintf($fmt,...$row);
    }
    return self::paginateText($sender,$pageNumber,$txt,$pageHeight);
  }
}
