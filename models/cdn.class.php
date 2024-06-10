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
      // we use priority 21 to make sure it's after any FV_Player_Pro_Ajax_Loader::register_meta_boxes()
      add_action( 'admin_init', array( $this, 'register_meta_boxes' ), 21 );
    }
    add_filter( 'plugins_loaded', array( $this, 'load_options' ), 8 );
    add_action( 'fv_player_shortinit_loaded', array( $this, 'load_options' ) );
  }


  /*
   * Used by FV Player Pro
   */
  function ajax() {
    if( isset($_POST['action']) && sanitize_key( $_POST['action'] ) == 'fv_fp_get_video_url' ) {
      $bFound = false;
      foreach( $this->aDomains AS $i => $sDomains ) {
        $aDomains = explode(',',$sDomains);
        foreach( $aDomains AS $sDomain ) {
          foreach( $_POST['sources'] AS $key => $aVideo ) {
            if( !isset($aVideo['src']) || !isset($aVideo['type']) ) continue;

            if( stripos($aVideo['src'],$sDomain) !== false ) {
              $bFound = true;
              $aVideo['src'] = $this->secure_link($aVideo['src'],$this->aSecureTokens[$i]);
              $_POST['sources'][$key] = $aVideo;
            }
          }
        }
      }

      if( $bFound ) {
        echo '<FVFLOWPLAYER>';
        // TODO: Sanitize
        echo wp_json_encode($_POST['sources']);
        echo '</FVFLOWPLAYER>';
        die();
      }
    }

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
    // either there is no FV_Player_Pro_Ajax_Loader from FV Player Pro or it's a back-end request for signed file URL
    if( !class_exists('FV_Player_Pro_Ajax_Loader') || is_array($args) && isset($args['dynamic']) && $args['dynamic'] ) {
      $bFound = false;
      foreach( $this->aDomains AS $i => $sDomains ) {
        $aDomains = explode(',',$sDomains);
        foreach( $aDomains AS $sDomain ) {
          if( stripos($url,$sDomain) !== false ) {

            // Use video duration as the TTL if not provided
            if ( ! $ttl ) {
              global $fv_fp, $post;
              if( !empty($fv_fp) && method_exists($fv_fp,'current_video') && $fv_fp->current_video() ) {
                $ttl = intval( $fv_fp->current_video()->getDuration() );
              } else if( !empty($post) && !empty($post->ID) ) {
                if( $sDuration = flowplayer::get_duration( $post->ID, $url, true ) ) {
                  $ttl = intval($sDuration);
                }
              }
            }

            $secureToken = ! empty( $this->aSecureTokens[ $i ] ) ? $this->aSecureTokens[ $i ] : false;

            $url = $this->secure_link( $url, $secureToken ,$ttl );
          }
        }
      }
    }

    return $url;
  }


  function get_signed_url_long( $url ) {
    // 1 week is the maximum of AWS S3 signed URLs: https://docs.aws.amazon.com/AmazonS3/latest/userguide/using-presigned-url.html#PresignedUrl-Expiration
    return $this->get_signed_url( $url, array( 'dynamic' => true ), WEEK_IN_SECONDS );
  }


  function get_domains() {
    global $fv_fp;
    if( isset($fv_fp->conf[$this->key]) && isset($fv_fp->conf[$this->key]['domain']) ) {
      return array( $fv_fp->conf[$this->key]['domain'] );
    }
    return false;
  }


  function get_secure_tokens() {
    global $fv_fp;
    if( isset($fv_fp->conf[$this->key]) && isset($fv_fp->conf[$this->key]['secure_token']) ) {
      return array( $fv_fp->conf[$this->key]['secure_token'] );
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

      add_action( 'plugins_loaded', array( $this, 'ajax' ), 9 );

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
        <td style="vertical-align:top"><label for="<?php echo esc_attr( $this->key ); ?>[domain]"><?php esc_html_e( 'Domain', 'fv-player' ); ?>:</label></td>
        <td>
          <input type="text" size="40" name="<?php echo esc_attr( $this->key ); ?>[domain]" id="<?php echo esc_attr( $this->key ); ?>[keycdn_domain]" value="<?php if( isset($fv_fp->conf[$this->key][$this->key.'_domain']) && strlen(trim($fv_fp->conf[$this->key]['domain'])) ) echo esc_attr( trim($fv_fp->conf[$this->key]['domain']) ); ?>" />
          <p class="description"><?php esc_html_e( 'You can enter multiple domains separated by <code>,</code>.', 'fv-player' ); ?></p>
        </td>
      </tr>
      <tr>
        <td><label for="<?php echo esc_attr( $this->key ); ?>[secure_token]"><?php esc_html_e( 'Secure Token', 'fv-player' ); ?>:</label></td>
        <td>
          <input type="text" size="40" name="<?php echo esc_attr( $this->key ); ?>[secure_token]" id="<?php echo esc_attr( $this->key ); ?>[secure_token]" value="<?php if( isset($fv_fp->conf[$this->key][$this->key.'_secure_token']) && strlen(trim($fv_fp->conf[$this->key]['secure_token'])) ) echo esc_attr( trim($fv_fp->conf[$this->key]['secure_token']) ); ?>" />
        </td>
      </tr>
      <tr>
        <td colspan="4">
          <a class="fv-wordpress-flowplayer-save button button-primary" href="#" style="margin-top: 2ex;"><?php esc_html_e( 'Save', 'fv-player' ); ?></a>
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
