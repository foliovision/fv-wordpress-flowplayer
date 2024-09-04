<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FV_Player_Subtitles {

  /**
   * List of RTL languages
   * @see https://meta.wikimedia.org/wiki/Template:List_of_language_names_ordered_by_code
   *
   * @var array
   */
  public $aRtlSubtitles = array(
    'ar', 'arc', 'arz', 'ckb', 'dv', 'fa', 'ha', 'he', 'khw', 'ks', 'ku', 'ps', 'sd', 'uz_af', 'yi'
  );

  public function __construct() {
    add_filter('fv_player_item', array($this, 'add_subtitles'), 10, 3 );
  }

  function add_subtitles( $aItem, $index ) {
    global $fv_fp;

    $aSubtitles = $this->get_subtitles($index);
    if( count($aSubtitles) == 0 ) return $aItem;

    $aLangs = flowplayer::get_languages();
    $countSubtitles = 0;
    $aOutput = array();

    foreach( $aSubtitles AS $key => $subtitles ) {
      if( $key == 'iw' ) $key = 'he';
      if( $key == 'in' ) $key = 'id';
      if( $key == 'jw' ) $key = 'jv';
      if( $key == 'mo' ) $key = 'ro';
      if( $key == 'sh' ) $key = 'sr';

      $objSubtitle = new stdClass;
      if( $key == 'subtitles' ) {
        $aLang = explode('-', get_bloginfo('language'));
        if( !empty($aLang[0]) ) $objSubtitle->srclang = $aLang[0];
        $sCode = $aLang[0];

        $sCaption = '';
        if( !empty($sCode) && $sCode == 'en' ) {
          $sCaption = 'English';

        } else if( !empty($sCode) ) {
          $translations = get_site_transient( 'available_translations' );
          $sLangCode = str_replace( '-', '_', get_bloginfo('language') );
          if( $translations && isset($translations[$sLangCode]) && !empty($translations[$sLangCode]['native_name']) ) {
            $sCaption = $translations[$sLangCode]['native_name'];
          }

        }

        if( $sCaption ) {
          $objSubtitle->label = $sCaption;
        }

      } else {
        $objSubtitle->srclang = $key;
        $objSubtitle->label = $aLangs[strtoupper($key)];
      }

      if( in_array( strtolower($key), $this->aRtlSubtitles) ) {
        $objSubtitle->rtl = true;
      }

      $objSubtitle->src = $subtitles;
      // default subtitle
      if( $countSubtitles == 0 && $fv_fp->_get_option('subtitleOn') ) {
        $objSubtitle->fv_default = true;
      }

      $aOutput[] = $objSubtitle;

      $countSubtitles++;
    }

    if( count($aSubtitles) ) {
      $aItem['subtitles'] = $aOutput;
    }

    return $aItem;
  }

  function get_subtitles($index = 0) {
    global $fv_fp;

    $aSubtitles = array();

    // each video can have subtitles in any language with new DB-based shortcodes
    if ( $fv_fp->current_video()) {
      if ( $fv_fp->current_video()->getMetaData()) {
        foreach ( $fv_fp->current_video()->getMetaData() as $meta_object) {
          if (strpos($meta_object->getMetaKey(), 'subtitles') !== false) {
            // subtitles meta data found, create URL from it
            // note: we ignore $index here, as it's used to determine an index
            //       for a single subtitle file from all subtitles set for the whole
            //       playlist, which was the old way of doing stuff
            $aSubtitles[str_replace( 'subtitles_', '', $meta_object->getMetaKey() )] = $this->get_subtitles_url( $meta_object->getMetaValue() );
          }
        }
      }
    } else {
      if( !empty( $fv_fp->aCurArgs ) && count($fv_fp->aCurArgs) > 0 ) {
        foreach( $fv_fp->aCurArgs AS $key => $subtitles ) {
          if( stripos($key,'subtitles') !== 0 || empty($subtitles) ) {
            continue;
          }

          $subtitles = explode( ";",$subtitles);
          if( empty($subtitles[$index]) ) continue;

          $aSubtitles[str_replace( 'subtitles_', '', $key )] = $this->get_subtitles_url( $subtitles[$index] );
        }
      }
    }

    return $aSubtitles;
  }

  function get_subtitles_url( $subtitles ) {
    global $fv_fp;

    if( strpos($subtitles,'http://') === false && strpos($subtitles,'https://') === false ) {
      if ( $subtitles[0] === '/' ) {
        $subtitles = substr($subtitles, 1);
      }

      $subtitles = $fv_fp->get_server_url() . $subtitles;
    }
    else {
      $subtitles = trim($subtitles);
    }

    $subtitles = apply_filters( 'fv_flowplayer_resource', $subtitles );

    return $subtitles;
  }

}

$FV_Player_Subtitles = new FV_Player_Subtitles();
