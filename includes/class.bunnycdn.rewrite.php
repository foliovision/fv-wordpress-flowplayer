<?php

if( !class_exists('FV_Player_BunnyCDN_Rewrite') ) {

  class FV_Player_BunnyCDN_Rewrite extends BunnyCDNFilter {
    public function rewrite_url( $url ) {
      $directoriesRegex = implode('|', $this->directories);
      $regex = '~(?:'. quotemeta($this->baseUrl) .')?/((?:'.$directoriesRegex.').*)~'; // custom regex to match url
      return preg_replace_callback($regex, array(&$this, "rewriteUrl"), $url);
    }
  }

}
