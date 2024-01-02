<?php

class FV_Player_SEO {
  
  var $can_seo = false;
  
  public function __construct() {

    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    add_filter('fv_flowplayer_args_pre', array($this, 'should_i'), 10, 3 );
    add_filter('fv_flowplayer_attributes', array($this, 'single_attributes'), 10, 3 );
    add_filter('fv_flowplayer_inner_html', array($this, 'single_video_seo'), 10, 2 );
    add_filter('fv_player_item_html', array($this, 'playlist_video_seo'), 10, 7 );

  }
  
  function single_attributes( $attributes, $media, $fv_fp ) {
    if( !empty($fv_fp->aCurArgs['playlist']) || $this->video_ads_active($fv_fp->aCurArgs)  || !$this->can_seo ) {
      return $attributes;
    }
    
    $attributes['itemprop'] = 'video';
    $attributes['itemscope'] = '';
    $attributes['itemtype'] = 'http://schema.org/VideoObject';
    
    return $attributes;
  }
  
  function video_ads_active($args) {
    $conf = get_option( 'fvwpflowplayer' );

    // check globals ads
    if(!empty($conf['pro']['video_ads_default'])) {
      if( $conf['pro']['video_ads_default'] != 'no') return true;
    } 

    if( !empty($conf['pro']['video_ads_postroll_default']) ) {
      if( $conf['pro']['video_ads_postroll_default'] != 'no' ) return true;
    }

    // check meta ads
    if( !empty($args['preroll']) ) {
      if( $args['preroll'] != 'no' ) return true;
    }

    if( !empty($args['postroll']) ) {
      if( $args['postroll'] != 'no' ) return true;
    }

    return false;
  }

  function get_markup( $args ) {

    $args = wp_parse_args( $args, array(
      'title' => false,
      'description' => false,
      'splash' => false,
      'url' => false,
      'duration' => false
    ) );

    extract($args);

    if( !$title ) {
      $title = get_the_title();
    }
    
    if( !$description ) { //  todo: read this from shortcode
      $description = get_post_meta(get_the_ID(),'_aioseop_description', true );
    }
    $post_content = get_the_content();
    if( !$description && strlen($post_content) > 0 ) {
      $post_content = strip_shortcodes( $post_content );
      $post_content = strip_tags( $post_content );
      $description = wp_trim_words( $post_content, 30 );
    }
    if( !$description ) {
      $description = get_option('blogdescription');
    }
    
    if( !$url ) {
      $url = get_permalink();
    }
    
    if( stripos($splash,'://') === false ) {
      $splash = home_url($splash);
    }

    $schema_tags = '<meta itemprop="name" content="'.esc_attr($title).'" />
        <meta itemprop="description" content="'.esc_attr($description).'" />
        <meta itemprop="thumbnailUrl" content="'.esc_attr($splash).'" />
        <meta itemprop="contentURL" content="'.esc_attr($url).'" />
        <meta itemprop="uploadDate" content="'.esc_attr(get_the_modified_date('c')).'" />';

    if( $duration ) {
      $duration = self::time_to_iso8601_duration($duration);
      $schema_tags .= "\n".'        <meta itemprop="duration" content="'.esc_attr($duration).'" />';
    }

    return $schema_tags;
  }
  
  function playlist_video_seo( $sHTML, $aArgs, $sSplashImage, $sItemCaption, $aPlayer, $index, $tDuration ) {
    if( $this->can_seo ) {
      $sHTML = str_replace( '<a', '<a itemprop="video" itemscope itemtype="http://schema.org/VideoObject" ', $sHTML );

      $args = array(
        'title' => $sItemCaption,
        'splash' => $sSplashImage,
      );

      if( $tDuration ) {
        $args['duration'] = $tDuration;
      }

      $sHTML = str_replace( '</a>', $this->get_markup( $args ).'</a>', $sHTML );
    }
    return $sHTML;
  }
  
  function should_i( $args ) {
    global $fv_fp;
    if( !$fv_fp->_get_option( array( 'integrations', 'schema_org' ) ) ) {
      $this->can_seo = false;
      return $args;
    }
    
    if( !get_permalink() || !$fv_fp->get_splash() ) {
      $this->can_seo = false;
    }
    
    $dynamic_domains = apply_filters('fv_player_pro_video_ajaxify_domains', array());
    $amazon = $fv_fp->_get_option('amazon_bucket');
    if( $amazon && is_array($amazon) && count($amazon) > 0 ) {
      foreach( $amazon AS $bucket ) {
        $dynamic_domains[] = 'amazonaws.com/'.$bucket.'/';
        $dynamic_domains[] = '//'.$bucket.'.s3';
      }      
    }
    
    $cf = $fv_fp->_get_option( array('pro','cf_domain') );
    if( $cf ) {
      $cf = explode( ',', $cf );
      if( is_array($cf) && count($cf) > 0 ) {
        foreach( $cf AS $cf_domain ) {
          $dynamic_domains[] = $cf_domain;
        }
      }
    }  
    
    if( count($dynamic_domains) ) {
      $is_dynamic = false;
      foreach( $dynamic_domains AS $domain ) {
        if( stripos($args['src'],$domain) !== false ) {
          $this->can_seo = false;
          return $args;
        }
      }
    }
    
    $this->can_seo = true;
    return $args;
  }
  
  function single_video_seo( $html, $fv_fp ) {
    if( !empty($fv_fp->aCurArgs['playlist']) || $this->video_ads_active($fv_fp->aCurArgs)  || !$this->can_seo ) {
      return $html;
    }

    // todo: use iframe or video link URL
    $args = array(
      'splash' => $fv_fp->get_splash()
    );

    if( !empty($fv_fp->aCurArgs['caption']) ) {
      $args['title'] = $fv_fp->aCurArgs['caption'];
    }

    if( $fv_fp->current_video() ) {
      $args['duration'] = $fv_fp->current_video()->getDuration();
    }

    $html .= "\n".$this->get_markup( $args )."\n";

    return $html;
  }

  public static function time_to_iso8601_duration($time) {
    $units = array(
      "Y" => 365*24*3600,
      "D" =>     24*3600,
      "H" =>        3600,
      "M" =>          60,
      "S" =>           1,
    );

    $str = "P";
    $istime = false;

    foreach ($units as $unitName => &$unit) {
      $quot  = intval($time / $unit);
      $time -= $quot * $unit;
      $unit  = $quot;
      if ($unit > 0) {
        if (!$istime && in_array($unitName, array("H", "M", "S"))) {
          $str .= "T";
          $istime = true;
        }
        $str .= strval($unit) . $unitName;
      }
    }

    return $str;
  }

}

$FV_Player_SEO = new FV_Player_SEO();
