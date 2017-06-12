<?php

class FV_Player_Subtitles {
  
  public function __construct() {
    add_filter('fv_player_item', array($this, 'add_subtitles'), 10, 2 );

  }

  function add_subtitles( $aItem, $index ) {
    global $fv_fp;
    
    $aSubtitles = $fv_fp->get_subtitles($index);
    if( count($aSubtitles) == 0 ) return $aItem;
        
    $aLangs = flowplayer::get_languages();
    $countSubtitles = 0;
    $aOutput = array();
    
    foreach( $aSubtitles AS $key => $subtitles ) {
      $objSubtitle = new stdClass;
      if( $key == 'subtitles' ) {                   
        $aLang = explode('-', get_bloginfo('language'));
        if( !empty($aLang[0]) ) $objSubtitle->srclang = $aLang[0];
        $sCode = $aLang[0];
        
        $sCaption = '';
        if( !empty($sCode) && $sCode == 'en' ) {
          $sCaption = 'English';
        
        } elseif( !empty($sCode) ) {
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
      

      $objSubtitle->src = $subtitles;
      if( $countSubtitles == 0 && $fv_fp->_get_option('subtitleOn') ) {
        $objSubtitle->default = true;
      }      
      $aOutput[] = $objSubtitle;
      
      $countSubtitles++;
    }    
    
    if( count($aSubtitles) ) {
      $aItem['subtitles'] = $aOutput;
    }
    
    return $aItem;
  }

}

$FV_Player_Subtitles = new FV_Player_Subtitles();
