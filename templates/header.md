<?php
  $hdr = [];
  foreach (["name","description","depend","softdepend","api","website"] as $attr) {
    if (!isset($yaml[$attr])) {
      $hdr[$attr] = "\n";
      continue;
    }
    if (is_array($yaml[$attr])) {
      $hdr[$attr] = implode(', ', $yaml[$attr])."\n";
    } else {
      $hdr[$attr] = $yaml[$attr]."\n";
    }
  }
  foreach (["Categories"] as $attr) {
    $hdr[$attr] = (isset($meta[$attr]) ? $meta[$attr] : "N/A")."\n";
  }
  //
  // # Categories
  //
  // - Admin Tools
  // - Anti-Griefing Tools
  // - Chat Related
  // - Developer Tools
  // - Economy
  // - Fun
  // - General
  // - Informational
  // - Mechanics
  // - Miscellaneous
  // - Teleportation
  // - World Editing and Management
  // - World Generators
?>

# <?= $hdr["name"] ?>

- Summary: <?= $hdr["description"] ?>
- PocketMine-MP API version: <?= $hdr["api"] ?>
- DependencyPlugins: <?= $hdr["depend"] ?>
- OptionalPlugins: <?= $hdr["softdepend"] ?>
- Categories: <?= $hdr["Categories"] ?>
- WebSite: <?= $hdr["website"] ?>
