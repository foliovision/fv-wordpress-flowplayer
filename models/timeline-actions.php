<?php

class FV_Player_Timeline_Actions {
  
  public function __construct() {
    add_filter( 'fv_flowplayer_shortcode', array( $this, 'shortcode' ), 10, 3 );
    add_filter( 'fv_player_item', array($this, 'add_timeline_actions'), 10, 2 );
    add_filter( 'fv_flowplayer_inner_html', array( $this, 'html' ), 10, 2 );
  }

  function add_timeline_actions( $aItem, $index ) {
    if( $index > 0 ) return $aItem;
    
    global $fv_fp;    
    $aActions = $this->get_actions($fv_fp->aCurArgs);
    
    if( count($aActions) ) {
      $aItem['cuepoints'] = $aActions;
    }
    
    return $aItem;
  }
  
  function get_actions( $args ) {
    if( empty($args['actions']) ) return false;
    
    $aActions = explode(',', $args['actions'] );
    if( count($aActions) == 0 ) return false;
    
    $aOutput = array();
    foreach( $aActions AS $k => $v ) {
      $v = explode('-',$v);
      $objAction = new stdClass;
      $objAction->time = $v[0];
      $objAction->popup = $v[1];
      $aOutput[] = $objAction;
    }
    
    return $aOutput;
  }
  
  function html( $sHTML ) {
    $aArgs = func_get_args();
    if( !empty($aArgs[1]->aCurArgs['actions']) ) {
      $aActions = $this->get_actions($aArgs[1]->aCurArgs);
      $aPopupsAdded = array();
      foreach( $aActions AS $objAction ) {
        if($objAction->popup === 'random' || is_numeric($objAction->popup)  ){
          $aPopupData = get_option('fv_player_popups');    
          if ($objAction->popup === 'random') {
            $iPopupIndex = rand(1,count($aPopupData) );
          } elseif (is_numeric($objAction->popup)) {
            $iPopupIndex = intval($objAction->popup);
          }
          
          if(isset($aPopupData[$iPopupIndex]) && !isset($aPopupsAdded[$iPopupIndex]) ){
            $aPopupsAdded[$iPopupIndex] = true;
            
            $popup = $aPopupData[$iPopupIndex]['html'];
            $sClass = ' fv_player_popup-'.$iPopupIndex;
        
            $popup = apply_filters('fv_flowplayer_popup_html', $popup);
            if (strlen(trim($popup)) > 0) {
              $sHTML .= '<div class="wpfp_custom_popup" data-popup-id="'.$iPopupIndex.'" style="display: none"><div class="fv_player_popup'.$sClass.' wpfp_custom_popup_content">'.$popup.'</div></div>';
            }
          }else{
            continue;
          }
        }
      }
    }
    return $sHTML;
  }
  
  function shortcode( $attrs ) {
    $aArgs = func_get_args();
    
    if( isset($aArgs[2]) && isset($aArgs[2]['actions']) ) {
      $attrs['actions'] = $aArgs[2]['actions'];
    }
    
    return $attrs;
  }  

}

$FV_Player_Timeline_Actions = new FV_Player_Timeline_Actions();
