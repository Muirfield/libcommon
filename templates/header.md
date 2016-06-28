<?php
  $h = [];
  foreach (["name","description","depend","softdepend","api","website"] as $attr) {
    if (!isset($yaml[$attr])) {
      $h[$attr] = "\n";
      continue;
    }
    if (is_array($yaml[$attr])) {
      $h[$attr] = implode(', ', $yaml[$attr])."\n";
    } else {
      $h[$attr] = $yaml[$attr]."\n";
    }
  }
  foreach (["Categories"] as $attr) {
    $h[$attr] = (isset($meta[$attr]) ? $meta[$attr] : "N/A")."\n";
  }
?>

# <?= $h["name"] ?>

- Summary: <?= $h["description"] ?>
- PocketMine-MP version: <?= $h["api"] ?>
- DependencyPlugins: <?= $h["depend"] ?>
- OptionalPlugins: <?= $h["softdepend"] ?>
- Categories: <?= $h["Categories"] ?>
- WebSite: <?= $h["website"] ?>
