<?php

if( class_exists('BunnyCDNFilter') && !class_exists('FV_Player_BunnyCDN_Rewrite') ) {

  class FV_Player_BunnyCDN_Rewrite extends BunnyCDNFilter {
    public function rewrite_url( $url ) {
      return $this->rewrite($url);
    }
  }


}
