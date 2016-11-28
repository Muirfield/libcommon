<?php if (isset($v_forum_thread)) { ?>
**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](<?= $v_forum_thread?>) for
that.  You are more likely to get a response and help that way.
<?php } else {?>

  <!-- Add the line: -->
  <!-- php: $v_forum_thread = \"http://forums.pocketmine.net/threads/XXXX\"; -->
  
<?php } ?>

_NOTE:_ This documentation was last updated for version **<?=$yaml["version"]?>**.

<?php if (isset($yaml["website"])) {?>
Please go to
[github](<?=$yaml["website"]?>)
for the most up-to-date documentation.

You can also download this plugin from this [page](<?=$yaml["website"]?>/releases).
<?php if (!isset($v_skip_lite_explanation)) { ?>
Usually there are two types of releases, a _normal_ release (no suffix) and a _lite_
release with the suffix `-lite`.  The _lite_ release has a dependancy on 
the [libcommon](https://github.com/Muirfield/libcommon/releases) plugin, where as
the _normal_ release does not.  You only need to download **one**.

<?php } ?>

When clonning this repository make sure you use the `--recursive` option:

    git clone --recursive <?=$yaml["website"]?>.git
    
Otherwise you need to initialize sub-modules manually:

    git clone <?=$yaml["website"]?>.git
    cd <?=basename($yaml["website"]).PHP_EOL?>
    git submodule update --init --recursive


<?php } ?>

