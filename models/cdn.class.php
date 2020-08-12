<?php

if( !class_exists('FV_Player_CDN') ) :

abstract class FV_Player_CDN {
  
  var $aDomains;
  
  var $aSecureTokens;
  
  var $key = false;
  
  var $title = false;
      
  function __construct( $args ) {
    if( !empty($args['key']) ) $this->key = $args['key'];
    if( !empty($args['title']) ) $this->title = $args['title'];
    
    if( !$this->aDomains && !$this->aSecureTokens ) {
      add_action( 'admin_init', array( $this, 'register_meta_boxes' ), 20 );
    }
    add_filter( 'plugins_loaded', array( $this, 'load_options' ), 8 );
  }
  
  
  function args( $args ) {
    // add the query arg you use in URL into this array
    return $args;
  }
  
  
  function domains( $aDomains ) {
    foreach( $this->aDomains AS $sDomains ) {
      $aTemp = explode(',',$sDomains);
      foreach( $aTemp AS $sDomain ) {
        if( $sDomain ) $aDomains[] = $sDomain;
      }
    }      
    
    return $aDomains;
  }
  
  
  function get_signed_url( $url, $args, $ttl = false ) {
    $bFound = false;
    foreach( $this->aDomains AS $i => $sDomains ) {
      $aDomains = explode(',',$sDomains);
      foreach( $aDomains AS $sDomain ) {
        if( stripos($url,$sDomain) !== false ) {
          $bFound = true;


          global $fv_fp, $post;
          if( !empty($fv_fp) && method_exists($fv_fp,'current_video') && $fv_fp->current_video() ) {
            $ttl = intval( $fv_fp->current_video()->getMetaValue('duration',true ) );
          } else if( !empty($post) && !empty($post->ID) ) {
            if( $sDuration = flowplayer::get_duration( $post->ID, $url, true ) ) {
              $ttl = intval($sDuration);
            }
          }
          
          $url = $this->secure_link($url,$this->aSecureTokens[$i],$ttl);
        }          
      }
    }
    
    return $url;
  }
  
  
  function get_signed_url_long( $url ) {
    return $this->get_signed_url($url, array( 'dynamic' => true ), 172800);
  }
  
  
  function get_domains() {
    global $fv_fp;
    if( isset($fv_fp->conf['pro']) && isset($fv_fp->conf['pro'][$this->key.'_domain']) ) {
      return array( $fv_fp->conf['pro'][$this->key.'_domain'] );
    }
    return false;
  }
  
  
  function get_secure_tokens() {
    global $fv_fp;
    if( isset($fv_fp->conf['pro']) && isset($fv_fp->conf['pro'][$this->key.'_secure_token']) ) {
      return array( $fv_fp->conf['pro'][$this->key.'_secure_token'] );
    }
    return false;
  }
  
  
  function load_options() {
    global $fv_fp;
    if( empty($fv_fp) ) return;
    
    if( !$this->aDomains ) {
      $this->aDomains = $this->get_domains();
    }
    if( !$this->aSecureTokens ) {
      $this->aSecureTokens = $this->get_secure_tokens();
    }
    
    if( $this->aDomains && $this->aSecureTokens ) {
      add_filter( 'fv_player_pro_video_ajaxify_domains', array( $this, 'domains'), 999, 2 );
      add_filter( 'fv_player_pro_video_ajaxify_args', array( $this, 'args'), 999, 2 );     
      add_filter( 'fv_flowplayer_video_src', array( $this, 'get_signed_url'), 10, 2 );
      
      add_filter( 'fv_flowplayer_splash', array( $this, 'get_signed_url_long') );
      add_filter( 'fv_flowplayer_playlist_splash', array( $this, 'get_signed_url_long') );
      add_filter( 'fv_flowplayer_resource', array( $this, 'get_signed_url_long') );
    }    
  }
  
  
  function options() {
    global $fv_fp;
    ?>
    <table class="form-table2" style="margin: 5px; ">
      <tr>
        <td style="vertical-align:top"><label for="pro[<?php echo $this->key; ?>_domain]"><?php _e('Domain', 'fv-player-pro'); ?>:</label></td>
        <td>
          <input type="text" size="40" name="pro[<?php echo $this->key; ?>_domain]" id="pro[keycdn_domain]" value="<?php if( isset($fv_fp->conf['pro'][$this->key.'_domain']) && strlen(trim($fv_fp->conf['pro'][$this->key.'_domain'])) ) echo trim($fv_fp->conf['pro'][$this->key.'_domain']); ?>" />
          <p class="description"><?php _e('You can enter multiple domains separated by <code>,</code>.', 'fv-player-pro'); ?></p>
        </td>
      </tr>        
      <tr>
        <td><label for="pro[<?php echo $this->key; ?>_secure_token]"><?php _e('Secure Token', 'fv-player-pro'); ?>:</label></td>
        <td>
          <input type="text" size="40" name="pro[<?php echo $this->key; ?>_secure_token]" id="pro[<?php echo $this->key; ?>_secure_token]" value="<?php if( isset($fv_fp->conf['pro'][$this->key.'_secure_token']) && strlen(trim($fv_fp->conf['pro'][$this->key.'_secure_token'])) ) echo trim($fv_fp->conf['pro'][$this->key.'_secure_token']); ?>" />
        </td>
      </tr>
      <!--<tr>
        <td style="vertical-align:top"><label for="pro[<?php echo $this->key; ?>_fallback]"><?php _e('Fallback Domain', 'fv-player-pro'); ?>:</label></td>
        <td>
          <input type="text" size="40" name="pro[<?php echo $this->key; ?>_fallback]" id="pro[<?php echo $this->key; ?>_fallback]" value="<?php if( isset($fv_fp->conf['pro'][$this->key.'_fallback']) && strlen(trim($fv_fp->conf['pro'][$this->key.'_fallback'])) ) echo trim($fv_fp->conf['pro'][$this->key.'_fallback']); ?>" />
          <p class="description"><?php _e('Will be used for Download feature, you can use some other CDN which you have configured on this screen.', 'fv-player-pro'); ?></p>
        </td>
      </tr>-->        
      <tr>    		
        <td colspan="4">
          <input type="submit" name="fv-wp-flowplayer-submit" class="button-primary" value="<?php _e('Save All Changes', 'fv-player-pro'); ?>" style="margin-top: 2ex;"/>
        </td>
      </tr>         
    </table>
    <?php
  }
  
  
  function register_meta_boxes() {
    add_meta_box( 'fv_player_'.$this->key, $this->title, array( $this, 'options' ), 'fv_flowplayer_settings_hosting', 'normal', 'low' );
  }
  
  
  abstract function secure_link( $url, $secret, $ttl = false );

}

endif;
