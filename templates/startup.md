<?php
$meta['github'] = 'http://github.com/';
if (file_exists('.git/config')) {
  $data = file_get_contents('.git/config');
  if (preg_match('/\n\s*url\s*=\s*(https?:\/\/github.com\/\S+)/',$data,$mv)) {
    $meta['github'] = preg_replace('/\.git$/','',$mv[1]);
    //fwrite(STDERR,'GITHUB: '.$meta['github'].PHP_EOL);
  }
}
  